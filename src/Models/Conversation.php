<?php

namespace Package\DocTalk\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Package\DocTalk\DocTalkConstants;
use Package\DocTalk\Services\LLMUtilities;

class Conversation extends Model
{
    protected $guarded = [];

    protected $connection = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('doctalk.db_connection');
    }

    protected static function boot(): void
    {
        parent::boot();

        try {
            self::deleteOld();
        } catch (Exception) {
        }
    }

    public function addChatMessage(string $message, bool $isAi = false): Message
    {
        // update conversation last used time
        $this->updated_at = now();
        $this->save();

        return $this->messages()->create([
            'content' => $message,
            'conversation_id' => $this->id,
            'ai' => $isAi,
            'llm' => ucfirst(config('doctalk.llm.llm_provider', 'Gemini')) . ' (' . config('doctalk.llm.llm_model', 'gemini-1.5-flash') . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Generate title for the chat
     */
    public function generateTitle($message): string
    {
        $title = 'New Conversation';

        try {

            if (config('doctalk.llm.generate_conversation_titles')) {
                $llm = LLMUtilities::getLLM();

                $prompt = "
                Create only a single title from the provided Text, it must be of minimum 4 characters and must not be more than
                30 characters and without punctuation characters, language must be same as Text. Text: '$message'. If text is
                too short, create on your own without completing the text.
                ";

                $title = $llm->chat($prompt);
                $title = preg_replace('/[^A-Za-z0-9] /', '', $title);
            }

        } catch (Exception) {
            //
        } finally {
            $this->name = $title;
            $this->updated_at = now();
            $this->created_at = now();
            $this->user_id = auth()->check() ? auth()->id() : 0;
            $this->save();
        }

        return $title;
    }

    // Create temp answer to show the user that the AI is typing
    public function createTempAImessage(): void
    {
        // update conversation last used time
        $this->updated_at = now();
        $this->save();

        $this->messages()->create([
            'content' => DocTalkConstants::LOADING_STRING,
            'llm' => ucfirst(config('doctalk.llm.llm_provider', 'Gemini')) . ' (' . config('doctalk.llm.llm_model', 'gemini-1.5-flash') . ')',
            'conversation_id' => $this->id,
            'ai' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->chaperone('conversation');
    }

    public static function deleteOld(): void
    {
        $days = Setting::query()->where('key', 'delete_conversations_after_days')->first()?->value ?? 0;

        if ($days) {
            $oldConversations = static::query()
                ->where('created_at', '<', now()->subDays($days))
                ->where('favorite', false)
                ->where('archived', false);

            if ($oldConversations->exists()) {
                $deletedCount = $oldConversations->delete();

                info("Deleted $deletedCount old conversations.");
            }
        }
    }
}
