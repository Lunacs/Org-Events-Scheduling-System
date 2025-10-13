<?php

namespace App\Livewire\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

class Roles extends Component
{
    #[Title('Superadmin - Roles & Permissions')]
    #[Layout('layouts.superadmin')]
    public function render()
    {
        return view('livewire.superadmin.roles');
    }
}
