<?php

namespace App\Livewire\Osa\TicketReview;

use App\Models\Event;
use App\Models\Event_Schedule;
use App\Models\Office_Approval;
use App\Models\OSA_Approval;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketForwardedToGsoNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast;

    #[Title('Ticket Details - OSA Admin')]
    #[Layout('components.layouts.app')]
    public Ticket $ticket;

    // Remarks for each action
    public $approvalRemarks = '';

    public $revisionRemarks = '';

    public $forwardRemarks = '';

    public $finalApprovalRemarks = '';

    public function mount($ticketNumber)
    {
        // Optimize: Select only needed columns from tickets table
        $this->ticket = Ticket::select([
            'ticket_id',
            'ticket_number',
            'title',
            'description',
            'status',
            'date_from',
            'date_to',
            'time_from',
            'time_to',
            'venue_requested',
            'alternate_venue',
            'special_requirements',
            'plv_participants',
            'external_participants',
            'total_participants',
            'estimated_budget',
            'budget_breakdown',
            'igp_requested',
            'igp_details',
            'oc_accommodation',
            'oc_tsp',
            'oc_driver_name',
            'oc_driver_contact_number',
            'oc_transportation_type',
            'oc_vehicle_plate_number',
            'additional_notes',
            'proponent_contact',
            'adviser_contact',
            'user_id',
            'event_type_id',
            'fund_source_id',
            'created_at',
            'updated_at',
        ])->with([
            'user:user_id,name,email,role_id,org_id,position_id,avatar_style,avatar_seed',
            'user.role:role_id,role_name',
            'user.studentOrganization:org_id,org_name,org_code,course_id,adviser_name,logo',
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

        DB::beginTransaction();
        try {
            // Lock the ticket to prevent concurrent modifications
            $this->ticket = Ticket::lockForUpdate()->find($this->ticket->ticket_id);

            $oldStatus = $this->ticket->status;

            // Update ticket status to approved
            $this->ticket->update(['status' => 'approved']);

            // Update or create current OSA approval state (one record per ticket)
            OSA_Approval::updateOrCreate(
                ['ticket_id' => $this->ticket->ticket_id],
                [
                    'user_id' => auth()->id(),
                    'decision' => 'approved',
                    'remarks' => $this->approvalRemarks,
                ]
            );

            // Log to approval history (immutable audit trail)
            $this->ticket->logApprovalHistory('osa', 'approved', $this->approvalRemarks);

            // Log transaction
            TransactionLogService::logTicketOperation('approved', $this->ticket, ['Remarks' => $this->approvalRemarks]);

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

            DB::commit();

            // Notify ticket owner about status change (after commit)
            $this->ticket->user->notify(new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'approved',
                $this->approvalRemarks
            ));

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
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Ticket approval failed', [
                'ticket_id' => $this->ticket->ticket_id,
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to approve ticket: ' . $e->getMessage());
        }
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

        DB::beginTransaction();
        try {
            // Lock the ticket to prevent concurrent modifications
            $this->ticket = Ticket::lockForUpdate()->find($this->ticket->ticket_id);

            $oldStatus = $this->ticket->status;

            // update ticket status to gso_review
            $this->ticket->update(['status' => 'gso_review']);

            // Update or create current OSA approval state
            OSA_Approval::updateOrCreate(
                ['ticket_id' => $this->ticket->ticket_id],
                [
                    'user_id' => auth()->id(),
                    'decision' => 'forwarded',
                    'remarks' => $this->forwardRemarks,
                ]
            );

            // Update or create current Office approval state for GSO
            Office_Approval::updateOrCreate(
                [
                    'ticket_id' => $this->ticket->ticket_id,
                    'office_id' => 2, // GSO office ID
                ],
                [
                    'user_id' => auth()->id(),
                    'decision' => 'pending',
                    'remarks' => $this->forwardRemarks,
                ]
            );

            // Log to approval history (immutable audit trail)
            $this->ticket->logApprovalHistory('osa', 'forwarded', $this->forwardRemarks);
            $this->ticket->logApprovalHistory('office', 'pending', $this->forwardRemarks, 1);

            // Log transaction
            TransactionLogService::logTicketOperation('forwarded', $this->ticket, ['Remarks' => $this->forwardRemarks]);

            DB::commit();

            // Send notifications after commit
            $this->ticket->user->notify(new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'gso_review',
                $this->forwardRemarks
            ));

            // Notify all GSO users in the GSO office about the forwarded ticket
            $gsoUsers = Cache::remember('gso_users_notifications', 3600, function () {
                return User::select(['user_id', 'name', 'email', 'role_id', 'office_id'])
                    ->where('role_id', User::getRoleId('gso'))
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
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Forward to GSO failed', [
                'ticket_id' => $this->ticket->ticket_id,
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to forward ticket: ' . $e->getMessage());
        }
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

        DB::beginTransaction();
        try {
            // Lock the ticket to prevent concurrent modifications
            $this->ticket = Ticket::lockForUpdate()->find($this->ticket->ticket_id);

            $oldStatus = $this->ticket->status;

            // Update ticket status to approved
            $this->ticket->update(['status' => 'approved']);

            // Update or create current OSA approval state
            OSA_Approval::updateOrCreate(
                ['ticket_id' => $this->ticket->ticket_id],
                [
                    'user_id' => auth()->id(),
                    'decision' => 'approved',
                    'remarks' => $this->finalApprovalRemarks,
                ]
            );

            // Log to approval history (immutable audit trail)
            $this->ticket->logApprovalHistory('osa', 'approved', $this->finalApprovalRemarks);

            // Log transaction
            TransactionLogService::logTicketOperation('final_approved', $this->ticket, ['Remarks' => $this->finalApprovalRemarks]);

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

            DB::commit();

            // Notify ticket owner about status change (after commit)
            $this->ticket->user->notify(new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'approved',
                $this->finalApprovalRemarks
            ));

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
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Final approval failed', [
                'ticket_id' => $this->ticket->ticket_id,
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to approve ticket: ' . $e->getMessage());
        }
    }

    public function forRevision()
    {
        // Validate remarks
        $this->validate([
            'revisionRemarks' => 'required|string|min:10',
        ], [
            'revisionRemarks.required' => 'Please provide detailed remarks explaining what needs to be revised.',
            'revisionRemarks.min' => 'Remarks must be at least 10 characters to provide clear guidance.',
        ]);

        DB::beginTransaction();
        try {
            // Lock the ticket to prevent concurrent modifications
            $this->ticket = Ticket::lockForUpdate()->find($this->ticket->ticket_id);

            $oldStatus = $this->ticket->status;

            $this->ticket->update(['status' => 'for_revision']);

            // Update or create current OSA approval state
            OSA_Approval::updateOrCreate(
                ['ticket_id' => $this->ticket->ticket_id],
                [
                    'user_id' => auth()->id(),
                    'decision' => 'for_revision',
                    'remarks' => $this->revisionRemarks,
                ]
            );

            // Log to approval history
            $this->ticket->logApprovalHistory('osa', 'for_revision', $this->revisionRemarks);

            // Log transaction
            TransactionLogService::logTicketOperation('for_revision', $this->ticket, ['Remarks' => $this->revisionRemarks]);

            DB::commit();

            // Notify ticket owner (after commit)
            $this->ticket->user->notify(new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'for_revision',
                $this->revisionRemarks
            ));

            // Reload the ticket
            $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

            // Dispatch events
            $this->dispatch('refresh-notifications');
            $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'for_revision');
            $this->dispatch('notification-received', [
                'title' => 'Ticket Needs Revision',
                'message' => "Your ticket {$this->ticket->ticket_number} has been sent back for revision.",
                'type' => 'warning',
            ]);

            $this->warning('Ticket has been sent back for revision.');
            $this->dispatch('ticket-for-revision');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('For revision failed', [
                'ticket_id' => $this->ticket->ticket_id,
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to send ticket for revision: ' . $e->getMessage());
        }
    }

    /**
     * Generate a temporary URL and open in a new tab for preview.
     */
    public function previewAttachment(int $attachmentId): void
    {
        $attachment = $this->ticket->attachments->firstWhere('attachment_id', $attachmentId);

        if (! $attachment) {
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

        if (! $attachment) {
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
