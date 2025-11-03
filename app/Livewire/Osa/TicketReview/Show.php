<?php

namespace App\Livewire\Osa\TicketReview;

use App\Models\Office_Approval;
use App\Models\OSA_Approval;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Notifications\TicketStatusUpdatedNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Show extends Component
{
    #[Title('Ticket Review - OSA Admin')]
    #[Layout('components.layouts.app')]
    public Ticket $ticket;

    public $comment = '';

    // Modal states
    public $showApprovalModal = false;

    public $showRejectionModal = false;

    public $showRevisionModal = false;

    public $showForwardModal = false;

    public $showFinalApprovalModal = false;

    public $showFinalRejectionModal = false;

    // Remarks for each action
    public $approvalRemarks = '';

    public $rejectionRemarks = '';

    public $revisionRemarks = '';

    public $forwardRemarks = '';

    public $finalApprovalRemarks = '';

    public $finalRejectionRemarks = '';

    public function mount($ticketNumber)
    {
        $this->ticket = Ticket::with([
            'user.studentOrganization.course',
            'user.position',
            'events.eventSchedules',
            'attachments',
            'eventType',
            'fundSource',
            'comments.user',
            'osaApprovals.user',
            'officeApprovals.office',
            'officeApprovals.user',
        ])->where('ticket_number', $ticketNumber)->firstOrFail();
    }

    public function openApprovalModal()
    {
        $this->showApprovalModal = true;
        $this->approvalRemarks = '';
    }

    public function closeApprovalModal()
    {
        $this->showApprovalModal = false;
        $this->approvalRemarks = '';
    }

    public function approveTicket()
    {
        // Validate remarks
        $this->validate([
            'approvalRemarks' => 'required|string|min:3',
        ], [
            'approvalRemarks.required' => 'Please provide remarks for approval.',
            'approvalRemarks.min' => 'Remarks must be at least 3 characters.',
        ]);

        $oldStatus = $this->ticket->status;

        // Update ticket status to approved
        $this->ticket->update(['status' => 'approved']);

        // Notify ticket owner about status change
        $this->ticket->user->notify(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            'approved',
            $this->approvalRemarks
        ));

        // Create OSA approval record
        OSA_Approval::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'approved',
            'remarks' => $this->approvalRemarks,
        ]);

        // Create a copy back to OSA approvals with pending status for tracking
        OSA_Approval::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'pending',
            'remarks' => 'Ticket approved - pending post-approval review',
        ]);

        // Create Event record
        $event = \App\Models\Event::create([
            'ticket_id' => $this->ticket->ticket_id,
            'event__type_id' => $this->ticket->event_type_id,
            'notes' => 'Event created from approved ticket',
        ]);

        // Create Event Schedule record
        \App\Models\Event_Schedule::create([
            'event_id' => $event->event_id,
            'start_date' => $this->ticket->date_from,
            'end_date' => $this->ticket->date_to,
            'start_time' => $this->ticket->time_from,
            'end_time' => $this->ticket->time_to,
            'venue' => $this->ticket->venue_requested,
            'status' => 'approved',
            'remarks' => 'Schedule created from approved ticket',
        ]);

        // Close modal and reset
        $this->closeApprovalModal();

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user', 'officeApprovals.office', 'officeApprovals.user', 'events.eventSchedules');

        // Fix notification dispatch
        session()->flash('success', 'Ticket has been approved and event has been created successfully.');
        $this->dispatch('ticket-approved');
    }

    public function openForwardModal()
    {
        $this->showForwardModal = true;
        $this->forwardRemarks = '';
    }

    public function closeForwardModal()
    {
        $this->showForwardModal = false;
        $this->forwardRemarks = '';
    }

    public function forwardToGso()
    {
        // Validate remarks
        $this->validate([
            'forwardRemarks' => 'required|string|min:3',
        ], [
            'forwardRemarks.required' => 'Please provide remarks for forwarding to GSO.',
            'forwardRemarks.min' => 'Remarks must be at least 3 characters.',
        ]);

        $oldStatus = $this->ticket->status;

        // update ticket status to gso_review
        $this->ticket->update(['status' => 'gso_review']);

        // Notify ticket owner about status change
        $this->ticket->user->notify(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            'gso_review',
            $this->forwardRemarks
        ));

        // Create OSA approval record showing it was forwarded
        OSA_Approval::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'forwarded',
            'remarks' => $this->forwardRemarks,
        ]);

        Office_Approval::create([
            'ticket_id' => $this->ticket->ticket_id,
            'office_id' => 1, // GSO office ID
            'user_id' => auth()->id(),
            'decision' => 'pending',
            'remarks' => $this->forwardRemarks,
        ]);

        // Close modal and reset
        $this->closeForwardModal();

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user', 'officeApprovals.office', 'officeApprovals.user');

        // Fix notification dispatch
        session()->flash('success', 'Ticket has been forwarded to GSO for approval.');
        $this->dispatch('ticket-forwarded');
    }

    public function openRevisionModal()
    {
        $this->showRevisionModal = true;
        $this->revisionRemarks = '';
    }

    public function closeRevisionModal()
    {
        $this->showRevisionModal = false;
        $this->revisionRemarks = '';
    }

    public function requestRevision()
    {
        // Validate remarks
        $this->validate([
            'revisionRemarks' => 'required|string|min:10',
        ], [
            'revisionRemarks.required' => 'Please provide detailed remarks explaining what needs to be revised.',
            'revisionRemarks.min' => 'Remarks must be at least 10 characters to provide clear guidance.',
        ]);

        $oldStatus = $this->ticket->status;

        $this->ticket->update(['status' => 'needs_revision']);

        // Notify ticket owner about status change
        $this->ticket->user->notify(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            'needs_revision',
            $this->revisionRemarks
        ));

        // Create OSA approval record for revision request
        OSA_Approval::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'revision_requested',
            'remarks' => $this->revisionRemarks,
        ]);

        // Close modal and reset
        $this->closeRevisionModal();

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user', 'officeApprovals.office', 'officeApprovals.user');

        // Fix notification dispatch
        session()->flash('info', 'Ticket has been sent back for revision.');
        $this->dispatch('ticket-revision-requested');
    }

    public function openRejectionModal()
    {
        $this->showRejectionModal = true;
        $this->rejectionRemarks = '';
    }

    public function closeRejectionModal()
    {
        $this->showRejectionModal = false;
        $this->rejectionRemarks = '';
    }

    public function rejectTicket()
    {
        // Validate remarks
        $this->validate([
            'rejectionRemarks' => 'required|string|min:10',
        ], [
            'rejectionRemarks.required' => 'Please provide detailed remarks explaining the reason for rejection.',
            'rejectionRemarks.min' => 'Remarks must be at least 10 characters to provide clear reasoning.',
        ]);

        $oldStatus = $this->ticket->status;

        $this->ticket->update(['status' => 'rejected']);

        // Create OSA approval record for rejection
        OSA_Approval::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'rejected',
            'remarks' => $this->rejectionRemarks,
        ]);

        // Notify the ticket owner about status change
        $this->ticket->user->notify(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            'rejected',
            $this->rejectionRemarks
        ));

        // Close modal and reset
        $this->closeRejectionModal();

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user', 'officeApprovals.office', 'officeApprovals.user');

        // Fix notification dispatch
        session()->flash('error', 'Ticket has been rejected.');
        $this->dispatch('ticket-rejected');
    }

    public function openFinalApprovalModal()
    {
        $this->showFinalApprovalModal = true;
        $this->finalApprovalRemarks = '';
    }

    public function closeFinalApprovalModal()
    {
        $this->showFinalApprovalModal = false;
        $this->finalApprovalRemarks = '';
    }

    public function finalApproval()
    {
        // Validate remarks
        $this->validate([
            'finalApprovalRemarks' => 'required|string|min:3',
        ], [
            'finalApprovalRemarks.required' => 'Please provide remarks for final approval.',
            'finalApprovalRemarks.min' => 'Remarks must be at least 3 characters.',
        ]);

        // This is called when OSA makes final decision after GSO review
        $oldStatus = $this->ticket->status;

        // Update ticket status to approved
        $this->ticket->update(['status' => 'approved']);

        // Always create a new OSA approval record to maintain audit trail
        // Never update existing records to preserve history
        OSA_Approval::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'approved',
            'remarks' => $this->finalApprovalRemarks,
        ]);

        // Create a copy back to OSA approvals with pending status for tracking
        OSA_Approval::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'pending',
            'remarks' => 'Ticket approved after GSO review - pending post-approval review',
        ]);

        // Notify ticket owner about status change
        $this->ticket->user->notify(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            'approved',
            $this->finalApprovalRemarks
        ));

        // Create Event record
        $event = \App\Models\Event::create([
            'ticket_id' => $this->ticket->ticket_id,
            'event__type_id' => $this->ticket->event_type_id,
            'notes' => 'Event created from approved ticket after GSO review',
        ]);

        // Create Event Schedule record
        \App\Models\Event_Schedule::create([
            'event_id' => $event->event_id,
            'start_date' => $this->ticket->date_from,
            'end_date' => $this->ticket->date_to,
            'start_time' => $this->ticket->time_from,
            'end_time' => $this->ticket->time_to,
            'venue' => $this->ticket->venue_requested,
            'status' => 'approved',
            'remarks' => 'Schedule created from approved ticket after GSO review',
        ]);

        // Close modal and reset
        $this->closeFinalApprovalModal();

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user', 'officeApprovals.office', 'officeApprovals.user', 'events.eventSchedules');

        // Fix notification dispatch
        session()->flash('success', 'Ticket has been approved and event has been created successfully.');
        $this->dispatch('ticket-final-approved');
    }

    public function openFinalRejectionModal()
    {
        $this->showFinalRejectionModal = true;
        $this->finalRejectionRemarks = '';
    }

    public function closeFinalRejectionModal()
    {
        $this->showFinalRejectionModal = false;
        $this->finalRejectionRemarks = '';
    }

    public function finalRejection()
    {
        // Validate remarks
        $this->validate([
            'finalRejectionRemarks' => 'required|string|min:10',
        ], [
            'finalRejectionRemarks.required' => 'Please provide detailed remarks explaining the reason for final rejection.',
            'finalRejectionRemarks.min' => 'Remarks must be at least 10 characters to provide clear reasoning.',
        ]);

        // This is called when OSA makes final decision to reject after GSO review
        $oldStatus = $this->ticket->status;

        $this->ticket->update(['status' => 'rejected']);

        // Always create a new OSA approval record to maintain audit trail
        // Never update existing records to preserve history
        OSA_Approval::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'decision' => 'rejected',
            'remarks' => $this->finalRejectionRemarks,
        ]);

        // Notify ticket owner about status change
        $this->ticket->user->notify(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            'rejected',
            $this->finalRejectionRemarks
        ));

        // Close modal and reset
        $this->closeFinalRejectionModal();

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user', 'officeApprovals.office', 'officeApprovals.user');

        // Fix notification dispatch
        session()->flash('error', 'Ticket has been rejected after GSO review.');
        $this->dispatch('ticket-final-rejected');
    }

    public function addComment()
    {
        if (empty(trim($this->comment))) {
            session()->flash('warning', 'Please enter a comment.');

            return;
        }

        TicketComment::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'content' => $this->comment,
        ]);

        $this->comment = '';
        $this->ticket->load('comments.user');

        session()->flash('success', 'Your comment has been added successfully.');
        $this->dispatch('comment-added');
    }

    public function render()
    {
        return view('livewire.osa.ticket-review.show');
    }
}
