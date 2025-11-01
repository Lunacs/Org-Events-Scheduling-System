<?php

namespace App\Livewire\StudentOrg;

use App\Models\TicketComment;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class MyTicket extends Component
{
    use WithPagination;

    #[Title('My Ticket - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]
    public $search = '';
    public $statusFilter = '';
    public $dateFilter = '';
    public $showDetailsModal = false;
    public $showCommentsModal = false;
    public $showEditDrawer = false;
    public $selectedTicketId;
    public $comment = '';

    #[On('open-ticket-details')]
    public function openDetailsModal($ticketId = null)
    {
        $this->selectedTicketId = $ticketId;
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedTicketId = null;
    }

    #[On('open-comment-section')]
    public function openCommentsModal($ticketId = null)
    {
        $this->selectedTicketId = $ticketId;
        $this->showCommentsModal = true;
    }

    public function closeCommentsModal()
    {
        $this->showCommentsModal = false;
        $this->selectedTicketId = null;
    }

    #[On('open-ticket-edit')]
    public function openEditDrawer($ticketId = null)
    {
        $this->selectedTicketId = $ticketId;
        $this->showEditDrawer = true;

        // Dispatch event to edit form component to load ticket data
        $this->dispatch('load-ticket-for-edit', ticketId: $ticketId);
    }

    public function closeEditDrawer()
    {
        $this->showEditDrawer = false;
        $this->selectedTicketId = null;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFilter = '';
    }

    public function getSelectedTicketProperty()
    {
        if (!$this->selectedTicketId) {
            \Log::info('No ticket ID set');
            return null;
        }

        return auth()->user()->tickets()
            ->with(['eventType', 'comments', 'attachments'])
            ->find($this->selectedTicketId);
    }

    public function getSelectedTicketCommentsProperty()
    {
        if (!$this->selectedTicketId) {
            \Log::info('No ticket ID set for comments');
            return null;
        }

        return auth()->user()->tickets()
            ->find($this->selectedTicketId)
            ?->comments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function addComment()
    {
        if (!$this->selectedTicketId) {
            session()->flash('warning', 'No ticket selected.');
            return;
        }

        if (empty(trim($this->comment))) {
            session()->flash('warning', 'Please enter a comment.');
            return;
        }

        TicketComment::create([
            'ticket_id' => $this->selectedTicketId,
            'user_id' => auth()->id(),
            'content' => $this->comment,
        ]);

        $this->comment = '';

        session()->flash('success', 'Your comment has been added successfully.');
        $this->dispatch('comment-added');
    }

    public function render()
    {
        $allTickets = auth()->user()->tickets()->with('eventType')->get();
        $ticketsQuery = auth()->user()->tickets()->with('eventType')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('ticket_number', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');

        return view('livewire.student-org.my-ticket', [
            'allTickets' => $allTickets,
            'tickets' => $ticketsQuery->paginate(10),
        ]);
    }
}
