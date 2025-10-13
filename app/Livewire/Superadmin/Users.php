<?php

namespace App\Livewire\Superadmin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Users extends Component
{
    #[Title('Superadmin - User Management')]
    #[Layout('layouts.superadmin')]
    public function render()
    {
        return view('livewire.superadmin.users');
    }
}
