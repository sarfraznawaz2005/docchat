<?php

namespace Package\DocTalk\Livewire\Pages\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Package\DocTalk\Models\Document;
use Package\DocTalk\Traits\UploadDocs;

#[Layout('doctalk::components.layouts.doctalk')]
class AddDocs extends Component
{
    use UploadDocs;
    use WithPagination;

    public string $searchQuery = '';

    public string $sortField = 'id';
    public bool $sortAsc = false;

    #[Title('Manage Documents')]
    public function render(): View|Factory|Application
    {
        return view('doctalk::livewire.pages.admin.add-docs');
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    #[Computed]
    public function documents(): LengthAwarePaginator
    {
        return Document::query()
            ->select(['id', 'content', 'llm', 'metadata', 'created_at'])
            ->where('content', 'like', '%' . $this->searchQuery . '%')
            ->orWhere('metadata', 'like', '%' . $this->searchQuery . '%')
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(25);
    }

    public function delete(Document $document): void
    {
        $document->delete();

        $this->dispatch('flashMessage', [
            'message' => 'Document chunk deleted successfully.',
            'type' => 'success'
        ]);
    }
}
