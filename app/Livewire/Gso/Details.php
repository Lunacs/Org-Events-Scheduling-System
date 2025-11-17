<?php

namespace App\Livewire\Gso;

use App\Livewire\Gso\Concerns\ResolvesOfficeContext;
use App\Models\Office_Approval;
use App\Models\OSA_Approval;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Details extends Component
{
    use ResolvesOfficeContext, Toast;

    #[Title('Ticket Details - GSO')]
    #[Layout('components.layouts.gso-layout')]
    public Ticket $ticket;

    // Remarks for each action
    public $approvalRemarks = '';

    public $rejectionRemarks = '';

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
            'oc_vehicle_type',
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

    public function render()
    {
        return view('livewire.gso.details');
    }

    /**
     * Approve ticket from GSO perspective
     */
    public function approveTicket()
    {
        // Authorize using policy
        Gate::authorize('approve', $this->ticket);

        // Validate remarks
        $this->validate([
            'approvalRemarks' => 'required|string|min:3',
        ], [
            'approvalRemarks.required' => 'Please provide remarks for approval.',
            'approvalRemarks.min' => 'Remarks must be at least 3 characters.',
        ]);

        $oldStatus = $this->ticket->status;

        // Update ticket status to pending_osa_approval (waiting for OSA final decision)
        $this->ticket->update(['status' => 'pending_osa_approval']);

        $officeId = $this->resolveOfficeId(Auth::user());

        // Update or create current Office approval state
        Office_Approval::updateOrCreate(
            [
                'ticket_id' => $this->ticket->ticket_id,
                'office_id' => $officeId,
            ],
            [
                'user_id' => auth()->id(),
                'decision' => 'approved',
                'remarks' => $this->approvalRemarks,
            ]
        );

        // Update or create current OSA approval state (pending final decision)
        $formattedRemarks = sprintf('APPROVED by GSO - %s', $this->approvalRemarks);
        OSA_Approval::updateOrCreate(
            ['ticket_id' => $this->ticket->ticket_id],
            [
                'user_id' => auth()->id(),
                'decision' => 'pending',
                'remarks' => $formattedRemarks,
            ]
        );

        // Log to approval history (immutable audit trail)
        $this->ticket->logApprovalHistory('office', 'approved', $this->approvalRemarks, $officeId);
        $this->ticket->logApprovalHistory('osa', 'pending', $formattedRemarks);

        // Log transaction
        TransactionLogService::logOfficeApproval('GSO', 'approved', $this->ticket, ['Remarks' => $this->approvalRemarks]);

        // Notify ticket owner about status change (DB + broadcast only)
        Notification::sendNow(
            $this->ticket->user,
            new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'pending_osa_approval',
                $this->approvalRemarks
            ),
            ['database', 'broadcast']
        );

        // Notify OSA users that GSO has approved
        $osaUsers = User::where('role_id', User::getRoleId('osa'))->get();
        Notification::sendNow(
            $osaUsers,
            new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'pending_osa_approval',
                "GSO has approved this ticket. Remarks: {$this->approvalRemarks}"
            ),
            ['database', 'broadcast']
        );

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

        // Clear remarks
        $this->approvalRemarks = '';

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'pending_osa_approval');
        $this->dispatch('ticket-approved');

        $this->success('Ticket has been approved. Waiting for OSA final decision.');
    }

    /**
     * Reject ticket from GSO perspective
     */
    public function rejectTicket()
    {
        // Authorize using policy
        Gate::authorize('reject', $this->ticket);

        // Validate remarks
        $this->validate([
            'rejectionRemarks' => 'required|string|min:10',
        ], [
            'rejectionRemarks.required' => 'Please provide detailed remarks explaining the reason for rejection.',
            'rejectionRemarks.min' => 'Remarks must be at least 10 characters to provide clear reasoning.',
        ]);

        $oldStatus = $this->ticket->status;

        // Update ticket status to rejected
        $this->ticket->update(['status' => 'rejected']);

        $officeId = $this->resolveOfficeId(Auth::user());

        // Update or create current Office approval state
        Office_Approval::updateOrCreate(
            [
                'ticket_id' => $this->ticket->ticket_id,
                'office_id' => $officeId,
            ],
            [
                'user_id' => auth()->id(),
                'decision' => 'rejected',
                'remarks' => $this->rejectionRemarks,
            ]
        );

        // Update or create current OSA approval state
        $formattedRemarks = sprintf('REJECTED by GSO - %s', $this->rejectionRemarks);
        OSA_Approval::updateOrCreate(
            ['ticket_id' => $this->ticket->ticket_id],
            [
                'user_id' => auth()->id(),
                'decision' => 'rejected',
                'remarks' => $formattedRemarks,
            ]
        );

        // Log to approval history (immutable audit trail)
        $this->ticket->logApprovalHistory('office', 'rejected', $this->rejectionRemarks, $officeId);
        $this->ticket->logApprovalHistory('osa', 'rejected', $formattedRemarks);

        // Log transaction
        TransactionLogService::logOfficeApproval('GSO', 'rejected', $this->ticket, ['Remarks' => $this->rejectionRemarks]);

        // Notify ticket owner about status change
        $this->ticket->user->notify(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            'pending_osa_approval',
            $this->approvalRemarks
        ));

        // Notify OSA users that GSO has rejected
        $osaUsers = User::where('role_id', User::getRoleId('osa'))->get();
        Notification::sendNow(
            $osaUsers,
            new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'rejected',
                "GSO has rejected this ticket. Remarks: {$this->rejectionRemarks}"
            ),
            ['database', 'broadcast']
        );

        // Reload the ticket with fresh approval data
        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role');

        // Clear remarks
        $this->rejectionRemarks = '';

        // Dispatch events for instant notifications
        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: 'rejected');
        $this->dispatch('ticket-rejected');

        $this->error('Ticket has been rejected.');
    }

    /**
     * Preview attachment
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
     * Download attachment
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
     * Build a temporary URL from the configured filesystem
     */
    private function makeTemporaryUrl(string $path, string $filename, bool $forceDownload = false): string
    {
        $disk = Storage::disk(config('filesystems.default'));

        try {
            if (method_exists($disk, 'temporaryUrl')) {
                $options = [
                    'ResponseContentDisposition' => ($forceDownload ? 'attachment' : 'inline').'; filename="'.addslashes($filename).'"',
                ];

                return $disk->temporaryUrl($path, now()->addMinutes(5), $options);
            }
        } catch (\Throwable $e) {
            // Fallback below if temporary URLs are unavailable
        }

        return Storage::url($path);
    }
}
