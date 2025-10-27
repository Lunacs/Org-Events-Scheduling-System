<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class MyTicket extends Component
{
    use WithPagination;

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
        $ticketsQuery = auth()->user()->tickets()->with('eventType')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('ticket_number', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');
        return view('livewire.student-org.my-ticket', [
            'allTickets' => clone $ticketsQuery->get(),
            'tickets' => $ticketsQuery->paginate(10),
        ]);
    }
}
