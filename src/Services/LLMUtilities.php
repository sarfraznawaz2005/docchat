<?php

namespace Package\DocTalk\Services;

use DOMDocument;
use Exception;
use Package\DocTalk\DocTalkConstants;
use Package\DocTalk\LLM\GeminiProvider;
use Package\DocTalk\LLM\LlmProvider;
use Package\DocTalk\LLM\OllamaProvider;
use Package\DocTalk\LLM\OpenAiProvider;
use Package\DocTalk\Models\Document;
use Pgvector\Laravel\Vector;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use Spatie\LaravelMarkdown\MarkdownRenderer;
use TextAnalysis\Analysis\FreqDist;
use TextAnalysis\Documents\TokensDocument;
use TextAnalysis\Exceptions\InvalidParameterSizeException;
use TextAnalysis\Tokenizers\GeneralTokenizer;

class LLMUtilities
{
    private static LlmProvider $llm;
    private static array $records = [];

    public static function goodToGo(): bool
    {
        return
            config('doctalk.enabled') &&
            config('doctalk.db_connection') &&
            config('doctalk.llm.llm_provider') &&
            config('doctalk.llm.api_key') &&
            config('doctalk.llm.llm_model');
    }

    public static function getLLM(): LlmProvider
    {
        $llm = config('doctalk.llm.llm_provider', 'gemini');
        $model = config('doctalk.llm.llm_model', 'gemini-1.5-flash');
        $apiKey = config('doctalk.llm.api_key');
        $maxTokens = config('doctalk.llm.options.maxOutputTokens', 4096);
        $temprature = config('doctalk.llm.options.temperature', 0.7);

        return match (strtolower($llm)) {
            'gemini' => new GeminiProvider($apiKey, $model, ['maxOutputTokens' => $maxTokens, 'temperature' => $temprature]),
            'openai' => new OpenAiProvider($apiKey, $model, ['max_tokens' => $maxTokens, 'temperature' => $temprature]),
            default => new OllamaProvider($apiKey, $model, ['max_tokens' => $maxTokens]),
        };
    }

    /**
     * @throws Exception
     */
    public static function searchTexts(string $query, int $maxResults = 3): array
    {
        static::$llm = static::getLLM();

        static::$records = Document::query()
            ->select(['id', 'content', 'llm', 'metadata'])
            ->get()
            ->toArray();

        // full semantic search
        $results = static::performLLMSemanticSearch($query);

        if (!empty($results)) {
            if (app()->environment('local')) {
                info('Resutls found via semantic search');
            }

            return static::getTopResults($results, $maxResults);
        }

        // partial semantic search
        $results = static::performTFIDFSearch($query);

        if (!empty($results)) {
            if (app()->environment('local')) {
                info('Resutls found via partial semantic search');
            }

            return static::getTopResults($results, $maxResults);
        }

        // direct text search
        $results = static::performTextSearch($query);

        if (!empty($results)) {
            if (app()->environment('local')) {
                info('Resutls found via text search');
            }
        }

        return static::getTopResults($results, $maxResults);
    }

    /**
     * @throws Exception
     */
    protected static function performLLMSemanticSearch(string $query): array
    {
        $field = static::getEmbdeddingField();

        $queryEmbeddings = static::$llm->embed(
            [static::getCleanedText($query, true)],
            static::getEmbdeddingModel(),
        );

        // openai or gemini
        $queryEmbeddings = new Vector($queryEmbeddings['embeddings'][0]['values'] ?? $queryEmbeddings[0]['embedding']);

        // Combine with ORDER BY and LIMIT to use an index
        return Document::query()
            ->select(['id', 'content', 'llm', 'metadata'])
            ->orderByRaw("$field <-> ?", [$queryEmbeddings])
            ->limit(5)
            ->get()
            ->toArray();
    }

