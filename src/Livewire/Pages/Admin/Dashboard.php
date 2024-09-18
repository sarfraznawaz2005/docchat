<?php

namespace Package\DocTalk\Livewire\Pages\Admin;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('doctalk::components.layouts.doctalk')]
class Dashboard extends Component
{
    #[Title('Dashboard')]
    public function render(): View|Factory|Application
    {
        return view('doctalk::livewire.pages.admin.dashboard');
    }
}
