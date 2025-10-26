<?php

namespace App\Livewire\Osa\TicketReview;

use App\Models\Office_Approval;
use App\Models\Ticket;
use App\Models\TicketComment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Show extends Component
{
    #[Title('Ticket Review - OSA Admin')]
    #[Layout('components.layouts.app')]
    public Ticket $ticket;

    public $comment = '';

    public function mount($ticketNumber)
    {
        $this->ticket = Ticket::with([
            'user.studentOrganization',
            'events.eventSchedules',
            'attachments',
            'eventType',
            'comments.user',
        ])->where('ticket_number', $ticketNumber)->firstOrFail();
    }

    public function approveTicket()
    {
        $this->ticket->update(['status' => 'approved']);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Success',
            'description' => 'Ticket has been approved successfully.',
        ]);
    }

    public function forwardToGso()
    {
        // update ticket status to forward_to_gso
        $this->ticket->update(['status' => 'forward_to_gso']);

        Office_Approval::create([
            'ticket_id' => $this->ticket->ticket_id,
            'office_id' => 1,
            'user_id' => $this->ticket->user_id,
            'decision' => 'pending',
            'remarks' => 'Ticket has been forwarded to GSO for approval.',
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Success',
            'description' => 'Ticket has been forwarded to GSO for approval.',
        ]);
    }

    public function requestRevision()
    {
        $this->ticket->update(['status' => 'under_review']);

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Revision Requested',
            'description' => 'Ticket has been sent back for revision.',
        ]);
    }

    public function rejectTicket()
    {
        $this->ticket->update(['status' => 'rejected']);

        $this->dispatch('toast', [
            'type' => 'error',
            'title' => 'Ticket Rejected',
            'description' => 'Ticket has been rejected.',
        ]);
    }

    public function addComment()
    {
        if (empty(trim($this->comment))) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'title' => 'Warning',
                'description' => 'Please enter a comment.',
            ]);

            return;
        }

        TicketComment::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'content' => $this->comment,
        ]);

        $this->comment = '';
        $this->ticket->load('comments.user');

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Comment Added',
            'description' => 'Your comment has been added successfully.',
        ]);
    }

    public function render()
    {
        return view('livewire.osa.ticket-review.show');
    }
}
