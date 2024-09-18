<?php

namespace Package\DocTalk\Livewire\Chat;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Package\DocTalk\Models\Conversation;

class Sidebar extends Component
{
    public ?Conversation $conversation = null;

    public string $searchQuery = '';

    protected $listeners = ['conversationsUpdated' => '$refresh'];

    public function mount($conversation = null): void
    {
        $this->conversation = $conversation ?? new Conversation();
    }

    public function render(): View|Factory|Application
    {
        return view('doctalk::livewire.chat.sidebar');
    }

    #[Computed]
    public function conversations(): Collection
    {
        return Conversation::query()
            ->where('user_id', auth()->check() ? auth()->id() : 0)
            ->when($this->searchQuery, function ($query) {
                $query->where('name', 'ILIKE', '%' . $this->searchQuery . '%');
            })
            ->orderByDesc('favorite')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();
    }

    #[Renderless]
    public function toggleFavorite(Conversation $conversation): void
    {
        $conversation->favorite = !$conversation->favorite;
        $conversation->save();

        $message = $conversation->favorite ? 'Conversation pinned successfully.' : 'Conversation un-pinned successfully.';

        $this->dispatch('flashMessage', [
            'message' => $message,
            'type' => 'success'
        ]);

        $this->dispatch('conversationsUpdated');
    }

    #[Renderless]
    public function toggleArchived(Conversation $conversation): void
    {
        $conversation->archived = !$conversation->archived;
        $conversation->save();

        $this->redirect(route('doctalk.chat'), true);
    }

    #[Renderless]
    public function rename(Conversation $conversation, $name): void
    {
        if (trim($conversation->name) === trim($name)) {
            return;
        }

        if (!trim($name)) {
            return;
        }

        if (strlen($name) < 4 || strlen($name) > 30) {

            $this->dispatch('flashMessage', [
                'message' => 'Conversation title must be between 4 to 30 characters.',
                'type' => 'error'
            ]);

            return;
        }

        $conversation->update(['name' => $name]);

        $this->dispatch('conversationsUpdated');

        $this->dispatch('flashMessage', [
            'message' => 'Conversation re-named successfully.',
            'type' => 'success'
        ]);
    }

    #[Renderless]
    public function delete(Conversation $conversation): void
    {
        $conversation->delete();

        // if it is active conversation, we redirect instead to avoid 404
        if ($this->conversation && $this->conversation->id === $conversation->id) {
            $this->redirect(route('doctalk.chat'), true);
        } else {
            $this->dispatch('conversationsUpdated');

            $this->dispatch('flashMessage', [
                'message' => 'Conversation deleted successfully.',
                'type' => 'success'
            ]);
        }
    }
}
