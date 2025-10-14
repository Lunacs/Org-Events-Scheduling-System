<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

class MyTicket extends Component
{
    #[Title('My Ticket - Student Organization')]
    #[Layout('layouts.student-org-layout')]
    public function render()
    {
        return view('livewire.student-org.my-ticket');
    }
}
