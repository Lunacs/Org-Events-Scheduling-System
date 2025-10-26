<?php

namespace App\Livewire\Osa;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\Ticket;
use App\Models\Attachment;

class TicketReview extends Component
{
    use WithPagination;

    #[Title('Ticket Review & Attachments - OSA Admin')]
    #[Layout('components.layouts.app')]

    public $selectedTicket = null;
    public $showModal = false;
    
    #[Url(except: '')]
    public $search = '';
    
    #[Url(except: 'pending')]
    public $statusFilter = 'pending';

    public function viewTicket($ticketId)
    {
        $this->selectedTicket = Ticket::select([
                'ticket_id', 'ticket_number', 'title', 'description', 'status',
                'date-requested', 'venue-requested', 'user_id', 'event_type_id'
            ])
            ->with([
                'user' => fn($q) => $q->select(['user_id', 'org_id'])
                    ->with('studentOrganization:org_id,org_name'),
                'events:event_id,ticket_id,title,expected_attendees,venue',
                'attachments:attachment_id,ticket_id,file_path,file_name',
                'eventType:event_type_id,type_name'
            ])
            ->find($ticketId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTicket = null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = 'pending';
        $this->resetPage();
    }

    public function render()
    {
        $tickets = Ticket::select([
                'ticket_id', 'ticket_number', 'title', 'description', 'status', 'created_at', 'user_id', 'event_type_id'
            ])
            ->with([
                'user' => fn($q) => $q->select(['user_id', 'org_id'])
                    ->with('studentOrganization:org_id,org_name'),
                'events:event_id,ticket_id',
                'attachments:attachment_id,ticket_id',
                'eventType:event_type_id,type_name'
            ])
            ->withCount(['events', 'attachments'])
            ->when($this->search, fn($query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($query) => $query->where('status', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.osa.ticket-review', compact('tickets'));
    }
}
