<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.public')]
#[Title('Data Privacy Notice')]
class DataPrivacyNotice extends Component
{
    public function render()
    {
        return view('livewire.data-privacy-notice');
    }
}
