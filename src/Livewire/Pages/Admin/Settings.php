<?php

namespace Package\DocTalk\Livewire\Pages\Admin;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Package\DocTalk\Models\Setting;
use Throwable;

#[Layout('doctalk::components.layouts.doctalk')]
class Settings extends Component
{
    #[Validate('integer')]
    public int $days;

    #[Title('Settings')]
    public function render(): View|Factory|Application
    {
        $this->days = Setting::query()->where('key', 'delete_conversations_after_days')->first()?->value ?? 0;

        return view('doctalk::livewire.pages.admin.settings');
    }

    public function saveSettings(): void
    {
        $this->validate();

        $this->resetErrorBag();
        $this->resetValidation();

        Setting::query()->updateOrCreate(['key' => 'delete_conversations_after_days'], ['value' => $this->days]);

        $this->dispatch('flashMessage', [
            'message' => 'Saved successfully.',
            'type' => 'success'
        ]);
    }

    #[Renderless]
    public function deleteAll(): void
    {
        try {

            DB::connection(config('doctalk.db_connection'))->transaction(function () {
                Schema::connection(config('doctalk.db_connection'))->disableForeignKeyConstraints();
                DB::connection(config('doctalk.db_connection'))->statement('TRUNCATE TABLE documents, conversations, messages RESTART IDENTITY CASCADE');
                Schema::connection(config('doctalk.db_connection'))->enableForeignKeyConstraints();
            });

            $this->dispatch('flashMessage', [
                'message' => 'All chat data deleted successfully.',
                'type' => 'success'
            ]);

        } catch (Throwable $e) {
            $this->dispatch('flashMessage', [
                'message' => 'Error: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }
}
