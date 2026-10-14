<?php

namespace App\Livewire\Osa\TicketReview;

use App\Models\Ticket;
use App\Services\TicketWorkflowService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

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
            'venue_other',
            'alternate_venue',
            'alternate_venue_other',
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
            'user' => fn ($q) => $q->withTrashed()
                ->select(['user_id', 'name', 'email', 'role_id', 'org_id', 'position_id', 'avatar_style', 'avatar_seed']),
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
            'comments.user' => fn ($q) => $q->withTrashed()
                ->select(['user_id', 'name', 'role_id', 'avatar_style', 'avatar_seed']),
            'comments.user.role:role_id,role_name',
            'osaApprovals:osa_approval_id,ticket_id,user_id,decision,remarks,created_at',
            'osaApprovals.user' => fn ($q) => $q->withTrashed()
                ->select(['user_id', 'name', 'role_id', 'avatar_style', 'avatar_seed']),
            'osaApprovals.user.role:role_id,role_name',
            'officeApprovals:id,ticket_id,office_id,user_id,decision,remarks,created_at',
            'officeApprovals.office:office_id,office_name',
            'officeApprovals.user' => fn ($q) => $q->withTrashed()
                ->select(['user_id', 'name', 'role_id', 'avatar_style', 'avatar_seed']),
            'officeApprovals.user.role:role_id,role_name',
        ])->where('ticket_number', $ticketNumber)->firstOrFail();
    }

    // Open/close modal methods removed; handled by Alpine.js

    public function approveTicket()
    {
        Gate::authorize('approve', $this->ticket);

        $this->validate([
            'approvalRemarks' => 'required|string|min:3|max:255',
        ], [
            'approvalRemarks.required' => 'Please provide remarks for approval.',
            'approvalRemarks.min' => 'Remarks must be at least 3 characters.',
            'approvalRemarks.max' => 'Remarks may not be longer than 255 characters.',
        ]);

        $this->runTransition(
            fn (TicketWorkflowService $workflow) => $workflow->approve($this->ticket, auth()->user(), $this->approvalRemarks),
            newStatus: 'approved',
            notice: ['title' => 'Ticket Approved', 'message' => "Your ticket {$this->ticket->ticket_number} has been approved!", 'type' => 'success'],
            success: 'Ticket has been approved and event has been created successfully.',
            browserEvent: 'ticket-approved',
        );
    }

    public function forwardToGso()
    {
        Gate::authorize('forwardToGso', $this->ticket);

        $this->validate([
            'forwardRemarks' => 'required|string|min:3|max:255',
        ], [
            'forwardRemarks.required' => 'Please provide remarks for forwarding to GSO.',
            'forwardRemarks.min' => 'Remarks must be at least 3 characters.',
            'forwardRemarks.max' => 'Remarks may not be longer than 255 characters.',
        ]);

        $this->runTransition(
            fn (TicketWorkflowService $workflow) => $workflow->forwardToGso($this->ticket, auth()->user(), $this->forwardRemarks),
            newStatus: 'gso_review',
            notice: ['title' => 'Ticket Forwarded to GSO', 'message' => "Your ticket {$this->ticket->ticket_number} has been forwarded to GSO for review.", 'type' => 'info'],
            success: 'Ticket has been forwarded to GSO for approval.',
            browserEvent: 'ticket-forwarded',
        );
    }

    public function finalApproval()
    {
        Gate::authorize('finalApprove', $this->ticket);

        $this->validate([
            'finalApprovalRemarks' => 'required|string|min:3|max:255',
        ], [
            'finalApprovalRemarks.required' => 'Please provide remarks for final approval.',
            'finalApprovalRemarks.min' => 'Remarks must be at least 3 characters.',
            'finalApprovalRemarks.max' => 'Remarks may not be longer than 255 characters.',
        ]);

        $this->runTransition(
            fn (TicketWorkflowService $workflow) => $workflow->approve($this->ticket, auth()->user(), $this->finalApprovalRemarks),
            newStatus: 'approved',
            notice: ['title' => 'Ticket Finally Approved', 'message' => "Your ticket {$this->ticket->ticket_number} has been finally approved after GSO review!", 'type' => 'success'],
            success: 'Ticket has been approved and event has been created successfully.',
            browserEvent: 'ticket-final-approved',
        );
    }

    public function forRevision()
    {
        Gate::authorize('requestRevision', $this->ticket);

        $this->validate([
            'revisionRemarks' => 'required|string|min:10|max:255',
        ], [
            'revisionRemarks.required' => 'Please provide detailed remarks explaining what needs to be revised.',
            'revisionRemarks.min' => 'Remarks must be at least 10 characters to provide clear guidance.',
            'revisionRemarks.max' => 'Remarks may not be longer than 255 characters.',
        ]);

        $this->runTransition(
            fn (TicketWorkflowService $workflow) => $workflow->requestRevision($this->ticket, auth()->user(), $this->revisionRemarks),
            newStatus: 'for_revision',
            notice: ['title' => 'Ticket Needs Revision', 'message' => "Your ticket {$this->ticket->ticket_number} has been sent back for revision.", 'type' => 'warning'],
            success: 'Ticket has been sent back for revision.',
            browserEvent: 'ticket-for-revision',
        );
    }

    /**
     * Run a workflow transition, then refresh the page state and tell the browser.
     *
     * @param  callable(TicketWorkflowService): Ticket  $transition
     * @param  array{title: string, message: string, type: string}  $notice
     */
    private function runTransition(callable $transition, string $newStatus, array $notice, string $success, string $browserEvent): void
    {
        $lock = Cache::lock("lock:ticket:decision:{$this->ticket->ticket_id}", 10);

        if (! $lock->get()) {
            $this->warning('Action is already in progress.');

            return;
        }

        try {
            $this->ticket = $transition(app(TicketWorkflowService::class));
        } catch (\DomainException $e) {
            $this->error($e->getMessage());

            return;
        } catch (\Exception $e) {
            \Log::error('Ticket decision failed', [
                'ticket_id' => $this->ticket->ticket_id,
                'status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
            $this->error('The action could not be completed. Please try again.');

            return;
        } finally {
            $lock->release();
        }

        $this->ticket->load('osaApprovals.user.role', 'officeApprovals.office', 'officeApprovals.user.role', 'events.eventSchedules');

        $this->dispatch('refresh-notifications');
        $this->dispatch('ticket-status-updated', ticketId: $this->ticket->ticket_id, newStatus: $newStatus);
        $this->dispatch('notification-received', $notice);

        $newStatus === 'for_revision' ? $this->warning($success) : $this->success($success);
        $this->dispatch($browserEvent);
    }

    /**
     * Generate a temporary URL and open in a new tab for preview.
     */
    public function previewAttachment(int $attachmentId): void
    {
        Gate::authorize('tickets.view');

        $attachment = $this->ticket->attachments->firstWhere('attachment_id', $attachmentId);

        if (! $attachment) {
            $this->warning('Attachment not found.');

            return;
        }

        $url = $this->makeTemporaryUrl($attachment->attachment_id, false);

        $this->dispatch('open-attachment-preview', url: $url);
    }

    /**
     * Generate a temporary URL that forces download and dispatch to client.
     */
    public function downloadAttachment(int $attachmentId): void
    {
        Gate::authorize('tickets.view');

        $attachment = $this->ticket->attachments->firstWhere('attachment_id', $attachmentId);

        if (! $attachment) {
            $this->warning('Attachment not found.');

            return;
        }

        $url = $this->makeTemporaryUrl($attachment->attachment_id, true);

        $this->dispatch('download-attachment', url: $url);
    }

    /**
     * Build a temporary URL from the configured filesystem.
     * Uses S3 temporaryUrl for cloud storage, or signed routes for local storage.
     */
    private function makeTemporaryUrl(int $attachmentId, bool $forceDownload = false): string
    {
        $routeName = $forceDownload ? 'attachments.download' : 'attachments.preview';

        return URL::temporarySignedRoute(
            $routeName,
            now()->addMinutes(5),
            ['attachment' => $attachmentId]
        );
    }

    public function render()
    {
        return view('livewire.osa.ticket-review.show');
    }
}
