<?php

namespace App\Livewire\Gso;

use App\Models\Event_Schedule;
use App\Models\Office_Approval;
use App\Models\Transaction_Logs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
    #[Title('Dashboard - GSO')]
    #[Layout('components.layouts.gso-layout')]

    public function render()
    {
        $user = Auth::user();
        $officeId = $user?->office_id;

        $baseApprovalQuery = Office_Approval::query()
            ->when($officeId, fn($query) => $query->where('office_id', $officeId));

        $stats = [
            'pending' => (clone $baseApprovalQuery)->where('decision', 'pending')->count(),
            'approvedToday' => (clone $baseApprovalQuery)
                ->where('decision', 'approved')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
            'rejectedToday' => (clone $baseApprovalQuery)
                ->where('decision', 'rejected')
                ->whereDate('updated_at', Carbon::today())
                ->count(),
            'upcomingEvents' => Event_Schedule::query()
                ->where('status', 'approved')
                ->where('schedule_date', '>=', Carbon::now())
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

        $recentActivities = Transaction_Logs::query()
            ->with('user')
            ->when($officeId, fn($query) => $query->whereHas('user', fn($userQuery) => $userQuery->where('office_id', $officeId)))
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

        return view('livewire.gso.dashboard', [
            'stats' => $stats,
            'pendingApprovals' => $pendingApprovals,
            'recentActivities' => $recentActivities,
            'user' => $user,
        ]);
    }

    protected function formatPendingApproval(Office_Approval $approval): array
    {
        $ticket = $approval->ticket;

        $rawDate = $ticket?->getAttribute('date-from');
        $eventDate = $this->parseDate($rawDate);

        if (! $eventDate && $ticket?->created_at) {
            $eventDate = $ticket->created_at instanceof Carbon
                ? $ticket->created_at
                : $this->parseDate((string) $ticket->created_at);
        }

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
            'priority' => $this->resolvePriority($ticket?->total_participants),
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
}
