<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class Dashboard extends Component
{
    #[Title('Student Organization Dashboard')]
    #[Layout('layouts.student-org-layout')]

    public function render()
    {
        return view('livewire.student-org.dashboard');
    }
}
