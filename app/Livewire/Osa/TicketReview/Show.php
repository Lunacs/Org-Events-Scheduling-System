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
            'user:user_id,name,email,role_id,org_id,position_id,avatar_style,avatar_seed',
            'user.role:role_id,role_name',
            'user.studentOrganization:org_id,org_name,org_code',
            'user.studentOrganization.course:course_id,course_name',
            'user.position:position_id,position_name',
            'events:event_id,ticket_id,event__type_id,notes',
            'events.eventSchedules:schedule_id,event_id,start_date,end_date,start_time,end_time,venue,status',
            'attachments:attachment_id,ticket_id,file_path,file_name',
            'eventType:event_type_id,type_name',
            'fundSource:source_id,source_name',
            'comments:id,ticket_id,user_id,content,created_at',
            'comments.user:user_id,name,role_id,avatar_style,avatar_seed',
            'comments.user.role:role_id,role_name',
            'osaApprovals:osa_approval_id,ticket_id,user_id,decision,remarks,created_at',
            'osaApprovals.user:user_id,name,role_id,avatar_style,avatar_seed',
            'osaApprovals.user.role:role_id,role_name',
            'officeApprovals:id,ticket_id,office_id,user_id,decision,remarks,created_at',
            'officeApprovals.office:office_id,office_name',
            'officeApprovals.user:user_id,name,role_id,avatar_style,avatar_seed',
            'officeApprovals.user.role:role_id,role_name',
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
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role', 'events.eventSchedules');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'approved');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Approved',
            'message' => "Your ticket {$this->ticket->ticket_number} has been approved!",
            'type' => 'success',
        ])->to($this->ticket->user);

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
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'gso_review');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Forwarded to GSO',
            'message' => "Your ticket {$this->ticket->ticket_number} has been forwarded to GSO for review.",
            'type' => 'info',
        ])->to($this->ticket->user);

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
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'needs_revision');
        $this->dispatch('notification-received', [
            'title' => 'Revision Requested',
            'message' => "Your ticket {$this->ticket->ticket_number} needs revision. Please check the remarks.",
            'type' => 'warning',
        ])->to($this->ticket->user);

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
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'rejected');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Rejected',
            'message' => "Your ticket {$this->ticket->ticket_number} has been rejected. Please check the remarks.",
            'type' => 'error',
        ])->to($this->ticket->user);

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
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role', 'events.eventSchedules');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'approved');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Finally Approved',
            'message' => "Your ticket {$this->ticket->ticket_number} has been finally approved after GSO review!",
            'type' => 'success',
        ])->to($this->ticket->user);

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
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'rejected');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Finally Rejected',
            'message' => "Your ticket {$this->ticket->ticket_number} has been finally rejected after GSO review.",
            'type' => 'error',
        ])->to($this->ticket->user);

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
        $this->ticket->load('comments.user.role');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('notification-received', [
            'title' => 'New Comment Added',
            'message' => "A new comment has been added to ticket {$this->ticket->ticket_number}.",
            'type' => 'info',
        ]);

        session()->flash('success', 'Your comment has been added successfully.');
        $this->dispatch('comment-added');
    }

    public function render()
    {
        return view('livewire.osa.ticket-review.show');
    }
}
