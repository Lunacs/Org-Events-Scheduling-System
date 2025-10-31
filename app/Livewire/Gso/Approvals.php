<?php

namespace App\Livewire\Gso;

use App\Models\Office_Approval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Approvals extends Component
{
    #[Title('Approvals Management - GSO')]
    #[Layout('components.layouts.gso-layout')]
    public string $search = '';

    public string $statusFilter = 'pending';

    public string $priorityFilter = 'all';

    public array $selectedRequests = [];

    public function render()
    {
        $user = Auth::user();
        $officeId = $user?->office_id;

        $baseQuery = $this->baseQuery($officeId);

        $pendingQuery = (clone $baseQuery)->where('decision', 'pending');

        $pendingApprovalsForStats = (clone $pendingQuery)->get();

        $stats = [
            'pending' => $pendingApprovalsForStats->count(),
            'todayApproved' => (clone $baseQuery)
                ->where('decision', 'approved')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
            'urgent' => $pendingApprovalsForStats
                ->filter(fn(Office_Approval $approval) => $this->determinePriorityKey($this->extractEventDate($approval)) === 'high')
                ->count(),
        ];

        $decision = $this->normalizeStatusFilter($this->statusFilter);

        $approvalsCollection = (clone $baseQuery)
            ->where('decision', $decision)
            ->when($this->search !== '', fn(Builder $query) => $this->applySearchFilter($query, $this->search))
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn(Office_Approval $approval) => $this->transformApproval($approval));

        if ($this->priorityFilter !== 'all') {
            $approvalsCollection = $approvalsCollection->filter(fn(array $approval) => ($approval['priority'] ?? 'low') === $this->priorityFilter);
        }

        $approvals = $approvalsCollection->values();

        return view('livewire.gso.approvals', [
            'approvals' => $approvals,
            'stats' => $stats,
            'statusDefinitions' => $this->statusDefinitions(),
        ]);
    }

    protected function baseQuery(?int $officeId): Builder
    {
        return Office_Approval::query()
            ->with([
                'ticket.eventType',
                'ticket.user.studentOrganization',
            ])
            ->when($officeId, fn(Builder $query) => $query->where('office_id', $officeId));
    }

    protected function normalizeStatusFilter(string $status): string
    {
        $normalized = Str::of($status)->lower()->snake()->toString();

        return $this->statusDefinitions()[$normalized]['key'] ?? 'pending';
    }

    protected function statusDefinitions(): array
    {
        return [
            'pending' => ['key' => 'pending', 'label' => 'Pending'],
            'approved' => ['key' => 'approved', 'label' => 'Approved'],
            'rejected' => ['key' => 'rejected', 'label' => 'Rejected'],
        ];
    }

    protected function applySearchFilter(Builder $query, string $search): void
    {
        $term = Str::lower(trim($search));

        if ($term === '') {
            return;
        }

        $likeTerm = '%' . $term . '%';

        $query->whereHas('ticket', function (Builder $ticketQuery) use ($likeTerm) {
            $ticketQuery
                ->whereRaw('LOWER(title) LIKE ?', [$likeTerm])
                ->orWhereRaw('LOWER(ticket_number) LIKE ?', [$likeTerm])
                ->orWhereHas('user.studentOrganization', fn(Builder $orgQuery) => $orgQuery->whereRaw('LOWER(org_name) LIKE ?', [$likeTerm]));
        });
    }

    protected function transformApproval(Office_Approval $approval): array
    {
        $ticket = $approval->ticket;

    $eventDate = $this->parseDate($ticket?->getAttribute('date_from'));
    $dueDate = $this->parseDate($ticket?->getAttribute('date_to'));

        $requirements = collect(preg_split('/[\,\n]+/', (string) ($ticket?->special_requirements ?? '')))
            ->map(fn(string $item) => trim($item))
            ->filter()
            ->values()
            ->all();

        $priority = $this->resolvePriority($eventDate);

        $statusDefinitions = $this->statusDefinitions();
        $status = $approval->decision ?? 'pending';
        $statusLabel = $statusDefinitions[$status]['label'] ?? ucfirst($status);

        return [
            'id' => $approval->id,
            'ticket_id' => $ticket?->ticket_id,
            'ticket_number' => $ticket?->ticket_number ?? 'N/A',
            'event_name' => $ticket?->title ?? 'N/A',
            'organization' => $ticket?->user?->studentOrganization?->org_name
                ?? $ticket?->user?->name
                ?? 'N/A',
            'request_type' => $ticket?->eventType?->type_name ?? 'N/A',
            'event_date' => $eventDate?->format('M d, Y') ?? 'N/A',
            'priority' => $priority['key'],
            'priority_label' => $priority['label'],
            'priority_days_until' => $priority['days_until'],
            'status' => $status,
            'status_label' => $statusLabel,
            'description' => $ticket?->description ?? 'No description provided.',
            'requirements' => $requirements,
            'submitted_date' => optional($ticket?->created_at)->format('M d, Y') ?? '—',
            'due_date' => $dueDate?->format('M d, Y') ?? '—',
            'remarks' => $approval->remarks ?? null,
            'participants' => $ticket?->total_participants,
        ];
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function resolvePriority(?Carbon $eventDate): array
    {
        $priorityKey = $this->determinePriorityKey($eventDate);

        $labels = [
            'high' => 'High Priority',
            'medium' => 'Medium Priority',
            'low' => 'Low Priority',
        ];

        return [
            'key' => $priorityKey,
            'label' => $labels[$priorityKey] ?? 'Low Priority',
            'days_until' => $this->daysUntilEvent($eventDate),
        ];
    }

    protected function determinePriorityKey(?Carbon $eventDate): string
    {
        $daysUntil = $this->daysUntilEvent($eventDate);

        if ($daysUntil === null) {
            return 'low';
        }

        if ($daysUntil <= 3) {
            return 'high';
        }

        if ($daysUntil <= 7) {
            return 'medium';
        }

        return 'low';
    }

    protected function daysUntilEvent(?Carbon $eventDate): ?int
    {
        if (! $eventDate) {
            return null;
        }

        return Carbon::now()->startOfDay()->diffInDays($eventDate->copy()->startOfDay(), false);
    }

    protected function extractEventDate(Office_Approval $approval): ?Carbon
    {
        $ticket = $approval->ticket;

        if (! $ticket) {
            return null;
        }

    return $this->parseDate($ticket->getAttribute('date_from'));
    }
}
