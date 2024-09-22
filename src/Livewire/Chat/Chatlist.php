<?php

namespace Package\DocTalk\Livewire\Chat;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Package\DocTalk\DocTalkConstants;
use Package\DocTalk\Models\Conversation;
use Package\DocTalk\Models\Message;
use Package\DocTalk\Services\LLMUtilities;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Chatlist extends Component
{
    public ?Conversation $conversation = null;

    public function mount(Conversation $conversation = null): void
    {
        $this->conversation = $conversation;

        if ($this->conversation->exists) {
            if (session()->has('lastUserMessage')) {
                session()->forget('lastUserMessage');
                $this->dispatch('getAIResponse');
            }
        }
    }

    public function placeholder(): string
    {
        return view('doctalk::livewire.loader');
    }

    #[On('inputSaved')]
    #[Renderless]
    public function addUserMessage(): void
    {
        $this->dispatch('createTempAImessage')->self();
    }

    #[On('createTempAImessage')]
    public function createTempAImessage(): void
    {
        $this->conversation->createTempAImessage();

        $this->dispatch('getAIResponse');
    }

    #[On('suggestedAnswer')]
    #[Renderless]
    function suggestedAnswer(string $linkText): void
    {
        $this->conversation->addChatMessage($linkText);

        $this->dispatch('createTempAImessage')->self();
    }

    public function regenerate(Message $message): void
    {
        $message->content = DocTalkConstants::LOADING_STRING;
        $message->updated_at = now();
        $message->save();

        $message->conversation->updated_at = now();
        $message->conversation->save();

        $this->dispatch('getAIResponse');
    }

    #[On('getAIResponse')]
    public function getAIResponse(): void
    {
        $this->dispatch('conversationsUpdated'); // so sidebar can push updated convs on top

        $tempMessage = $this->conversation
            ->messages()
            ->where('content', '=', DocTalkConstants::LOADING_STRING)
            ->latest()
            ->first();

        try {

            $usertMessage = $this->conversation->messages()->where('ai', false)->latest()->first();

            $llm = LLMUtilities::getLLM();
            $context = '';
            $metadata = [];

            $latestMessages = $this->getLatestMessages($this->conversation);
            $uniqueMessages = $this->getUniqueMessages($latestMessages, $usertMessage);
            $conversationHistory = implode("\n", array_map(fn($message) => LLMUtilities::htmlToText($message), $uniqueMessages));

            $results = LLMUtilities::searchTexts($usertMessage->content);
            //dd($results);

            // try with standalone question
//            if (!count($results)) {
//                $standAloneQuestion = LLMUtilities::getStandAloneQuestion($usertMessage->content, $conversationHistory);
//
//                if ($standAloneQuestion) {
//                    $results = LLMUtilities::searchTexts($standAloneQuestion);
//                }
//            }

            if (!count($results)) {
                $this->stream(
                    to: 'liveUpdate',
                    content: DocTalkConstants::NO_RESULTS_FOUND
                );

                $tempMessage->update(['content' => DocTalkConstants::NO_RESULTS_FOUND]);
                return;
            }

            // build context
            foreach ($results as $result) {
                $context .= $result['content'] . "\n\n";
                $metadata[] = $result['metadata'];
            }

            $prompt = LLMUtilities::makePrompt($context, $usertMessage->content, $conversationHistory);

            $consolidatedResponse = '';

            $llm->chat($prompt, true, function ($chunk) use (&$consolidatedResponse) {
                $consolidatedResponse .= $chunk;

                $this->stream(
                    to: 'liveUpdate',
                    content: $chunk
                );
            });

            $consolidatedResponse = LLMUtilities::processMarkdownToHtml($consolidatedResponse);

            if (config('doctalk.llm.show_sources')) {
                if (!str_contains(strtolower($consolidatedResponse), 'have enough information to answer this question accurately.')) {
                    $consolidatedResponse .= '<small>Sources: ' . LLMUtilities::formatMetadata($metadata) . '</small>';
                }
            }

            $tempMessage->update([
                'content' => $consolidatedResponse,
            ]);
        } catch (Exception $e) {
            Log::error(__CLASS__ . ': ' . $e->getMessage());
            $error = '<span class="red">Oops! Failed to get a response, please try again.' . ' ' . $e->getMessage() . '</span>';

            $this->stream(
                to: 'liveUpdate',
                content: $error,
                replace: true
            );

            //$latestMessage->delete();
            $tempMessage->update(['content' => $error]);
        } finally {
            $this->dispatch('focusInput');
            $this->dispatch('conversationsUpdated');
        }
    }

    /** @noinspection ALL */
    #[Computed]
    public function messages()
    {
        if ($this->conversation) {
            return $this->conversation->messages->sortBy('id');
        }
    }

    public function render(): View|Factory|Application
    {
        return view('doctalk::livewire.chat.chatlist');
    }

    public function delete(Message $message): void
    {
        $message->conversation->updated_at = now();
        $message->conversation->save();

        $message->delete();

        // doing redirect because otherwise was getting 404 for some reason on multiple random deletes
        $this->redirect(route('doctalk.chat', $this->conversation->id), true);
    }

    #[Renderless]
    public function clearConversation(): void
    {
        if ($this->conversation->messages()->delete()) {
            // doing redirect because otherwise was getting 404 for some reason on multiple random deletes
            $this->redirect(route('doctalk.chat', $this->conversation->id), true);
        }
    }

    #[Renderless]
    public function export($format): StreamedResponse
    {
        $filename = 'chat-' . strtolower(Str::slug($this->conversation->name)) . '.' . $format;

        $content = '<meta charset="utf-8"><div style="margin:50px;">';
        $content .= '<div align="center"><h2 style="margin-bottom: 0">Conversation Name: ' . $this->conversation->name . '</h2></div><br>';
        $content .= '<div align="center"><strong>Created On: ' . $this->conversation->created_at . '</strong></div><br>';

        if ($format === 'txt') {
            $content .= str_repeat('-', 100);
        }

        foreach ($this->messages as $message) {
            $body = trim($message->content);

            if ($message->ai) {
                $content .= <<<HTML
<div style='border-radius: 10px; border: 1px solid #555; padding: 15px; margin-bottom: 25px;'>
<strong>AI - $message->llm:</strong>
<hr>
$body
</div>
HTML;
            } else {
                $content .= <<<HTML
<div style='border-radius: 10px; border: 1px solid #555; padding: 15px; margin-bottom: 25px; background: #E5FCD4;'>
<strong>User:</strong>
<hr>
$body
</div>
HTML;

            }

            if ($format === 'txt') {
                $content .= str_repeat('-', 100);
            } else {
                $content = str_ireplace('related questions:', '', $content);

                // Remove <related_question> tags including their contents
                $content = preg_replace('/<related_question>.*?<\/related_question>/is', '', $content);
                $content = preg_replace('/&lt;related_question&gt;.*?&lt;\/related_question&gt;/is', '', $content);
                $content = str_ireplace('<li></li>', '', $content);
            }
        }

        $content .= '</div>';

        $content = trim($content);

        if ($format === 'txt') {
            $content = LLMUtilities::htmlToText($content, false);
        }

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename);
    }

    private function getLatestMessages(Conversation $conversation)
    {
        return $conversation
            ->messages()
            ->where('content', '!=', DocTalkConstants::LOADING_STRING)
            ->whereNot(function ($query) {
                $query
                    ->where('content', 'like', '%conversation history%')
                    ->orWhere('content', 'like', '%have enough information to answer this question accurately%')
                    ->orWhere('content', 'like', '%provided context%');
            })
            ->latest()
            ->limit(DocTalkConstants::CONVERSATION_HISTORY)
            ->get()
            ->sortBy('id');
    }

    private function getUniqueMessages($latestMessages, Message $userQuery): array
    {
        $uniqueMessages = [];
        foreach ($latestMessages as $message) {

            // do not add latest user query to conversation history
            if ($message->id === $userQuery->id) {
                continue;
            }

            $formattedMessage = ($message->ai ? 'AI: ' : 'USER: ') . $message->content;

            if (!$message->ai) {
                $uniqueMessages[] = $formattedMessage; // allow all user messages
            } else {
                if (!in_array($formattedMessage, $uniqueMessages)) {
                    $uniqueMessages[] = LLMUtilities::htmlToText($formattedMessage);
                }
            }

        }

        return $uniqueMessages;
    }
}
