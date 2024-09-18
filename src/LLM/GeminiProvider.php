<?php

namespace Package\DocTalk\LLM;

use Exception;

class GeminiProvider extends BaseLLMProvider
{
    public function __construct(string $apiKey, string $model, array $options = [], int $retries = 1)
    {
        parent::__construct($apiKey, 'https://generativelanguage.googleapis.com/v1beta/', $model, $options, $retries);
    }

    /**
     * @throws Exception
     */
    public function chat(string $message, bool $stream = false, ?callable $callback = null): string
    {
        $responseType = $stream ? 'streamGenerateContent' : 'generateContent';

        $url = $this->baseUrl . 'models/' . $this->model . ":$responseType?key=" . $this->apiKey;

        $body = [
            'contents' => [
                'role' => 'user',
                'parts' => [
                    ['text' => $message],
                ],
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_NONE',
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_NONE',
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_NONE',
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_NONE',
                ],
            ],
            'generationConfig' => $this->options,
        ];

        if ($stream) {
            try {
                $this->makeRequest($url, $body, $stream, false, $callback);
            } catch (Exception) {
                // fallback via non-streaming response
                sleep(1);
                $response = $this->makeRequest($this->baseUrl . 'models/' . $this->model . ":generateContent?key=" . $this->apiKey, $body, false, false, $callback);
                $text = $this->getResult($response);

                if ($callback) {
                    $callback($text);
                } else {
                    echo "event: update\n";
                    echo 'data: ' . json_encode($text) . "\n\n";
                    ob_flush();
                    flush();
                }
            }
        } else {
            $response = $this->makeRequest($url, $body, false, false, $callback);
        }

        return isset($response) ? $this->getResult($response) : '';
    }

    /**
     * @throws Exception
     */
    public function embed(array $texts, string $embeddingModel, $taskType = 'SEMANTIC_SIMILARITY'): array|string
    {
        $url = $this->baseUrl . "models/$embeddingModel:batchEmbedContents?key=" . $this->apiKey;

        $content = [];

        foreach ($texts as $text) {
            $content[] = [
                "model" => "models/$embeddingModel",
                "content" => [
                    "parts" => [
                        [
                            "text" => $text
                        ]
                    ]
                ],
                'taskType' => $taskType
            ];
        }

        $body = [
            "requests" => $content
        ];

        $response = $this->makeRequest($url, $body);

        return $response ?? [];
    }

    protected function getResult(array $response): string
    {
        $text = '';

        if (isset($response['candidates'])) {
            foreach ($response['candidates'] as $candidate) {
                if (!isset($candidate['content'])) {
                    return "No response, please try again!";
                }

                foreach ($candidate['content']['parts'] as $part) {
                    $text .= $part['text'] . (php_sapi_name() === 'cli' ? "\n" : PHP_EOL);
                }
            }
        }

        return $text;
    }

    /**
     * @throws Exception
     */
    protected function getStreamingResponse($data, ?callable $callback = null): void
    {
        try {

            $data = $this->fixJson($data);
            $json = json_decode("[$data]", true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // streams works even when some chunks fail because of which caused double response, ignoring...
                //throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }

            if ($json) {
                foreach ($json as $jsonItem) {
                    if (isset($jsonItem['candidates'])) {
                        foreach ($jsonItem['candidates'] as $candidate) {
                            if (isset($candidate['content'])) {
                                foreach ($candidate['content']['parts'] ?? [] as $part) {
                                    $text = $part['text'] ?? '';

                                    if (php_sapi_name() === 'cli') {
                                        if ($callback) {
                                            $callback($text);
                                        } else {
                                            echo $text;
                                        }

                                        continue;
                                    }

                                    if (!$text) {
                                        continue;
                                    }

                                    if ($callback) {
                                        $callback($text);
                                    } else {
                                        echo "event: update\n";
                                        echo 'data: ' . json_encode($text) . "\n\n";
                                        ob_flush();
                                        flush();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
