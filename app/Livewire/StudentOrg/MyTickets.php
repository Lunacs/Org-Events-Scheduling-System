<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

class MyTickets extends Component
{
    #[Title('My Tickets - Student Organization')]
    #[Layout('layouts.student-org-layout')]

    public $search = '';
    public $statusFilter = '';
    public $dateFilter = '';

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFilter = '';
    }

    public function render()
    {
        return view('livewire.student-org.my-tickets');
    }
}
