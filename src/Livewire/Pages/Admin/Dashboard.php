<?php

namespace Package\DocTalk\Livewire\Pages\Admin;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Package\DocTalk\Models\Conversation;
use Package\DocTalk\Models\Document;
use Package\DocTalk\Models\Message;

#[Layout('doctalk::components.layouts.doctalk')]
class Dashboard extends Component
{
    #[Title('Dashboard')]
    public function render(): View|Factory|Application
    {
        return view('doctalk::livewire.pages.admin.dashboard');
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'documents' => Document::query()->distinct()->count('filename'),
            'conversations' => Conversation::query()->count(),
            'users' => Conversation::query()->distinct()->count('user_id'),
            'messages' => Message::query()->count(),
        ];
    }
}
