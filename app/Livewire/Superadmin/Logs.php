<?php

namespace App\Livewire\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

class Logs extends Component
{
    #[Title('Superadmin - Transaction Logs')]
    #[Layout('layouts.superadmin')]
    public function render()
    {
        return view('livewire.superadmin.logs');
    }
}
