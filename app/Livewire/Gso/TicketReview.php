<?php

namespace App\Livewire\Gso;

use App\Livewire\Gso\Concerns\ResolvesOfficeContext;
use App\Models\Office_Approval;
use App\Models\Student_Organization;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class TicketReview extends Component
{
    use ResolvesOfficeContext, WithPagination;

    #[Title('Ticket Review - GSO')]
    #[Layout('components.layouts.gso-layout')]
    public string $filterOrganization = '';

    public string $filterPriority = '';

    public string $filterStatus = '';

    public string $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterOrganization()
    {
        $this->resetPage();
    }

    public function updatedFilterPriority()
    {
        $this->resetPage();
    }

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
                ->filter(fn (Office_Approval $approval) => $this->determinePriorityKey($this->extractEventDate($approval)) === 'high')
                ->count(),
        ];

        $totalTickets = (clone $baseQuery)->count();

        $ticketCollection = $this->filteredQuery($officeId)
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Office_Approval $approval) => $this->formatTicket($approval));

        if ($this->filterPriority !== '') {
            $ticketCollection = $ticketCollection->filter(fn (array $ticket) => ($ticket['priority'] ?? 'low') === $this->filterPriority);
        }

        // Manual pagination using Collection
        $perPage = 10;
        $currentPage = $this->paginators['page'] ?? 1;
        $tickets = $ticketCollection->values();

        // Create paginator manually
        $paginatedTickets = new \Illuminate\Pagination\LengthAwarePaginator(
            $tickets->forPage($currentPage, $perPage),
            $tickets->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        $organizations = Student_Organization::all();

        return view('livewire.gso.ticket-review', [
            'approvals' => $paginatedTickets,
            'organizations' => $organizations,
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
                'ticket.user' => fn($q) => $q->withTrashed()->with('studentOrganization'),
            ])
            ->where('office_id', $officeId)
            // Show tickets that GSO has interacted with, excluding completed by default
            ->whereHas('ticket', function ($query) {
                $query->whereHas('user', fn($q) => $q->withTrashed());

                // Exclude completed tickets unless specifically filtered
                if ($this->filterStatus !== 'completed') {
                    $query->where('status', '!=', 'completed');
                }
            });
    }

    protected function filteredQuery(int $officeId): Builder
    {
        return $this->baseQuery($officeId)
            ->when($this->filterStatus !== '', fn (Builder $query) => $this->applyStatusFilter($query, $this->filterStatus))
            ->when($this->filterOrganization !== '', function (Builder $query) {
                $organization = $this->filterOrganization;

                if ($organization) {
                    $query->whereHas('ticket.user', function (Builder $userQuery) use ($organization) {
                        $userQuery->withTrashed()
                            ->whereHas('studentOrganization', fn (Builder $orgQuery) => $orgQuery->where('org_id', $organization));
                    });
                }
            })
            ->when($this->search !== '', function (Builder $query) {
                $term = '%'.Str::of($this->search)->trim().'%';

                $query->whereHas('ticket', function (Builder $ticketQuery) use ($term) {
                    $ticketQuery
                        ->where('title', 'like', $term)
                        ->orWhere('ticket_number', 'like', $term)
                        ->orWhereHas('user', function (Builder $userQuery) use ($term) {
                            $userQuery->withTrashed()
                                ->whereHas('studentOrganization', fn (Builder $orgQuery) => $orgQuery->where('org_name', 'like', $term));
                        });
                });
            });
    }

    protected function formatTicket(Office_Approval $approval): array
    {
        $ticket = $approval->ticket;

        $eventDate = $this->parseDate($ticket?->getAttribute('date_from'));
        $dueDate = $this->parseDate($ticket?->getAttribute('date_to'));

        $requirements = collect(preg_split('/[,\n]+/', (string) ($ticket?->special_requirements ?? '')))
            ->map(fn (string $item) => trim($item))
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
            'venue' => $ticket?->venue_display_name ?? 'TBD',
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

        if ($decision === 'for_revision') {
            return ['for_revision', 'For Revision'];
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
            'for_revision' => ['key' => 'for_revision', 'label' => 'For Revision'],
            'completed' => ['key' => 'completed', 'label' => 'Completed'],
        ];
    }
}
