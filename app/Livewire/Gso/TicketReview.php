<?php

namespace App\Livewire\Gso;

use App\Livewire\Gso\Concerns\ResolvesOfficeContext;
use App\Models\Office_Approval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class TicketReview extends Component
{
    use ResolvesOfficeContext;

    #[Title('Ticket Review - GSO')]
    #[Layout('components.layouts.gso-layout')]
    public string $filterType = '';

    public string $filterPriority = '';

    public string $filterStatus = '';

    public string $search = '';

    public function render()
    {
    $user = Auth::user();
    $officeId = $this->resolveOfficeId($user);

        $baseQuery = $this->baseQuery($officeId);

        $pendingApprovals = (clone $baseQuery)->where('decision', 'pending')->get();

        $stats = [
            'pending' => $pendingApprovals->count(),
            'approvedToday' => (clone $baseQuery)
                ->where('decision', 'approved')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
            'urgent' => $pendingApprovals
                ->filter(fn(Office_Approval $approval) => $this->determinePriorityKey($this->extractEventDate($approval)) === 'high')
                ->count(),
        ];

        $totalTickets = (clone $baseQuery)->count();

        $ticketCollection = $this->filteredQuery($officeId)
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn(Office_Approval $approval) => $this->formatTicket($approval));

        if ($this->filterPriority !== '') {
            $ticketCollection = $ticketCollection->filter(fn(array $ticket) => ($ticket['priority'] ?? 'low') === $this->filterPriority);
        }

        $tickets = $ticketCollection->values();

        return view('livewire.gso.ticket-review', [
            'tickets' => $tickets,
            'stats' => $stats,
            'totalTickets' => $totalTickets,
            'statusDefinitions' => $this->statusDefinitions(),
        ]);
    }

    protected function baseQuery(int $officeId): Builder
    {
        return Office_Approval::query()
            ->with([
                'ticket.eventType',
                'ticket.user.studentOrganization',
            ])
            ->where('office_id', $officeId);
    }

    protected function filteredQuery(int $officeId): Builder
    {
        return $this->baseQuery($officeId)
            ->when($this->filterStatus !== '', fn(Builder $query) => $this->applyStatusFilter($query, $this->filterStatus))
            ->when($this->filterType !== '', function (Builder $query) {
                $type = $this->mapTypeFilter($this->filterType);

                if ($type) {
                    $query->whereHas('ticket.eventType', fn(Builder $typeQuery) => $typeQuery->whereRaw('LOWER(type_name) = ?', [Str::lower($type)]));
                }
            })
            ->when($this->search !== '', function (Builder $query) {
                $term = '%' . Str::of($this->search)->trim() . '%';

                $query->whereHas('ticket', function (Builder $ticketQuery) use ($term) {
                    $ticketQuery
                        ->where('title', 'like', $term)
                        ->orWhere('ticket_number', 'like', $term)
                        ->orWhereHas('user.studentOrganization', fn(Builder $orgQuery) => $orgQuery->where('org_name', 'like', $term));
                });
            });
    }

    protected function formatTicket(Office_Approval $approval): array
    {
        $ticket = $approval->ticket;

    $eventDate = $this->parseDate($ticket?->getAttribute('date_from'));
    $dueDate = $this->parseDate($ticket?->getAttribute('date_to'));

        $requirements = collect(preg_split('/[,\n]+/', (string) ($ticket?->special_requirements ?? '')))
            ->map(fn(string $item) => trim($item))
            ->filter()
            ->values()
            ->all();

        $priority = $this->resolvePriority($eventDate);
        [$statusKey, $statusLabel] = $this->resolveStatusAttributes($approval);

        return [
            'approval_id' => $approval->id,
            'ticket_id' => $ticket?->ticket_id,
            'ticket_number' => $ticket?->ticket_number ?? 'N/A',
            'event_name' => $ticket?->title ?? 'N/A',
            'organization' => $ticket?->user?->studentOrganization?->org_name
                ?? $ticket?->user?->name
                ?? 'N/A',
            'request_type' => $ticket?->eventType?->type_name ?? 'N/A',
            'event_date' => $eventDate?->format('M d, Y') ?? 'N/A',
            'venue' => $ticket?->venue_requested ?? 'TBD',
            'priority' => $priority['key'],
            'priority_label' => $priority['label'],
            'priority_days_until' => $priority['days_until'],
            'status' => $statusKey,
            'status_label' => $statusLabel,
            'office_id' => $approval->office_id ?? $this->resolveOfficeId(Auth::user()),
            'attendees' => $ticket?->total_participants,
            'submitted_date' => optional($ticket?->created_at)->format('M d, Y') ?? '—',
            'due_date' => $dueDate?->format('M d, Y') ?? '—',
            'description' => $ticket?->description ?? 'No description provided.',
            'requirements' => $requirements,
        ];
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

    protected function mapTypeFilter(string $value): ?string
    {
        return match ($value) {
            'venue' => 'Venue Booking',
            'equipment' => 'Equipment',
            'logistics' => 'Logistics',
            'catering' => 'Catering',
            default => null,
        };
    }

    protected function normalizeStatus(string $value): string
    {
        $value = Str::of($value)->snake()->toString();

        return array_key_exists($value, $this->statusDefinitions()) ? $value : 'pending';
    }

    protected function applyStatusFilter(Builder $query, string $status): Builder
    {
        $normalized = $this->normalizeStatus($status);

        return $query->where('decision', $normalized);
    }

    protected function resolveStatusAttributes(Office_Approval $approval): array
    {
        $decision = $approval->decision;

        if ($decision === 'approved') {
            return ['approved', 'Approved'];
        }

        if ($decision === 'rejected') {
            return ['rejected', 'Rejected'];
        }

        $definitions = $this->statusDefinitions();
        $key = $definitions[$decision]['key'] ?? 'pending';
        $label = $definitions[$decision]['label'] ?? ucfirst($key);

        return [$key, $label];
    }

    protected function statusDefinitions(): array
    {
        return [
            'pending' => ['key' => 'pending', 'label' => 'Pending'],
            'approved' => ['key' => 'approved', 'label' => 'Approved'],
            'rejected' => ['key' => 'rejected', 'label' => 'Rejected'],
        ];
    }
}
