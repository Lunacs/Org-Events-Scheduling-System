<?php

namespace App\Livewire\Gso;

use App\Livewire\Gso\Concerns\ResolvesOfficeContext;
use App\Models\Event_Schedule;
use App\Models\Office_Approval;
use App\Models\Transaction_Logs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Dashboard extends Component
{
    use ResolvesOfficeContext, Toast;
    #[Title('Dashboard - GSO')]
    #[Layout('components.layouts.gso-layout')]

    public int $refreshTicker = 0;

    public function render()
    {
        $user = Auth::user();
        $officeId = $this->resolveOfficeId($user);

        $baseApprovalQuery = Office_Approval::query()
            ->where('office_id', $officeId);

        $stats = [
            'pending' => (clone $baseApprovalQuery)->where('decision', 'pending')->count(),
            'approvedToday' => (clone $baseApprovalQuery)
                ->where('decision', 'approved')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
            'for_revisionToday' => (clone $baseApprovalQuery)
                ->where('decision', 'for_revision')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
            'upcomingEvents' => Event_Schedule::query()
                ->where('status', 'approved')
                ->where('start_date', '>=', Carbon::now())
                ->count(),
        ];

        $pendingApprovals = (clone $baseApprovalQuery)
            ->where('decision', 'pending')
            ->with([
                'ticket.eventType',
                'ticket.user.studentOrganization',
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn(Office_Approval $approval) => $this->formatPendingApproval($approval))
            ->values();

        $approvalSnapshot = (clone $baseApprovalQuery)
            ->with([
                'ticket.eventType',
                'ticket.user.studentOrganization',
            ])
            ->orderByDesc('created_at')
            ->limit(25)
            ->get()
            ->unique('ticket_id')
            ->values()
            ->take(5)
            ->map(fn(Office_Approval $approval) => $this->formatPendingApproval($approval))
            ->values();

        $recentActivities = Transaction_Logs::query()
            ->with('user')
            ->whereHas('user', fn($userQuery) => $userQuery->where('office_id', $officeId))
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function (Transaction_Logs $log) {
                return [
                    'id' => $log->log_id,
                    'action' => $log->action,
                    'details' => $log->details,
                    'time_ago' => optional($log->created_at)->diffForHumans(),
                ];
            })
            ->values();

        $ticketsInQueue = $this->countUniqueTicketsInQueue($officeId);

        return view('livewire.gso.dashboard', [
            'stats' => $stats,
            'pendingApprovals' => $pendingApprovals,
            'approvalSnapshot' => $approvalSnapshot,
            'recentActivities' => $recentActivities,
            'user' => $user,
            'ticketsInQueue' => $ticketsInQueue,
        ]);
    }

    public function refreshData(): void
    {
        $this->refreshTicker++;
    $this->success('Dashboard data refreshed!', position: 'toast-top');
    }

    protected function formatPendingApproval(Office_Approval $approval): array
    {
        $ticket = $approval->ticket;

    $rawDate = $ticket?->getAttribute('date_from');
        $eventDate = $this->parseDate($rawDate);

        if (! $eventDate && $ticket?->created_at) {
            $eventDate = $ticket->created_at instanceof Carbon
                ? $ticket->created_at
                : $this->parseDate((string) $ticket->created_at);
        }

        $priorityKey = $this->determinePriorityKey($eventDate);

        return [
            'approval_id' => $approval->id,
            'ticket_id' => $ticket?->ticket_id,
            'ticket_number' => $ticket?->ticket_number ?? 'N/A',
            'event_title' => $ticket?->title ?? 'N/A',
            'organization' => $ticket?->user?->studentOrganization?->org_name
                ?? $ticket?->user?->name
                ?? 'N/A',
            'request_type' => trim((string) ($ticket?->eventType?->type_name ?? 'N/A')),
            'event_date' => $eventDate?->translatedFormat('M d, Y') ?? 'N/A',
            'priority' => $this->resolvePriority($eventDate),
            'priority_key' => $priorityKey,
            'days_until_event' => $this->daysUntil($eventDate),
        ];
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function resolvePriority(?Carbon $eventDate): string
    {
        return match ($this->determinePriorityKey($eventDate)) {
            'high' => 'High',
            'medium' => 'Medium',
            default => 'Low',
        };
    }

    protected function determinePriorityKey(?Carbon $eventDate): string
    {
        $daysUntil = $this->daysUntil($eventDate);

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

    protected function daysUntil(?Carbon $eventDate): ?int
    {
        if (! $eventDate) {
            return null;
        }

        return Carbon::now()->startOfDay()->diffInDays($eventDate->copy()->startOfDay(), false);
    }

    private function countUniqueTicketsInQueue(int $officeId): int
    {
        return (int) Office_Approval::query()
            ->where('office_id', $officeId)
            ->distinct('ticket_id')
            ->count('ticket_id');
    }
}
