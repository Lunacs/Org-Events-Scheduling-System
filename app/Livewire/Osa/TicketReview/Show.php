<?php

namespace App\Livewire\Osa\TicketReview;

use App\Models\Event;
use App\Models\Event_Schedule;
use App\Models\Office_Approval;
use App\Models\OSA_Approval;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\TicketCommentNotification;
use App\Notifications\TicketForwardedToGsoNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast;

    #[Title('Ticket Review - OSA Admin')]
    #[Layout('components.layouts.app')]
    public Ticket $ticket;

    public $comment = '';

    // Remarks for each action
    public $approvalRemarks = '';

    public $rejectionRemarks = '';

    public $revisionRemarks = '';

    public $forwardRemarks = '';

    public $finalApprovalRemarks = '';

    public $finalRejectionRemarks = '';

    public function mount($ticketNumber)
    {
        // Optimize: Select only needed columns from tickets table
        $this->ticket = Ticket::select([
            'ticket_id', 'ticket_number', 'title', 'description', 'status', 'date_from', 'date_to',
            'time_from', 'time_to', 'venue_requested', 'plv_participants', 'external_participants', 'total_participants',
            'additional_notes', 'user_id', 'event_type_id', 'fund_source_id', 'created_at', 'updated_at'
        ])->with([
            'user:user_id,name,email,role_id,org_id,position_id,avatar_style,avatar_seed',
            'user.role:role_id,role_name',
            'user.studentOrganization:org_id,org_name,org_code,course_id',
            'user.studentOrganization.course:course_id,course_name',
            'user.position:position_id,position_name',
            'events:event_id,ticket_id,event__type_id,notes',
            'events.eventSchedules:schedule_id,event_id,start_date,end_date,start_time,end_time,venue,status',
            'attachments:attachment_id,ticket_id,file_path,file_name,file_type',
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

    // Open/close modal methods removed; handled by Alpine.js

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
        $event = Event::create([
            'ticket_id' => $this->ticket->ticket_id,
            'event__type_id' => $this->ticket->event_type_id,
            'notes' => 'Event created from approved ticket',
        ]);

        // Create Event Schedule record
        Event_Schedule::create([
            'event_id' => $event->event_id,
            'start_date' => $this->ticket->date_from,
            'end_date' => $this->ticket->date_to,
            'start_time' => $this->ticket->time_from,
            'end_time' => $this->ticket->time_to,
            'venue' => $this->ticket->venue_requested,
            'status' => 'approved',
            'remarks' => 'Schedule created from approved ticket',
        ]);

        // Client-side modal closing handled via Alpine.js

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role', 'events.eventSchedules');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'approved');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Approved',
            'message' => "Your ticket {$this->ticket->ticket_number} has been approved!",
            'type' => 'success',
        ]);

        $this->success('Ticket has been approved and event has been created successfully.');
        $this->dispatch('ticket-approved');
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

        // Notify all GSO users in the GSO office about the forwarded ticket
        // Optimize: Cache GSO users query and select only needed columns
        $gsoUsers = \Illuminate\Support\Facades\Cache::remember('gso_users_notifications', 3600, function () {
            return User::select(['user_id', 'name', 'email', 'role_id', 'office_id'])
                ->where('role_id', User::ROLE_GSO)
                ->where('office_id', 1)
                ->get();
        });

        foreach ($gsoUsers as $gsoUser) {
            $gsoUser->notify(new TicketForwardedToGsoNotification(
                $this->ticket,
                $this->forwardRemarks
            ));
        }

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'gso_review');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Forwarded to GSO',
            'message' => "Your ticket {$this->ticket->ticket_number} has been forwarded to GSO for review.",
            'type' => 'info',
        ]);

        $this->success('Ticket has been forwarded to GSO for approval.');
        $this->dispatch('ticket-forwarded');
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

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'needs_revision');
        $this->dispatch('notification-received', [
            'title' => 'Revision Requested',
            'message' => "Your ticket {$this->ticket->ticket_number} needs revision. Please check the remarks.",
            'type' => 'warning',
        ]);

        $this->warning('Ticket has been sent back for revision.');
        $this->dispatch('ticket-revision-requested');
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

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'rejected');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Rejected',
            'message' => "Your ticket {$this->ticket->ticket_number} has been rejected. Please check the remarks.",
            'type' => 'error',
        ]);

        $this->error('Ticket has been rejected.');
        $this->dispatch('ticket-rejected');
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
        $event = Event::create([
            'ticket_id' => $this->ticket->ticket_id,
            'event__type_id' => $this->ticket->event_type_id,
            'notes' => 'Event created from approved ticket after GSO review',
        ]);

        // Create Event Schedule record
        Event_Schedule::create([
            'event_id' => $event->event_id,
            'start_date' => $this->ticket->date_from,
            'end_date' => $this->ticket->date_to,
            'start_time' => $this->ticket->time_from,
            'end_time' => $this->ticket->time_to,
            'venue' => $this->ticket->venue_requested,
            'status' => 'approved',
            'remarks' => 'Schedule created from approved ticket after GSO review',
        ]);

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role', 'events.eventSchedules');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'approved');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Finally Approved',
            'message' => "Your ticket {$this->ticket->ticket_number} has been finally approved after GSO review!",
            'type' => 'success',
        ]);

        $this->success('Ticket has been approved and event has been created successfully.');
        $this->dispatch('ticket-final-approved');
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

        // Client-side modal closing handled via Alpine.js

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'rejected');
        $this->dispatch('notification-received', [
            'title' => 'Ticket Finally Rejected',
            'message' => "Your ticket {$this->ticket->ticket_number} has been finally rejected after GSO review.",
            'type' => 'error',
        ]);

        $this->error('Ticket has been rejected after GSO review.');
        $this->dispatch('ticket-final-rejected');
    }

    public function addComment()
    {
        $this->validate(
            ['comment' => 'required|string|min:3|max:1000'],
            [
                'comment.required' => 'Please enter a comment.',
                'comment.min' => 'Comment must be at least 3 characters.',
                'comment.max' => 'Comment cannot exceed 1000 characters.',
            ]
        );

        // Create comment
        $newComment = TicketComment::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'content' => $this->comment,
        ]);

        // Clear the input
        $this->comment = '';

        // Reload only the comment relationship with minimal data
        $this->ticket->load([
            'comments:id,ticket_id,user_id,content,created_at',
            'comments.user:user_id,name,role_id,avatar_style,avatar_seed',
            'comments.user.role:role_id,role_name',
        ]);

        // Notify relevant parties (server-side concern)
        $this->notifyCommentAdded($newComment);

        // Simple success message (no heavy notifications)
        $this->success('Comment added successfully.');

        // Dispatch event for avatar initialization (client-side UI)
        $this->dispatch('comment-added');
    }

    /**
     * Notify relevant users when a comment is added
     * OSA comment → Notify ticket owner (Student Org)
     * Student Org comment → Notify OSA
     * If ticket forwarded to GSO → Also notify GSO
     */
    private function notifyCommentAdded(TicketComment $comment)
    {
        $commenter = auth()->user();
        $ticketOwner = $this->ticket->user;

        // Don't notify the person who made the comment
        $usersToNotify = collect();

        // Always notify the ticket owner if they didn't make the comment
        if ($ticketOwner->user_id !== $commenter->user_id) {
            $usersToNotify->push($ticketOwner);
        }

        // If commenter is Student Org, notify OSA users
        // Optimize: Cache OSA users query and select only needed columns
        if ($commenter->isStudentOrg()) {
            $osaUsers = \Illuminate\Support\Facades\Cache::remember('osa_users_notifications', 3600, function () {
                return User::select(['user_id', 'name', 'email', 'role_id'])
                    ->where('role_id', User::ROLE_OSA)
                    ->get();
            });
            $usersToNotify = $usersToNotify->merge($osaUsers);
        }

        // If ticket is in GSO review status, notify GSO users (but only if they didn't comment)
        // if (in_array($this->ticket->status, ['gso_review', 'pending_osa_approval'])) {
        //     $gsoUsers = User::where('role_id', User::ROLE_GSO)
        //         ->where('user_id', '!=', $commenter->user_id)
        //         ->get();
        //     $usersToNotify = $usersToNotify->merge($gsoUsers);
        // }

        // Send DB + broadcast immediately; queue mail separately to avoid UI delay
        $usersToNotify->unique('user_id')->each(function ($user) use ($comment, $commenter) {
            // immediate
            $user->notifyNow(new TicketCommentNotification($this->ticket, $comment, $commenter, ['database', 'broadcast']));

            // queued mail only
            $user->notify(new TicketCommentNotification($this->ticket, $comment, $commenter, ['mail']));
        });

        // Dispatch real-time notification event
        if ($usersToNotify->isNotEmpty()) {
            $this->dispatch('refresh-notifications');
        }
    }

    /**
     * Generate a temporary URL and open in a new tab for preview.
     */
    public function previewAttachment(int $attachmentId): void
    {
        $attachment = $this->ticket->attachments->firstWhere('attachment_id', $attachmentId);

        if (!$attachment) {
            $this->warning('Attachment not found.');

            return;
        }

        $url = $this->makeTemporaryUrl($attachment->file_path, $attachment->file_name, false);

        $this->dispatch('open-attachment-preview', url: $url);
    }

    /**
     * Generate a temporary URL that forces download and dispatch to client.
     */
    public function downloadAttachment(int $attachmentId): void
    {
        $attachment = $this->ticket->attachments->firstWhere('attachment_id', $attachmentId);

        if (!$attachment) {
            $this->warning('Attachment not found.');

            return;
        }

        $url = $this->makeTemporaryUrl($attachment->file_path, $attachment->file_name, true);

        $this->dispatch('download-attachment', url: $url);
    }

    /**
     * Build a temporary URL from the configured filesystem. Falls back to public URL if unsupported.
     */
    private function makeTemporaryUrl(string $path, string $filename, bool $forceDownload = false): string
    {
        $disk = Storage::disk(config('filesystems.default'));

        try {
            if (method_exists($disk, 'temporaryUrl')) {
                $options = [
                    'ResponseContentDisposition' => ($forceDownload ? 'attachment' : 'inline') . '; filename="' . addslashes($filename) . '"',
                ];

                return $disk->temporaryUrl($path, now()->addMinutes(5), $options);
            }
        } catch (\Throwable $e) {
            // Fallback below if temporary URLs are unavailable for the disk
        }

        return Storage::url($path);
    }

    public function render()
    {
        return view('livewire.osa.ticket-review.show');
    }
}
