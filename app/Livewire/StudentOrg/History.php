<?php

namespace App\Livewire\StudentOrg;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

class History extends Component
{
    #[Title('Event History - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $yearFilter = '';
    public $showDetailsModal = false;
    public $loadingDetails = false;
    public $selectedTicket = null;

    public function openDetailsModal($ticketId)
    {
        $this->showDetailsModal = true;
        $this->loadingDetails = true;
        $this->selectedTicket = null;

        // Dispatch browser event to load ticket details after modal renders
        $this->dispatch('modal-opened', ticketId: $ticketId);
    }

    #[On('modal-opened')]
    public function loadTicketDetails($ticketId)
    {
        $this->selectedTicket = \App\Models\Ticket::with([
            'user.studentOrganization.course',
            'user.position',
            'eventType',
            'fundSource',
            'attachments'
        ])->findOrFail($ticketId);

        $this->loadingDetails = false;
    }

    public function exportReport()
    {
        // Implement export logic
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->typeFilter = '';
        $this->yearFilter = '';
    }

    public function getStatsProperty()
    {
        $userId = auth()->id();

        // Total events (approved + rejected)
        $totalEvents = DB::table('tickets')
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'rejected'])
            ->count();

        // Approved count
        $approvedCount = DB::table('tickets')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->count();

        // Rejected count
        $rejectedCount = DB::table('tickets')
            ->where('user_id', $userId)
            ->where('status', 'rejected')
            ->count();

        // Calculate percentages
        $approvedPercentage = $totalEvents > 0 ? round(($approvedCount / $totalEvents) * 100) : 0;
        $rejectedPercentage = $totalEvents > 0 ? round(($rejectedCount / $totalEvents) * 100) : 0;

        // Average processing days (from created_at to when status changed to approved)
        $avgProcessingDays = DB::table('tickets')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->value('avg_days');

        $avgProcessingDays = $avgProcessingDays ? round($avgProcessingDays, 1) : 0;

        return [
            'total' => $totalEvents,
            'approved' => [
                'count' => $approvedCount,
                'percentage' => $approvedPercentage,
            ],
            'rejected' => [
                'count' => $rejectedCount,
                'percentage' => $rejectedPercentage,
            ],
            'avgProcessingDays' => $avgProcessingDays,
        ];
    }

    public function getEventTypeDistributionProperty()
    {
        $userId = auth()->id();

        // Get count of approved tickets by event type
        $eventTypes = DB::table('tickets')
            ->join('event__types', 'tickets.event_type_id', '=', 'event__types.event_type_id')
            ->where('tickets.user_id', $userId)
            ->where('tickets.status', 'approved')
            ->select('event__types.type_name', DB::raw('COUNT(*) as count'))
            ->groupBy('event__types.event_type_id', 'event__types.type_name')
            ->orderByDesc('count')
            ->get();

        $total = $eventTypes->sum('count');

        if ($total === 0) {
            return [];
        }

        // Define colors for each event type
        $colors = [
            'bg-blue-500',
            'bg-green-500',
            'bg-yellow-500',
            'bg-purple-500',
            'bg-pink-500',
            'bg-indigo-500',
            'bg-red-500',
            'bg-orange-500',
        ];

        return $eventTypes->map(function ($item, $index) use ($total, $colors) {
            $percentage = round(($item->count / $total) * 100);
            return [
                'name' => $item->type_name,
                'count' => $item->count,
                'percentage' => $percentage,
                'color' => $colors[$index % count($colors)],
            ];
        });
    }

    public function getMonthlyActivityProperty()
    {
        $userId = auth()->id();

        // Get ticket counts for last 6 months
        $monthlyData = DB::table('tickets')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get()
            ->keyBy('month');

        // Generate last 6 months
        $months = collect();
        for ($i = 0; $i < 6; $i++) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $count = $monthlyData->get($monthKey)?->count ?? 0;

            $months->push([
                'name' => $date->format('F Y'),
                'count' => $count,
            ]);
        }

        // Calculate percentages based on max count
        $maxCount = $months->max('count') ?: 1;

        return $months->map(function ($item) use ($maxCount) {
            $item['percentage'] = round(($item['count'] / $maxCount) * 100);
            return $item;
        });
    }

    public function getTicketsProperty()
    {
        $userId = auth()->id();

        $query = DB::table('tickets')
            ->leftJoin('events', 'tickets.ticket_id', '=', 'events.ticket_id')
            ->leftJoin('event__types', function($join) {
                $join->on('events.event__type_id', '=', 'event__types.event_type_id')
                    ->orOn('tickets.event_type_id', '=', 'event__types.event_type_id');
            })
            ->leftJoin('event_schedules', function($join) {
                $join->on('events.event_id', '=', 'event_schedules.event_id')
                    ->where('event_schedules.status', 'active');
            })
            ->leftJoin('o_s_a__approvals as latest_osa', function($join) {
                $join->on('tickets.ticket_id', '=', 'latest_osa.ticket_id')
                    ->whereRaw('latest_osa.updated_at = (
                    SELECT MAX(updated_at)
                    FROM o_s_a__approvals
                    WHERE ticket_id = tickets.ticket_id
                )');
            })
            ->where('tickets.user_id', $userId)
            ->whereIn('tickets.status', ['approved', 'rejected', 'cancelled']);

        // Search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('tickets.title', 'like', '%' . $this->search . '%')
                    ->orWhere('tickets.description', 'like', '%' . $this->search . '%')
                    ->orWhere('tickets.venue_requested', 'like', '%' . $this->search . '%')
                    ->orWhere('event_schedules.venue', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->statusFilter) {
            $query->where('tickets.status', $this->statusFilter);
        }

        // Event type filter
        if ($this->typeFilter) {
            $query->where('event__types.type_name', $this->typeFilter);
        }

        // Year filter
        if ($this->yearFilter) {
            $query->where(function($q) {
                $q->whereYear('tickets.date_from', $this->yearFilter)
                    ->orWhereYear('event_schedules.start_date', $this->yearFilter);
            });
        }

        $tickets = $query->select(
            'tickets.*',
            'events.event_id',
            'events.notes as event_notes',
            'event__types.type_name as event_type_name',
            'event_schedules.schedule_id',
            'event_schedules.start_date',
            'event_schedules.end_date',
            'event_schedules.start_time',
            'event_schedules.end_time',
            'event_schedules.venue as schedule_venue',
            'event_schedules.status as schedule_status',
            'event_schedules.remarks as schedule_remarks',
            'latest_osa.remarks as osa_remarks',
            'latest_osa.decision as osa_decision'
        )
            ->orderByDesc('tickets.created_at')
            ->paginate(10);

        return $tickets;
    }

    public function getEventTypesProperty()
    {
        return \App\Models\Event_Type::orderBy('type_name')
            ->get()
            ->map(fn($type) => [
                'id' => $type->type_name,
                'name' => $type->type_name
            ])
            ->prepend(['id' => '', 'name' => 'All Types']);
    }

    public function getYearsProperty()
    {
        $userId = auth()->id();

        // Get distinct years from both ticket dates and event schedule dates
        $years = DB::table('tickets')
            ->leftJoin('events', 'tickets.ticket_id', '=', 'events.ticket_id')
            ->leftJoin('event_schedules', 'events.event_id', '=', 'event_schedules.event_id')
            ->where('tickets.user_id', $userId)
            ->whereIn('tickets.status', ['approved', 'rejected', 'cancelled'])
            ->selectRaw('DISTINCT YEAR(COALESCE(event_schedules.start_date, tickets.date_from)) as year')
            ->whereNotNull(DB::raw('YEAR(COALESCE(event_schedules.start_date, tickets.date_from))'))
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($year) => [
                'id' => (string) $year,
                'name' => (string) $year
            ])
            ->prepend(['id' => '', 'name' => 'All Years']);

        return $years;
    }

    public function render()
    {
        return view('livewire.student-org.history');
    }
}
