<?php

namespace Package\DocTalk\Livewire\Chat;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

class Embed extends Component
{
    public function render(): View|Factory|Application
    {
        return view('doctalk::livewire.chat.embed');
    }
}
