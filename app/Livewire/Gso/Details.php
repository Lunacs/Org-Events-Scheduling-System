<?php

namespace App\Livewire\Gso;

use App\Livewire\Gso\Concerns\ResolvesOfficeContext;
use App\Models\Office_Approval;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\TicketCommentNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public ?int $ticketId = null;

    public ?int $officeId = null;
    public ?int $approvalId = null;

    public $comment = '';

    public $approvalRemarks = '';

    public $rejectionRemarks = '';

    /**
     * The office approval record matched to the active office context.
     */
    protected ?Office_Approval $officeApproval = null;
    protected ?Office_Approval $baseOfficeApproval = null;

    /**
     * View model segments consumed by the Blade template.
     *
     * @var array<string, mixed>
     */
    public array $overview = [];

    public array $organization = [];

    public array $eventDetails = [];

    public array $participants = [];

    public array $requirements = [];

    public array $schedules = [];

    public array $attachments = [];

    public array $osaApprovals = [];

    public array $officeApprovals = [];
    public array $financial = [];
    public array $igp = [];
    public array $offCampus = [];
    public ?string $additionalNotes = null;
    public bool $showActionModal = false;
    public string $actionType = '';
    public string $confirmationInput = '';
    public ?int $actionApprovalId = null;

    public function mount(?Ticket $ticket = null, ?int $office = null, ?int $approval = null, ?int $ticketId = null): void
    {
        $this->officeId = $office;
        $this->approvalId = $approval;

        $this->officeId = $this->resolveActiveOfficeId();
        $this->approvalId = $this->resolveActiveApprovalId();

        $this->hydrateFromTicket($this->resolveTicket($ticket, $ticketId));
    }

    public function loadTicket(?int $ticketId): void
    {
        $this->hydrateFromTicket($this->resolveTicket(null, $ticketId));
    }

    public function render()
    {
        return view('livewire.gso.details');
    }

    protected function resolveTicket(?Ticket $ticket, ?int $ticketId): Ticket
    {
        $resolved = $ticket;

        if ($resolved === null && $ticketId !== null) {
            $resolved = Ticket::query()->findOrFail($ticketId);
        }

        if ($resolved === null) {
            abort(404);
        }

        $resolved->load([
            'eventType',
            'user.studentOrganization.course',
            'user.position',
            'attachments',
            'events.eventSchedules',
            'osaApprovals.user',
            'officeApprovals.office',
            'officeApprovals.user',
            'comments.user.role',
            'fundSource',
        ]);

        return $resolved;
    }

    protected function hydrateFromTicket(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->ticketId = $ticket->getAttribute('ticket_id');
        $this->officeApproval = $this->resolveOfficeApproval($ticket);
        $this->baseOfficeApproval = $this->officeApproval;
        $this->actionApprovalId = $this->officeApproval?->getKey();

        $this->officeApproval = $this->baseOfficeApproval;

        $this->requirements = $this->buildRequirements();
        $this->overview = $this->buildOverview();
        $this->organization = $this->buildOrganization();
        $this->eventDetails = $this->buildEventDetails($this->requirements);
        $this->participants = $this->buildParticipants();
        $this->schedules = $this->buildSchedules();
        $this->attachments = $this->buildAttachments();
        $this->osaApprovals = $this->buildOsaApprovals();
        $this->officeApprovals = $this->buildOfficeApprovals();
        $this->financial = $this->buildFinancial();
        $this->igp = $this->buildIgpDetails();
        $this->offCampus = $this->buildOffCampusDetails();
        $this->additionalNotes = $this->resolveAdditionalNotes();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildOverview(): array
    {
        $statusSource = $this->officeApproval?->decision ?? $this->ticket->status;
        $status = $this->normalizeStatus($statusSource);
        $eventDate = $this->parseDate($this->ticketAttribute('date_from'));
        $priorityKey = $this->determinePriorityKey($eventDate);
        $priorityLabel = $this->resolvePriorityLabel($priorityKey);
        $officeName = $this->officeApproval?->office?->office_name;

        if (! $officeName && $this->officeId !== null) {
            $officeName = optional(
                $this->ticket->officeApprovals
                    ->firstWhere('office_id', $this->officeId)
            )->office?->office_name;
        }

        return [
            'ticket_number' => $this->ticket->ticket_number ?? 'N/A',
            'status' => $status,
            'status_label' => $this->resolveStatusLabel($status),
            'status_badge' => $this->resolveStatusBadge($status),
            'status_detail' => $this->officeApproval?->remarks,
            'office_name' => $officeName,
            'priority_label' => $priorityLabel,
            'priority_badge' => $this->resolvePriorityBadge($priorityKey),
            'event_type' => $this->ticket->eventType?->type_name ?? '—',
            'submitted_at' => $this->formatDateTime($this->ticket->created_at),
            'last_updated' => $this->formatDateTime($this->ticket->updated_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildOrganization(): array
    {
        $user = $this->ticket->user;
        $organization = $user?->studentOrganization;

        return [
            'organization_name' => $organization?->org_name ?? '—',
            'organization_code' => $organization?->org_code ?? '—',
            'course' => $organization?->course?->course_name ?? '—',
            'adviser' => $organization?->adviser_name ?? '—',
            'proponent' => $user?->name ?? '—',
            'position' => $user?->position?->position_name ?? '—',
            'email' => $user?->email ?? '—',
        ];
    }

    /**
     * @param  array<int, string>  $requirements
     * @return array<string, mixed>
     */
    protected function buildEventDetails(array $requirements): array
    {
        $dateFrom = $this->ticketAttribute('date_from');
        $dateTo = $this->ticketAttribute('date_to');
        $timeFrom = $this->ticketAttribute('time_from');
        $timeTo = $this->ticketAttribute('time_to');

        return [
            'title' => $this->ticket->title ?? '—',
            'description' => $this->ticket->description ?? 'No description provided.',
            'event_type' => $this->ticket->eventType?->type_name ?? '—',
            'date_range' => $this->formatDateRange($dateFrom, $dateTo),
            'time_range' => $this->formatTimeRange($timeFrom, $timeTo),
            'venue_requested' => $this->ticket->venue_requested ?? '—',
            'alternate_venue' => $this->ticket->alternate_venue ?? '—',
            'sponsoring_body' => $this->ticket->sponsoring_body ?? '—',
            'special_requirements' => $requirements,
            'off_campus_label' => $this->ticketIsOffCampus() ? 'Yes' : 'No',
            'organizer_notes' => $this->ticket->additional_notes ?? null,
            'notes' => $this->ticket->events
                ->pluck('notes')
                ->filter()
                ->values()
                ->all(),
        ];
    }

    protected function buildFinancial(): array
    {
        $budget = $this->ticket->estimated_budget;

        return [
            'fund_source' => $this->ticket->fundSource?->source_name ?? '—',
            'estimated_budget' => $this->formatCurrency($budget),
            'budget_breakdown' => $this->ticket->budget_breakdown ?: null,
            'has_breakdown' => filled($this->ticket->budget_breakdown),
        ];
    }

    protected function buildIgpDetails(): array
    {
        $requested = (bool) $this->ticket->igp_requested;

        return [
            'requested' => $requested,
            'request_label' => $requested ? 'Requested' : 'Not Requested',
            'details' => $requested ? ($this->ticket->igp_details ?: null) : null,
            'has_details' => $requested && filled($this->ticket->igp_details),
        ];
    }

    protected function buildOffCampusDetails(): array
    {
        $raw = [
            'accommodation' => $this->ticket->oc_accommodation,
            'transport_provider' => $this->ticket->oc_tsp,
            'driver_name' => $this->ticket->oc_driver_name,
            'driver_contact' => $this->ticket->oc_driver_contact_number,
            'vehicle_type' => $this->ticket->oc_vehicle_type,
            'vehicle_plate' => $this->ticket->oc_vehicle_plate_number,
        ];

        $hasDetails = collect($raw)->filter(fn($value) => filled($value))->isNotEmpty();

        return [
            'is_off_campus' => $hasDetails,
            'has_details' => $hasDetails,
            'accommodation' => $raw['accommodation'] ?? null,
            'transport_provider' => $raw['transport_provider'] ?? null,
            'transport_provider_label' => $this->resolveTransportLabel($raw['transport_provider'] ?? null),
            'driver_name' => $raw['driver_name'] ?? null,
            'driver_contact' => $raw['driver_contact'] ?? null,
            'vehicle_type' => $raw['vehicle_type'] ?? null,
            'vehicle_plate' => $raw['vehicle_plate'] ?? null,
        ];
    }

    protected function resolveAdditionalNotes(): ?string
    {
        return $this->ticket->additional_notes ?: null;
    }

    /**
     * @return array<string, int>
     */
    protected function buildParticipants(): array
    {
        return [
            'plv' => (int) ($this->ticket->plv_participants ?? 0),
            'external' => (int) ($this->ticket->external_participants ?? 0),
            'total' => (int) ($this->ticket->total_participants ?? 0),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function buildRequirements(): array
    {
        $raw = (string) ($this->ticket->special_requirements ?? '');

        return Str::of($raw)
            ->replace([';', '|'], ',')
            ->explode(',')
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildSchedules(): array
    {
        return $this->ticket->events
            ->values()
            ->flatMap(function ($event, int $eventIndex) {
                $label = 'Event '.($eventIndex + 1);

                return $event->eventSchedules
                    ->map(function ($schedule) use ($event, $label) {
                        $status = $this->normalizeStatus($schedule->status ?? 'pending');

                        return [
                            'id' => $schedule->schedule_id,
                            'event_label' => $label,
                            'event_notes' => $event->notes ?? null,
                            'datetime' => $schedule->start_date?->format('M d, Y g:i A') ?? '—',
                            'venue' => $schedule->schedule_venue ?? '—',
                            'status' => $status,
                            'status_label' => $this->resolveStatusLabel($status),
                            'status_badge' => $this->resolveStatusBadge($status),
                            'remarks' => $schedule->remarks,
                        ];
                    });
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildAttachments(): array
    {
        return $this->ticket->attachments
            ->map(function ($attachment) {
                $type = $attachment->file_type ? Str::headline($attachment->file_type) : 'File';

                return [
                    'id' => $attachment->attachment_id,
                    'name' => $attachment->file_name ?? $type,
                    'type' => $type,
                    'size' => $this->formatFileSize($attachment->file_size ?? null),
                    'url' => $this->resolveAttachmentUrl($attachment->file_path ?? null),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildOsaApprovals(): array
    {
        return $this->ticket->osaApprovals
            ->sortBy(function ($approval) {
                return $approval->updated_at ?? $approval->created_at;
            })
            ->map(function ($approval) {
                $decision = $this->normalizeStatus($approval->decision ?? 'pending');

                return [
                    'id' => $approval->osa_approval_id,
                    'approver' => $approval->user?->name ?? '—',
                    'decision' => $decision,
                    'decision_label' => $this->resolveStatusLabel($decision),
                    'badge' => $this->resolveStatusBadge($decision),
                    'remarks' => $approval->remarks,
                    'timestamp' => $this->formatDateTime($approval->updated_at),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildOfficeApprovals(): array
    {
        $approvals = collect($this->ticket->officeApprovals);

        if ($this->approvalId !== null) {
            $approvals = $approvals->where('id', $this->approvalId);
        } elseif ($this->officeId !== null) {
            $approvals = $approvals->where('office_id', $this->officeId);
        }

        return $approvals
            ->sortBy(function ($approval) {
                return $approval->updated_at ?? $approval->created_at;
            })
            ->map(function ($approval) {
                $decision = $this->normalizeStatus($approval->decision ?? 'pending');
                $displayTimestamp = $approval->updated_at ?? $approval->created_at;

                return [
                    'id' => $approval->id,
                    'office' => $approval->office?->office_name ?? '—',
                    'approver' => $approval->user?->name ?? '—',
                    'decision' => $decision,
                    'decision_label' => $this->resolveStatusLabel($decision),
                    'badge' => $this->resolveStatusBadge($decision),
                    'remarks' => $approval->remarks,
                    'timestamp' => $this->formatDateTime($displayTimestamp),
                ];
            })
            ->values()
            ->all();
    }

    protected function ticketAttribute(string $key): ?string
    {
        $value = $this->ticket->getAttribute($key);

        return $value !== null ? (string) $value : null;
    }

    protected function normalizeStatus(?string $status): string
    {
        return Str::of($status ?? 'pending')->lower()->toString();
    }

    protected function resolveStatusLabel(string $status): string
    {
        return Str::headline($status ?: 'pending');
    }

    protected function resolveStatusBadge(string $status): string
    {
        return match ($status) {
            'approved' => 'badge-success',
            'rejected' => 'badge-error',
            default => 'badge-warning',
        };
    }

    protected function formatCurrency($value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return '₱' . number_format((float) $value, 2);
    }

    protected function determinePriorityKey(?Carbon $eventDate): string
    {
        if (! $eventDate) {
            return 'low';
        }

        $daysUntil = Carbon::now()->startOfDay()->diffInDays($eventDate->copy()->startOfDay(), false);

        if ($daysUntil <= 3) {
            return 'high';
        }

        if ($daysUntil <= 7) {
            return 'medium';
        }

        return 'low';
    }

    protected function resolvePriorityLabel(string $priorityKey): string
    {
        return match ($priorityKey) {
            'high' => 'High',
            'medium' => 'Medium',
            default => 'Low',
        };
    }

    protected function resolvePriorityBadge(string $priority): string
    {
        return match ($priority) {
            'high' => 'badge-error',
            'medium' => 'badge-warning',
            default => 'badge-success',
        };
    }

    protected function formatDateTime(?Carbon $dateTime): string
    {
        return $dateTime ? $dateTime->copy()->timezone(config('app.timezone'))->format('M d, Y g:i A') : '—';
    }

    protected function formatDateRange(?string $from, ?string $to): string
    {
        $start = $this->parseDate($from);
        $end = $this->parseDate($to);

        if ($start && $end) {
            if ($start->isSameDay($end)) {
                return $start->format('M d, Y');
            }

            return sprintf('%s – %s', $start->format('M d, Y'), $end->format('M d, Y'));
        }

        if ($start) {
            return $start->format('M d, Y');
        }

        if ($end) {
            return $end->format('M d, Y');
        }

        return '—';
    }

    protected function formatTimeRange(?string $from, ?string $to): string
    {
        $start = $this->formatTime($from);
        $end = $this->formatTime($to);

        if ($start && $end) {
            return $start === $end ? $start : sprintf('%s – %s', $start, $end);
        }

        return $start ?? $end ?? '—';
    }

    protected function formatTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $formats = ['H:i', 'H:i:s', 'g:i A', 'g:i a'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('g:i A');
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('g:i A');
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function formatFileSize($bytes): ?string
    {
        if ($bytes === null || $bytes === '') {
            return null;
        }

        $bytes = (int) $bytes;

        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = max(0, min($power, count($units) - 1));
        $formatted = $bytes / (1024 ** $power);

        return sprintf('%s %s', $power === 0 ? $bytes : number_format($formatted, 2), $units[$power]);
    }

    protected function resolveAttachmentUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return $path;
        }

        try {
            return Storage::url($path);
        } catch (\Throwable) {
            return asset($path);
        }
    }

    protected function resolveActiveOfficeId(): int
    {
        if ($this->officeId !== null) {
            return (int) $this->officeId;
        }

        $routeOffice = request()->route('office') ?? request()->query('office');

        if (is_numeric($routeOffice)) {
            $candidate = (int) $routeOffice;

            if ($candidate > 0) {
                return $candidate;
            }
        }

        return $this->resolveOfficeId(Auth::user());
    }

    protected function ticketIsOffCampus(): bool
    {
        return collect([
            $this->ticket->oc_accommodation,
            $this->ticket->oc_tsp,
            $this->ticket->oc_driver_name,
            $this->ticket->oc_driver_contact_number,
            $this->ticket->oc_vehicle_type,
            $this->ticket->oc_vehicle_plate_number,
        ])->contains(fn($value) => filled($value));
    }

    protected function resolveTransportLabel(?string $provider): string
    {
        return match ($provider) {
            'in-house' => 'In-house',
            'outsourced' => 'Outsourced',
            null => '—',
            default => Str::headline($provider),
        };
    }

    protected function resolveActiveApprovalId(): ?int
    {
        if ($this->approvalId !== null) {
            return (int) $this->approvalId;
        }

        $routeApproval = request()->route('approval') ?? request()->query('approval');

        if (is_numeric($routeApproval)) {
            $candidate = (int) $routeApproval;

            if ($candidate > 0) {
                return $candidate;
            }
        }

        return null;
    }

    protected function resolveOfficeApproval(Ticket $ticket): ?Office_Approval
    {
        $approvals = $ticket->officeApprovals;

        if ($this->approvalId) {
            $match = $approvals->firstWhere('id', $this->approvalId);

            if ($match) {
                return $match;
            }
        }

        if ($this->officeId !== null) {
            return $approvals->firstWhere('office_id', $this->officeId);
        }

        return null;
    }

    public function openActionModal(string $action): void
    {
        if (! in_array($action, ['approve', 'reject'], true)) {
            return;
        }

        $approval = $this->ensureOfficeApproval();

        if (! $approval) {
            return;
        }

        $this->resetErrorBag();
        $this->confirmationInput = '';
        $this->actionType = $action;
        $this->showActionModal = true;
    }

    public function cancelActionModal(): void
    {
        $this->reset(['showActionModal', 'actionType', 'confirmationInput']);
        $this->resetErrorBag();
    }

    public function performAction(): void
    {
        if (! in_array($this->actionType, ['approve', 'reject'], true)) {
            return;
        }

        $approval = $this->ensureOfficeApproval();

        if (! $approval) {
            $this->addError('confirmationInput', 'Approval record not found.');
            return;
        }

        $requiredWord = $this->actionType;

        if (strtolower(trim($this->confirmationInput)) !== $requiredWord) {
            $this->addError('confirmationInput', 'Type "' . $requiredWord . '" to proceed.');
            return;
        }

        $decision = $this->actionType === 'approve' ? 'approved' : 'rejected';

        $approval->decision = $decision;
        $approval->updated_at = now();
        $approval->save();

        $this->officeApproval = $approval;

        $this->dispatch('refreshApprovals');

        $this->cancelActionModal();
        $this->loadTicket((int) $this->ticketId);
        session()->flash('message', 'Request ' . $decision . ' successfully.');
    }

    protected function ensureOfficeApproval(): ?Office_Approval
    {
        if ($this->officeApproval?->exists) {
            return $this->officeApproval;
        }

        if ($this->actionApprovalId === null) {
            return null;
        }

        $approval = Office_Approval::query()->find($this->actionApprovalId);

        if ($approval) {
            $this->officeApproval = $approval;
        }

        return $approval;
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

        // Update or create Office_Approval record
        $officeApproval = $this->resolveOfficeApproval($this->ticket);

        if ($officeApproval && $officeApproval->decision === 'pending') {
            // Update existing pending approval
            $officeApproval->update([
                'decision' => 'approved',
                'remarks' => $this->approvalRemarks,
                'user_id' => auth()->id(),
            ]);
        } else {
            // Create new approval record
            Office_Approval::create([
                'ticket_id' => $this->ticket->ticket_id,
                'office_id' => $this->officeId,
                'user_id' => auth()->id(),
                'decision' => 'approved',
                'remarks' => $this->approvalRemarks,
            ]);
        }

        // Notify ticket owner about status change
        $this->ticket->user->notify(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            'pending_osa_approval',
            $this->approvalRemarks
        ));

        // Notify OSA users that GSO has approved
        $osaUsers = User::where('role_id', User::ROLE_OSA)->get();
        foreach ($osaUsers as $osaUser) {
            $osaUser->notify(new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'pending_osa_approval',
                "GSO has approved this ticket. Remarks: {$this->approvalRemarks}"
            ));
        }

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

        // Update or create Office_Approval record
        $officeApproval = $this->resolveOfficeApproval($this->ticket);

        if ($officeApproval && $officeApproval->decision === 'pending') {
            // Update existing pending approval
            $officeApproval->update([
                'decision' => 'rejected',
                'remarks' => $this->rejectionRemarks,
                'user_id' => auth()->id(),
            ]);
        } else {
            // Create new approval record
            Office_Approval::create([
                'ticket_id' => $this->ticket->ticket_id,
                'office_id' => $this->officeId,
                'user_id' => auth()->id(),
                'decision' => 'rejected',
                'remarks' => $this->rejectionRemarks,
            ]);
        }

        // Notify ticket owner about status change
        $this->ticket->user->notify(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            'rejected',
            $this->rejectionRemarks
        ));

        // Notify OSA users that GSO has rejected
        $osaUsers = User::where('role_id', User::ROLE_OSA)->get();
        foreach ($osaUsers as $osaUser) {
            $osaUser->notify(new TicketStatusUpdatedNotification(
                $this->ticket,
                $oldStatus,
                'rejected',
                "GSO has rejected this ticket. Remarks: {$this->rejectionRemarks}"
            ));
        }

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
     * Add a comment to the ticket
     */
    public function addComment()
    {
        if (empty(trim($this->comment))) {
            $this->warning('Please enter a comment.');

            return;
        }

        // Create comment
        $newComment = TicketComment::create([
            'ticket_id' => $this->ticket->ticket_id,
            'user_id' => auth()->id(),
            'content' => $this->comment,
        ]);

        // Clear the input
        $this->comment = '';

        // Reload ticket with comments and user relationship
        $this->ticket->load([
            'comments:id,ticket_id,user_id,content,created_at',
            'comments.user:user_id,name,role_id,avatar_style,avatar_seed',
            'comments.user.role:role_id,role_name',
            'user:user_id,name,email,role_id',
        ]);

        // Notify relevant parties
        $this->notifyCommentAdded($newComment);

        $this->success('Comment added successfully.');
        $this->dispatch('comment-added');
    }

    /**
     * Notify relevant users when a comment is added
     * GSO comment → Notify ticket owner (Student Org) and OSA
     */
    private function notifyCommentAdded(TicketComment $comment)
    {
        $commenter = auth()->user();

        // Ensure ticket user relationship is loaded
        if (! $this->ticket->relationLoaded('user')) {
            $this->ticket->load('user:user_id,name,email,role_id');
        }

        $ticketOwner = $this->ticket->user;
        $usersToNotify = collect();

        // Always notify the ticket owner (Student Org) if they didn't make the comment
        if ($ticketOwner && $ticketOwner->user_id !== $commenter->user_id) {
            $usersToNotify->push($ticketOwner);
        }

        // Notify OSA users
        $osaUsers = User::where('role_id', User::ROLE_OSA)->get();
        $usersToNotify = $usersToNotify->merge($osaUsers);

        // Send DB + broadcast immediately; queue mail separately
        $usersToNotify->unique('user_id')->each(function ($user) use ($comment, $commenter) {
            $user->notifyNow(new TicketCommentNotification($this->ticket, $comment, $commenter, ['database', 'broadcast']));
            $user->notify(new TicketCommentNotification($this->ticket, $comment, $commenter, ['mail']));
        });

        // Dispatch real-time notification event
        if ($usersToNotify->isNotEmpty()) {
            $this->dispatch('refresh-notifications');
        }
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
    public function downloadAttachment(int $attachmentId)
    {
        $attachment = $this->ticket->attachments->firstWhere('attachment_id', $attachmentId);

        if (! $attachment) {
            $this->warning('Attachment not found.');

            return null;
        }

        $url = $this->makeTemporaryUrl($attachment->file_path, $attachment->file_name, true);

        return redirect()->away($url);
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
