<?php

namespace App\Livewire\StudentOrg;

use App\Models\Ticket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
    #[Title('Dashboard - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]
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

    public function render()
    {
        $tickets = $this->getBaseTicketsQuery()
            ->with(['eventType', 'user' => function ($query) {
                $query->withTrashed();
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $recentTickets = $tickets->take(5);

        $upcomingEventsCount = $this->getBaseTicketsQuery()
            ->where('status', 'approved')
            ->whereBetween('date_from', [now(), now()->addDays(30)])
            ->count();

        return view('livewire.student-org.dashboard', [
            'tickets' => $tickets,
            'recentTickets' => $recentTickets,
            'upcomingEventsCount' => $upcomingEventsCount,
        ]);
    }
}
