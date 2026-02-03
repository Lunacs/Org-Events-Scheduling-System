<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class Dashboard extends Component
{
    #[Title('Dashboard - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]
    private function getBaseTicketsQuery()
    {
        $user = auth()->user();
        $query = \App\Models\Ticket::query();

        if ($user->position->position_name === 'President') {
            $query->where('user_id', $user->user_id);
        } elseif (in_array($user->position->position_name, ['Chairperson', 'Adviser'])) {
            $query->whereHas('user', function($q) use ($user) {
                $q->withTrashed()->where('org_id', $user->org_id);
            });
        }

        return $query;
    }

    public function render()
    {
        // Get all tickets with event type
        $tickets = $this->getBaseTicketsQuery()
            ->with(['eventType', 'user' => function($query) {
                $query->withTrashed();
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get the 5 most recent tickets
        $recentTickets = $tickets->take(5);

        // Get upcoming approved events within the next 30 days
        $upcomingEvents = $this->getBaseTicketsQuery()
            ->with(['user' => function($query) {
                $query->withTrashed();
            }])
            ->where('status', 'approved')
            ->whereBetween('date_from', [now(), now()->addDays(30)])
            ->orderBy('date_from', 'asc')
            ->get();

        // Get recent unread notifications (last 3)
        $recentNotifications = auth()->user()->unreadNotifications()
            ->take(3)
            ->get();

        return view('livewire.student-org.dashboard', [
            'tickets' => $tickets,
            'recentTickets' => $recentTickets,
            'upcomingEvents' => $upcomingEvents,
            'recentNotifications' => $recentNotifications,
        ]);
    }
}
