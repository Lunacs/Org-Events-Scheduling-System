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
        $tickets = auth()->user()->tickets()->with('eventType')->orderBy('created_at', 'desc');
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
            'tickets' => $tickets->get(),
            'upcomingEvents' => $upcomingEvents,
            'recentNotifications' => $recentNotifications,
        ]);
    }

}
