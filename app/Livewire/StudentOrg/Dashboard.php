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

    #[Computed(persist: true, seconds: 300)]
    public function tickets(): Collection
    {
        $user = auth()->user();

        return Cache::remember("studentorg_dashboard_tickets_{$user->user_id}", $this->cacheDuration, function () {
            return $this->getBaseTicketsQuery()
                ->with(['eventType', 'user' => function ($query) {
                    $query->withTrashed();
                }])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    #[Computed(persist: true, seconds: 300)]
    public function recentTickets(): Collection
    {
        $user = auth()->user();

        return Cache::remember("studentorg_dashboard_recent_{$user->user_id}", $this->cacheDuration, function () {
            return $this->getBaseTicketsQuery()
                ->with(['eventType', 'user' => function ($query) {
                    $query->withTrashed();
                }])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        });
    }

    #[Computed(persist: true, seconds: 300)]
    public function upcomingEventsCount(): int
    {
        $user = auth()->user();

        return Cache::remember("studentorg_dashboard_upcoming_{$user->user_id}", $this->cacheDuration, function () {
            return $this->getBaseTicketsQuery()
                ->where('status', 'approved')
                ->whereBetween('date_from', [now(), now()->addDays(30)])
                ->count();
        });
    }
}