    protected static function performTFIDFSearch(string $query): array
    {
        $tokenizedDocs = array_map(function ($doc) {
            $cleanedText = static::getCleanedText($doc['content'], true);

            return new TokensDocument(explode(' ', $cleanedText));
        }, static::$records);

        // Clean and tokenize the query
        $cleanedQuery = static::getCleanedText($query, true);
        $tokenizedQuery = new TokensDocument(explode(' ', $cleanedQuery));

        $totalDocs = count($tokenizedDocs);
        $docDF = static::calculateDF($tokenizedDocs); // Document Frequencies

        $docVectors = [];
        foreach ($tokenizedDocs as $doc) {
            $tf = static::calculateTF($doc->getDocumentData());
            $docVectors[] = static::calculateTFIDF($tf, $docDF, $totalDocs);
        }

        $queryTF = static::calculateTF($tokenizedQuery->getDocumentData());
        $queryVector = static::calculateTFIDF($queryTF, $docDF, $totalDocs);

        $rankedResults = [];
        foreach ($docVectors as $key => $docVector) {
            $similarity = static::cosineSimilarity($docVector, $queryVector);

            if ($similarity > 0) {
                $rankedResults[] = static::$records[$key];
            }
        }

        usort($rankedResults, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_filter($rankedResults);
    }

    /**
     * @throws Exception
     */
    protected static function performTextSearch(string $query): array
    {
        $results = [];
        $cleanedQuery = static::getCleanedText($query, true);

        foreach (static::$records as $chunk) {
            $exactMatchScore = static::calculateExactMatchScore($cleanedQuery, $chunk['content']);
            $fuzzyMatchScore = static::calculateFuzzyMatchScore($cleanedQuery, $chunk['content']);

            $maxScore = max($exactMatchScore, $fuzzyMatchScore);

            if ($maxScore >= static::getSimiliarityThreashold()) {
                $results[] = $chunk;
            }
        }

        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return $results;
    }

    /**
     * @throws Exception
     */
    public static function getTextsWithEmbeddings(array $texts): array
    {
        try {

            $textSplits = array_map(function ($split) {
                return $split['text'];
            }, $texts);

            $chunks = array_chunk($textSplits, static::getEmbdeddingBatchSize());

            foreach ($chunks as $chunkIndex => $chunk) {
                // Get embeddings for this chunk
                $embeddings = static::$llm->embed($chunk, static::getEmbdeddingModel());
                $embeddings = $embeddings['embeddings'] ?? $embeddings; // Handle Gemini or OpenAI response

                // Calculate the starting index for this chunk
                $startIndex = $chunkIndex * static::getEmbdeddingBatchSize();

                // Map embeddings to the corresponding original texts
                foreach ($embeddings as $embeddingIndex => $embedding) {
                    $originalIndex = $startIndex + $embeddingIndex;

                    if (isset($texts[$originalIndex])) {
                        $texts[$originalIndex]['embeddings'] = $embedding['embedding'] ?? $embedding['values'];
                    }
                }
            }

            return $texts;

        } catch (Exception $e) {
            throw new Exception('Failed to save contents to database: ' . $e->getMessage());
        }
    }

    public static function getEmbdeddingModel(): string
    {
        if (static::$llm instanceof OpenAiProvider) {
            return DocTalkConstants::OPENAI_EMBEDDING_MODEL;
        }

        return DocTalkConstants::GEMINI_EMBEDDING_MODEL;
    }

    public static function getEmbdeddingBatchSize(): string
    {
        if (static::$llm instanceof OpenAiProvider) {
            return DocTalkConstants::OPENAI_EMBEDDING_BATCHSIZE;
        }

        return DocTalkConstants::GEMINI_EMBEDDING_BATCHSIZE;
    }

    public static function getEmbdeddingField(): string
    {
        if (static::$llm instanceof OpenAiProvider) {
            return 'embedding_1536';
        }

        return 'embedding_768';
    }

    /**
     * @throws Exception
     */
    public static function extractTextFromFile(string $file, int $chunkSize = 1000): array
    {
        static::$llm = static::getLLM();

        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch (strtolower($extension)) {
            case 'pdf':

                $config = new Config();
                $config->setRetainImageContent(false);

                $parser = new Parser([], $config);

                $texts = [];
                $pdf = $parser->parseFile($file);
                $pages = $pdf->getPages();

                foreach ($pages as $pageNumber => $page) {
                    $texts[] = [
                        'text' => $page->getText(),
                        'source' => basename($file) . ' [' . $pageNumber + 1 . ']',
                        'file' => basename($file),
                    ];
                }

                // filter out bad stuff
                $texts = array_map(fn($item) => ['text' => static::getCleanedText($item['text']), 'source' => $item['source'], 'file' => $item['file']], $texts);
                $texts = array_filter($texts, fn($item) => !empty(trim($item['text'])) && strlen(trim($item['text'])) > 2);

                return static::splitTextIntoChunks($texts, $chunkSize);
            case 'txt':
            case 'md':
            case 'html':
            case 'htm':

                $content = file_get_contents($file);
                $lines = explode("\n", $content);

                $texts[] = [
                    'text' => $lines,
                    'source' => basename($file),
                    'file' => basename($file),
                ];

                // filter out bad stuff
                $texts = array_map(fn($item) => ['text' => static::getCleanedText($item['text']), 'source' => $item['source'], 'file' => $item['file']], $texts);
                $texts = array_filter($texts, fn($item) => !empty(trim($item['text'])) && strlen(trim($item['text'])) > 2);

                return static::splitTextIntoChunks($texts, $chunkSize);
            default:
                throw new Exception("Unsupported file type: $extension");
        }
    }

    public static function splitTextIntoChunks(array $texts, int $chunkSize = 1000): array
    {
        $chunks = [];
        $overlapPercentage = 3; // 30% overlap
        $overlapSize = max(1, (int)($chunkSize * ($overlapPercentage / 100)));

        foreach ($texts as $text) {
            $fullText = $text['text'];
            $totalLength = strlen($fullText);

            $chunkStart = 0;
            while ($chunkStart < $totalLength) {
                $chunkEnd = min($chunkStart + $chunkSize, $totalLength);
                $chunk = substr($fullText, $chunkStart, $chunkEnd - $chunkStart);

                // Append the chunk
                $chunks[] = [
                    'text' => trim($chunk),
                    'metadata' => $text['source'],
                    'file' => $text['file']
                ];

                if ($chunkEnd == $totalLength) {
                    break;
                }

                $nextChunkStart = max(0, $chunkEnd - $overlapSize);
                $chunkStart = $nextChunkStart;
            }
        }

        return $chunks;
    }

    public static function getCleanedText(string $text, bool $removeStopWords = false): string
    {
        $text = strtolower(strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_HTML5)));

