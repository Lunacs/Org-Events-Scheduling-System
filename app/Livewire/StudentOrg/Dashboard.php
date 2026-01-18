<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class Dashboard extends Component
{
    #[Title('Dashboard - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]
    public function render()
    {
        // Get all tickets for the authenticated user with event type
        $tickets = auth()->user()->tickets()->with('eventType')->orderBy('created_at', 'desc')->get();

        // Get the 5 most recent tickets
        $recentTickets = $tickets->take(5);

        // Get upcoming approved events within the next 30 days
        $upcomingEvents = auth()->user()->tickets()
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
