<?php

namespace App\Livewire\Gso;

use App\Models\Office_Approval;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Details extends Component
{
    use Toast;

    #[Title('Ticket Details - GSO')]
    #[Layout('components.layouts.gso-layout')]

    public Ticket $ticket;
    public $approvalRemarks = '';
    public $revisionRemarks = '';

    public function mount($ticketNumber)
    {
        $this->ticket = Ticket::with([
            'user' => fn($q) => $q->withTrashed()->with('studentOrganization'),
            'events.eventSchedules',
            'attachments',
            'osaApprovals.user' => fn($q) => $q->withTrashed(),
            'officeApprovals.office',
            'officeApprovals.user' => fn($q) => $q->withTrashed(),
            'approvalHistory.user' => fn($q) => $q->withTrashed(),
            'approvalHistory.office',
        ])->where('ticket_number', $ticketNumber)->firstOrFail();
    }

    public function render()
    {
        $statusOverview = $this->buildStatusOverview();
        $allowedActions = $this->getAllowedActions();

        return view('livewire.gso.details', [
            'statusOverview' => $statusOverview,
            'allowedActions' => $allowedActions,
        ]);
    }

    protected function buildStatusOverview(): array
    {
        $currentUser = Auth::user();
        $officeId = $currentUser?->office_id;

        if (!$officeId) {
            return [
                'status' => 'pending',
                'status_label' => 'Pending',
                'status_badge' => 'badge-warning',
                'status_detail' => null,
            ];
        }

        $officeApproval = $this->ticket->officeApprovals()
            ->where('office_id', $officeId)
            ->first();

        if (!$officeApproval) {
            return [
                'status' => 'pending',
                'status_label' => 'Pending Review',
                'status_badge' => 'badge-warning',
                'office_id' => $officeId,
                'office_name' => $currentUser->office?->office_name,
                'status_detail' => null,
            ];
        }

        $decision = strtolower($officeApproval->decision ?? 'pending');

        return [
            'status' => $decision,
            'status_label' => ucfirst(str_replace('_', ' ', $decision)),
            'status_badge' => match ($decision) {
                'approved' => 'badge-success',
                default => 'badge-warning',
            },
            'office_id' => $officeId,
            'office_name' => $officeApproval->office?->office_name,
            'status_detail' => $officeApproval->remarks,
        ];
    }

    protected function getAllowedActions(): array
    {
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->isGSO()) {
            return [];
        }

        $officeId = $currentUser->office_id;
        if (!$officeId) {
            return [];
        }

        $officeApproval = $this->ticket->officeApprovals()
            ->where('office_id', $officeId)
            ->first();

        $officeDecision = $officeApproval ? strtolower($officeApproval->decision ?? 'pending') : 'pending';

        if ($officeDecision !== 'pending') {
            return [];
        }

        if ($this->ticket->status === 'gso_review') {
            return ['approve', 'for_revision'];
        }

        return [];
    }

    public function approveTicket()
    {
        Gate::authorize('approve', $this->ticket);

        $this->validate([
            'approvalRemarks' => 'required|string|min:3',
        ], [
            'approvalRemarks.required' => 'Please provide remarks for approval.',
            'approvalRemarks.min' => 'Remarks must be at least 3 characters.',
        ]);

        DB::beginTransaction();
        try {
            // Lock the ticket to prevent concurrent modifications
            $this->ticket = Ticket::with([
                'user' => fn($q) => $q->withTrashed()->with('studentOrganization'),
            ])->lockForUpdate()->find($this->ticket->ticket_id);

            $oldStatus = $this->ticket->status;
            $officeId = Auth::user()->office_id;

            $this->ticket->update(['status' => 'pending_osa_approval']);

            Office_Approval::updateOrCreate(
                [
                    'ticket_id' => $this->ticket->ticket_id,
                    'office_id' => $officeId,
                ],
                [
                    'user_id' => Auth::id(),
                    'decision' => 'approved',
                    'remarks' => $this->approvalRemarks,
                ]
            );

            $this->ticket->logApprovalHistory(
                'office',
                'approved',
                $this->approvalRemarks,
                $officeId
            );

            TransactionLogService::logOfficeApproval(
                'GSO',
                'approved',
                $this->ticket,
                ['Remarks' => $this->approvalRemarks]
            );

            DB::commit();

            // Send notifications after commit
            $this->ticket->user->notify(new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'pending_osa_approval',
                "GSO has approved your ticket and it is now awaiting final OSA approval."
            ));

            $osaRoleId = User::getRoleId('osa');
            $osaUsers = User::where('role_id', $osaRoleId)->get();

            Notification::sendNow(
                $osaUsers,
                new TicketStatusUpdatedNotification(
                    $this->ticket,
                    $oldStatus,
                    'pending_osa_approval',
                    "GSO has approved this ticket. Remarks: {$this->approvalRemarks}"
                )
            );

            $this->ticket->load([
                'user' => fn($q) => $q->withTrashed()->with('studentOrganization'),
                'events.eventSchedules',
                'attachments',
                'osaApprovals.user' => fn($q) => $q->withTrashed(),
                'officeApprovals.office',
                'officeApprovals.user' => fn($q) => $q->withTrashed(),
                'approvalHistory.user' => fn($q) => $q->withTrashed(),
                'approvalHistory.office',
            ]);

            $this->approvalRemarks = '';

            $this->dispatch('refresh-notifications');
            $this->dispatch(
                'ticket-status-updated',
                ticketId: $this->ticket->ticket_id,
                newStatus: 'pending_osa_approval'
            );
            $this->dispatch('ticket-approved');

            $this->success('Ticket approved. Awaiting OSA final decision.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('GSO ticket approval failed', [
                'ticket_id' => $this->ticket->ticket_id,
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to approve ticket: ' . $e->getMessage());
        }
    }

    public function forRevision()
    {
        Gate::authorize('requestRevision', $this->ticket);

        $this->validate([
            'revisionRemarks' => 'required|string|min:10',
        ], [
            'revisionRemarks.required' => 'Please provide detailed remarks explaining what needs to be revised.',
            'revisionRemarks.min' => 'Remarks must be at least 10 characters to provide clear guidance.',
        ]);

        DB::beginTransaction();
        try {
            // Lock the ticket to prevent concurrent modifications
            $this->ticket = Ticket::with([
                'user' => fn($q) => $q->withTrashed()->with('studentOrganization'),
            ])->lockForUpdate()->find($this->ticket->ticket_id);

            $oldStatus = $this->ticket->status;
            $officeId = Auth::user()->office_id;

            $this->ticket->update(['status' => 'pending_osa_approval']);

            Office_Approval::updateOrCreate(
                [
                    'ticket_id' => $this->ticket->ticket_id,
                    'office_id' => $officeId,
                ],
                [
                    'user_id' => Auth::id(),
                    'decision' => 'for_revision',
                    'remarks' => $this->revisionRemarks,
                ]
            );

            $this->ticket->logApprovalHistory(
                'office',
                'for_revision',
                $this->revisionRemarks,
                $officeId
            );

            TransactionLogService::logOfficeApproval(
                'GSO',
                'for_revision',
                $this->ticket,
                ['Remarks' => $this->revisionRemarks]
            );

            DB::commit();

            // Send notifications after commit
            $this->ticket->user->notify(new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'pending_osa_approval',
                "GSO has reviewed your ticket and forwarded it to OSA for a final decision."
            ));

            $osaRoleId = User::getRoleId('osa');
            $osaUsers = User::where('role_id', $osaRoleId)->get();

            Notification::sendNow(
                $osaUsers,
                new TicketStatusUpdatedNotification(
                    $this->ticket,
                    $oldStatus,
                    'pending_osa_approval',
                    "GSO has requested revisions. Remarks: {$this->revisionRemarks}"
                )
            );

            $this->ticket->load([
                'user' => fn($q) => $q->withTrashed()->with('studentOrganization'),
                'events.eventSchedules',
                'attachments',
                'osaApprovals.user' => fn($q) => $q->withTrashed(),
                'officeApprovals.office',
                'officeApprovals.user' => fn($q) => $q->withTrashed(),
                'approvalHistory.user' => fn($q) => $q->withTrashed(),
                'approvalHistory.office',
            ]);

            $this->revisionRemarks = '';

            $this->dispatch('refresh-notifications');
            $this->dispatch(
                'ticket-status-updated',
                ticketId: $this->ticket->ticket_id,
                newStatus: 'pending_osa_approval'
            );
            $this->dispatch('ticket-for-revision');

            $this->warning('Revision requested. OSA will review and make the final decision.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('GSO revision request failed', [
                'ticket_id' => $this->ticket->ticket_id,
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to request revision: ' . $e->getMessage());
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

        $url = $this->makeTemporaryUrl($attachment->attachment_id, false);

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

        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            $routeName,
            now()->addMinutes(5),
            ['attachment' => $attachmentId]
        );
    }
}
