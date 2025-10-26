<?php

namespace App\Livewire\Gso;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Details extends Component
{
    #[Title('Ticket Details - GSO')]
    #[Layout('components.layouts.gso-layout')]
    public Ticket $ticket;

    public ?int $ticketId = null;

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

    public function mount(Ticket $ticket = null, ?int $ticketId = null): void
    {
        $resolved = $this->resolveTicket($ticket, $ticketId);

        $this->hydrateFromTicket($resolved);
    }

    public function loadTicket(int $ticketId): void
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
        ]);

        return $resolved;
    }

    protected function hydrateFromTicket(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->ticketId = $ticket->getAttribute('ticket_id');

        $this->requirements = $this->buildRequirements();
        $this->overview = $this->buildOverview();
        $this->organization = $this->buildOrganization();
        $this->eventDetails = $this->buildEventDetails($this->requirements);
        $this->participants = $this->buildParticipants();
        $this->schedules = $this->buildSchedules();
        $this->attachments = $this->buildAttachments();
        $this->osaApprovals = $this->buildOsaApprovals();
        $this->officeApprovals = $this->buildOfficeApprovals();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildOverview(): array
    {
        $status = $this->normalizeStatus($this->ticket->status);
        $priorityLabel = $this->resolvePriority($this->ticket->total_participants);
        $priorityKey = Str::of($priorityLabel)->lower()->toString();

        return [
            'ticket_number' => $this->ticket->ticket_number ?? 'N/A',
            'status' => $status,
            'status_label' => $this->resolveStatusLabel($status),
            'status_badge' => $this->resolveStatusBadge($status),
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
     * @param array<int, string> $requirements
     * @return array<string, mixed>
     */
    protected function buildEventDetails(array $requirements): array
    {
        $dateFrom = $this->ticketAttribute('date-from');
        $dateTo = $this->ticketAttribute('date-to');
        $timeFrom = $this->ticketAttribute('time-from');
        $timeTo = $this->ticketAttribute('time-to');

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
            'notes' => $this->ticket->events
                ->pluck('notes')
                ->filter()
                ->values()
                ->all(),
        ];
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
            ->map(fn(string $item) => trim($item))
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
                $label = 'Event ' . ($eventIndex + 1);

                return $event->eventSchedules
                    ->map(function ($schedule) use ($event, $label) {
                        $status = $this->normalizeStatus($schedule->status ?? 'pending');

                        return [
                            'id' => $schedule->schedule_id,
                            'event_label' => $label,
                            'event_notes' => $event->notes ?? null,
                            'datetime' => $schedule->schedule_date?->format('M d, Y g:i A') ?? '—',
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
            ->sortByDesc('updated_at')
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
        return $this->ticket->officeApprovals
            ->sortByDesc('updated_at')
            ->map(function ($approval) {
                $decision = $this->normalizeStatus($approval->decision ?? 'pending');

                return [
                    'id' => $approval->id,
                    'office' => $approval->office?->office_name ?? '—',
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

    protected function resolvePriority(?int $totalParticipants): string
    {
        if ($totalParticipants === null) {
            return 'Low';
        }

        return match (true) {
            $totalParticipants >= 200 => 'High',
            $totalParticipants >= 100 => 'Medium',
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
}
