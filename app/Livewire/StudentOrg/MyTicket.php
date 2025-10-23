<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

class MyTicket extends Component
{
    #[Title('My Ticket - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

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
        $tickets = auth()->user()->tickets()->with('eventType')->orderBy('created_at', 'desc');
        return view('livewire.student-org.my-ticket', [
            'tickets' => $tickets->get(),
        ]);
    }
}