        // we mean literal stings here not actual new lines, so do not use " characters
        $text = str_replace(['\n', '\r', '\r\n'], ' ', $text);

        // Add spaces around Unicode sequences and convert them to actual characters
        $text = preg_replace_callback(
            '/\\\\u([0-9A-Fa-f]{4})/',
            function ($matches) {
                return ' ' . mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE') . ' ';
            },
            $text
        );

        // Replace unwanted characters and clean the text
        $text = preg_replace(
            [
                '/\r\n|\r/',                        // Handle different newline characters
                '/(\s*\n\s*){3,}/',                 // Replace multiple newlines with double newlines
                '/\s+/',                            // Replace multiple spaces with single space
                '/[^\w\s\-$%_.\/]/',                // Allow only letters, numbers, $, -, _, %, /, ., and space
                '/(\$|%|_|-|\\|.|\/| )\1+/',        // Remove duplicate special characters
            ],
            [
                "\n",
                "\n\n",
                ' ',
                ' ',
                '$1',
            ],
            $text
        );

        // Tokenize text
        $tokenizer = new GeneralTokenizer();
        $tokens = $tokenizer->tokenize($text);

        if ($removeStopWords) {
            $text = implode(' ', $tokens);
            $text = static::removeStopwords($text);
            $tokens = explode(' ', $text);
        }

