<?php

namespace App\Livewire\StudentOrg;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
    #[Title('Dashboard - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

    // Cache duration in seconds
    protected int $cacheDuration = 300; // 5 minutes

    public function render()
    {
        return view('livewire.student-org.dashboard', [
            'tickets' => $this->tickets,
            'recentTickets' => $this->recentTickets,
            'upcomingEventsCount' => $this->upcomingEventsCount,
        ]);
    }

    private function getBaseTicketsQuery()
    {
        $user = auth()->user();
        $query = Ticket::query();

        if ($user->position->position_name === 'President') {
            $query->where('user_id', $user->user_id);
        } elseif (in_array($user->position->position_name, ['Chairperson', 'Adviser'])) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->withTrashed()->where('org_id', $user->org_id);
            });
        }

        return $query;
    }

    #[Computed]
    public function tickets(): Collection
    {
        $user = auth()->user();

        return \App\Services\Cache\DashboardCacheService::getDashboardWidget('student_org', 'tickets', function () {
            return $this->getBaseTicketsQuery()
                ->with(['eventType', 'user' => function ($query) {
                    $query->withTrashed();
                }])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    #[Computed]
    public function recentTickets(): Collection
    {
        $user = auth()->user();

        return \App\Services\Cache\DashboardCacheService::getDashboardWidget('student_org', 'recent_tickets', function () {
            return $this->getBaseTicketsQuery()
                ->with(['eventType', 'user' => function ($query) {
                    $query->withTrashed();
                }])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        });
    }

    #[Computed]
    public function upcomingEventsCount(): int
    {
        $user = auth()->user();

        return \App\Services\Cache\DashboardCacheService::getDashboardWidget('student_org', 'upcoming_events', function () {
            return $this->getBaseTicketsQuery()
                ->where('status', 'approved')
                ->whereBetween('date_from', [now(), now()->addDays(30)])
                ->count();
        });
    }
}
