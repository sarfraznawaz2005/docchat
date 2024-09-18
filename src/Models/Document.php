<?php

namespace Package\DocTalk\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class Document extends Model
{
    use HasNeighbors;

    protected $guarded = [];

    protected $connection = null;

    protected $casts = [
        'embedding_1536' => Vector::class,
        'embedding_768' => Vector::class,
        'metadata' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('doctalk.db_connection');
    }

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('d-m-Y h:i');
    }

    public function getUpdatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('d-m-Y h:i');
    }

    /**
     * @throws Exception
     */
    public static function saveTexts(array $texts): void
    {
        try {

            $llm = config('doctalk.llm.llm_provider', 'gemini');

            foreach ($texts as $text) {

                $content = $text['text'];
                $contentHash = hash('sha256', $content);

                $values = [
                    'content' => $content,
                    'llm' => $llm,
                    'metadata' => $text['metadata'] ?? [],
                    'filename' => $text['file']
                ];

                $embeddings = new Vector($text['embeddings']);

                if ($llm === 'openai') {
                    $values['embedding_1536'] = $embeddings;
                } elseif ($llm === 'gemini') {
                    $values['embedding_768'] = $embeddings;
                }

                Document::query()->updateOrCreate(['content_hash' => $contentHash], $values);
            }

        } catch (Exception $e) {
            throw new Exception('Failed to save contents to database: ' . $e->getMessage());
        }
    }
}