        return implode(' ', $tokens);
    }

    public static function removeStopwords(string $text): string
    {
        $stopWords = [
            'the', 'a', 'an', 'and', 'but', 'if', 'or', 'because', 'as', 'until', 'while', 'of', 'at',
            'by', 'for', 'with', 'about', 'against', 'between', 'into', 'through', 'during', 'before',
            'after', 'above', 'below', 'to', 'from', 'up', 'down', 'in', 'out', 'on', 'off', 'over',
            'under', 'again', 'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why', 'how',
            'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor',
            'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 'can', 'will', 'just', 'don',
            'should', 'now', 'what', 'is', 'am', 'are', 'was', 'were', 'be', 'been', 'being', 'has',
            'have', 'had', 'do', 'does', 'did', 'having', 'he', 'she', 'it', 'they', 'them', 'his',
            'her', 'its', 'their', 'my', 'your', 'our', 'we', 'you', 'who', 'whom', 'which', 'this',
            'that', 'these', 'those', 'I', 'me', 'mine', 'yours', 'ours', 'himself', 'herself', 'itself',
            'themselves', 'aren\'t', 'can\'t', 'cannot', 'could', 'couldn\'t', 'didn\'t', 'doesn\'t',
            'doing', 'don\'t', 'hadn\'t', 'hasn\'t', 'haven\'t', 'he\'d', 'he\'ll', 'he\'s', 'here\'s',
            'hers', 'him', 'how\'s', 'i', 'i\'d', 'i\'ll', 'i\'m', 'i\'ve', 'isn\'t', 'it\'s', 'let\'s',
            'mustn\'t', 'myself', 'ought', 'ourselves', 'she\'d', 'she\'ll', 'she\'s', 'shouldn\'t',
            'that\'s', 'theirs', 'there\'s', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'wasn\'t',
            'we\'d', 'we\'ll', 'we\'re', 'we\'ve', 'weren\'t', 'what\'s', 'when\'s', 'where\'s',
            'who\'s', 'why\'s', 'won\'t', 'would', 'wouldn\'t', 'you\'d', 'you\'ll', 'you\'re',
            'you\'ve', 'yourself', 'yourselves'
        ];

        $words = explode(' ', $text);
        $filteredWords = array_diff($words, $stopWords);

        return implode(' ', $filteredWords);
    }

    public static function htmlToText($html, $removeWhiteSpace = true): string
    {
        $text = str_ireplace('related questions:', '', $html);

        // Remove <related_question> tags including their contents
        $text = preg_replace('/<related_question>.*?<\/related_question>/is', '', $text);
        $text = preg_replace('/&lt;related_question&gt;.*?&lt;\/related_question&gt;/is', '', $text);

        // Replace <br> tags with newlines
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);

        // Replace </p> tags with double newlines
        $text = preg_replace('/<\/p>/i', "\n\n", $text);

        // Remove all remaining HTML tags
        $text = strip_tags($text);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize line breaks
        $text = preg_replace('/\r\n|\r/', "\n", $text);

        // Replace any combination of more than two newlines and whitespace with two newlines
        $text = preg_replace('/(\s*\n\s*){3,}/', "\n\n", $text);

        // Remove extra whitespace
        if ($removeWhiteSpace) {
            $text = preg_replace('/\s+/', ' ', $text);
        }

        return trim($text);
    }

    public static function AIChatFailed($result): string
    {
        if (str_contains(strtolower($result), 'failed to get a successful response') ||
            str_contains(strtolower($result), 'unknown error')) {
            return "There was some problem. $result";
        }

        return '';
    }

    public static function makePrompt(string $context, string $userQuery, string $conversationHistory): string
    {
        $relatedQuestionsPrompt = '';

        if (config('doctalk.llm.enable_related_questions')) {
            $relatedQuestionsPrompt = DocTalkConstants::RELATED_QUESTIONS_PROMPT;
        }

        $prompt = DocTalkConstants::MAIN_PROMPT;

        $prompt = str_ireplace('{{CONTEXT}}', $context, $prompt);
        $prompt = str_ireplace('{{USER_QUESTION}}', $userQuery, $prompt);
        $prompt = str_ireplace('{{CONVERSATION_HISTORY}}', $conversationHistory, $prompt);

        $prompt .= $relatedQuestionsPrompt;

        if (!config('doctalk.llm.enable_related_questions')) {
            $prompt .= <<< 'EOL'

        Remember to only provid answers from provided context or conversation history, DO NOT answer from your own knowledge base.
        If you are unsure about the answer, respond with "Sorry, I don't have enough information to answer this question accurately."
        NEVER ATTEMPT TO MAKE UP OR GUESS AN ANSWER.

        EOL;
        }

        $prompt .= "\n\nYOUR ANSWER HERE:";

        if (app()->environment('local')) {
            info("\n" . str_repeat('-', 100) . "\n" . $prompt . "\n");
        }

        return $prompt;
    }

    public static function processMarkdownToHtml($markdownContent, $fixBroken = true): string
    {
        // Use the MarkdownRenderer to convert markdown to HTML
        $markdownRenderer = app(MarkdownRenderer::class);
        $htmlContent = $markdownRenderer->toHtml($markdownContent);

        if ($fixBroken) {
            // Suppress libxml errors and warnings
            libxml_use_internal_errors(true);

            // Initialize DOMDocument and prevent automatic DOCTYPE addition
            $doc = new DOMDocument();
            $doc->substituteEntities = false;

            // Convert to HTML entities and load into DOMDocument with a dummy structure
            $content = mb_convert_encoding($htmlContent, 'html-entities', 'utf-8');
            $success = $doc->loadHTML('<html><body>' . $content . '</body></html>');

            libxml_clear_errors();

            if ($success) {
                // Extract only the content inside the <body> tag
                $bodyContent = '';
                foreach ($doc->getElementsByTagName('body')->item(0)->childNodes as $childNode) {
                    $bodyContent .= $doc->saveHTML($childNode);
                }

                return $bodyContent ?: $htmlContent;
            }
        }

        return $htmlContent;
    }

    protected static function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        foreach ($vecA as $key => $valueA) {
            $valueB = $vecB[$key] ?? 0;
            $dotProduct += $valueA * $valueB;
            $magnitudeA += $valueA * $valueA;
            $magnitudeB += $valueB * $valueB;
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA * $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    protected static function calculateExactMatchScore(string $query, string $text): float
    {
        return stripos($text, $query) !== false ? static::getSimiliarityThreashold() : 0.0;
    }

    protected static function calculateFuzzyMatchScore(string $query, string $text): float
    {
        $distance = levenshtein($query, $text);
        $maxLength = max(strlen($query), strlen($text));

        return $maxLength === 0 ? static::getSimiliarityThreashold() : 1 - ($distance / $maxLength);
    }

    protected static function getSimiliarityThreashold(): float
    {
        // because there is difference in the cosine similarity values between OpenAI and Gemini
        if (static::$llm instanceof OpenAiProvider) {
            return 0.75;
        } else {
            return 0.6;
        }
    }

    protected static function calculateTF($tokens): array
    {
        try {
            $freqDist = new FreqDist($tokens);
        } catch (InvalidParameterSizeException) {
            return $tokens;
        }

        return $freqDist->getKeyValuesByFrequency();
    }

    protected static function calculateDF($documents): array
    {
        $df = [];

        foreach ($documents as $tokens) {
            $uniqueTokens = array_unique($tokens->getDocumentData());
            foreach ($uniqueTokens as $token) {
                if (!isset($df[$token])) {
                    $df[$token] = 0;
                }
                $df[$token]++;
            }
        }

        return $df;
    }

    protected static function calculateTFIDF($tf, $df, $totalDocs): array
    {
        $tfidf = [];

        foreach ($tf as $term => $count) {
            $idf = log($totalDocs / ($df[$term] ?? 1)); // Inverse Document Frequency
            $tfidf[$term] = $count * $idf;
        }

        return $tfidf;
    }

    protected static function getTopResults(array $results, $maxResults): array
    {
        $topResults = array_slice($results, 0, $maxResults);

        foreach ($topResults as &$result) {
            if (isset($result['matchedChunk']['embeddings'])) {
                unset($result['matchedChunk']['embeddings']);
            }
        }

        return $topResults;
    }

    public static function sizeInMB($sizeInKB): string
    {
        if ($sizeInKB > 0) {
            return $sizeInKB / 1024 . 'mb'; // 1 MB = 1024 KB
        }

        return "0mb";
    }

    public static function formatMetadata(array $array): string
    {
        $filePages = [];

        foreach ($array as $item) {
            // extract filename and page number
            if (preg_match('/^(.*\.pdf) \[(\d+)]$/', $item, $matches)) {
                $filename = $matches[1];
                $pageNumber = $matches[2];

                // Initialize the array for the filename if it doesn't exist
                if (!isset($filePages[$filename])) {
                    $filePages[$filename] = [];
                }

                // Add the page number if it's not already in the array
                if (!in_array($pageNumber, $filePages[$filename])) {
                    $filePages[$filename][] = $pageNumber;
                }
            }
        }

        // Prepare the output
        $outputArray = [];

        foreach ($filePages as $filename => $pages) {
            sort($pages, SORT_NUMERIC);
            $outputArray[] = $filename . ' [' . implode(', ', $pages) . ']';
        }

        return implode(', ', $outputArray);
    }
}
