<?php

namespace Package\DocTalk\Livewire\Pages;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Package\DocTalk\Models\Conversation;
use Package\DocTalk\Services\LLMUtilities;
use Package\DocTalk\Traits\UploadDocs;

#[Layout('doctalk::components.layouts.doctalk')]
class Chat extends Component
{
    use UploadDocs;

    #[Validate('min:1')]
    public string $query = '';

    public ?Conversation $conversation = null;

    public function mount($conversation = null): void
    {
        $this->conversation = $conversation ?? new Conversation();
    }

    #[Title('Chat With Documents')]
    public function render(): View|Factory|Application
    {
        $this->dispatch('focusInput');

        return view('doctalk::livewire.pages.chat');
    }

    #[Renderless]
    public function save(): void
    {
        $this->validate();

        if (!$this->query) {
            $this->dispatch('flashMessage', [
                'message' => 'Please enter a message.',
                'type' => 'error'
            ]);

            $this->dispatch('focusInput');

            return;
        }

        // create new conversation if not exists
        if (!$this->conversation->exists) {

            // for new conversation, we need to generate a title
            $result = $this->conversation->generateTitle($this->query);

            if ($error = LLMUtilities::AIChatFailed($result)) {
                $this->conversation->delete();
                $this->conversation = null;

                $this->dispatch('flashMessage', [
                    'message' => $error,
                    'type' => 'error'
                ]);

                return;
            }

            $message = $this->conversation->addChatMessage($this->query);

            $this->conversation->createTempAImessage();

            session()->flash('lastUserMessage', $message->id);

            $this->redirect(route('doctalk.chat', $this->conversation->id), true);

            return;
        }

        $this->conversation->addChatMessage($this->query);

        $this->dispatch('inputSaved');
        $this->dispatch('conversationsUpdated'); // so sidebar can push updated convs on top

        $this->reset('query');
    }
}
