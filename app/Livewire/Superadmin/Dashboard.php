<?php

namespace App\Livewire\Superadmin;

use App\Models\Event;
use App\Models\Event_Schedule;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Lazy]
class Dashboard extends Component
{
    use Toast;

    #[Title('Superadmin - Dashboard')]
    #[Layout('components.layouts.superadmin')]
    public function placeholder()
    {
        return view('livewire.superadmin.placeholders.dashboard');
    }

    public function render()
    {
        return view('livewire.superadmin.dashboard')->with([
            'stats' => $this->stats,
            'todaySnapshot' => $this->todaySnapshot,
            'attentionRequired' => $this->attentionRequired,
            'upcomingEvents' => $this->upcomingEvents,
        ]);
    }

    #[Computed(persist: true, seconds: 300)] // 5 minutes cache
    public function stats(): array
    {
        return Cache::remember('superadmin_dashboard_stats', 300, function () {
            $pendingTickets = Ticket::where('status', 'pending')->count();
            $gsoReviewTickets = Ticket::where('status', 'gso_review')->count();
            $forRevisionTickets = Ticket::where('status', 'for_revision')->count();

            return [
                'totalUsers' => User::count(),
                'totalTickets' => Ticket::count(),
                'totalEvents' => Event::count(),
                'pendingTickets' => $pendingTickets,
                'gsoReviewTickets' => $gsoReviewTickets,
                'forRevisionTickets' => $forRevisionTickets,
                'eventsThisWeek' => Event::whereHas('eventSchedules', function ($q) {
                    $q->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()]);
                })->count(),
                'upcomingEventsCount' => Event::whereHas('eventSchedules', function ($q) {
                    $q->where('start_date', '>=', now()->toDateString())
                        ->where('start_date', '<=', now()->addDays(7)->toDateString());
                })->count(),
            ];
        });
    }

    #[Computed(persist: true, seconds: 180)] // 3 minutes cache
    public function todaySnapshot(): array
    {
        return Cache::remember('superadmin_dashboard_today_snapshot', 180, function () {
            $today = now()->toDateString();
            $todayStart = now()->startOfDay();
            $todayEnd = now()->endOfDay();

            return [
                'eventsToday' => Event::whereHas('eventSchedules', function ($q) use ($today) {
                    $q->whereDate('start_date', $today);
                })->count(),
                'ticketsSubmittedToday' => Ticket::whereDate('created_at', $today)->count(),
                'ticketsApprovedToday' => Ticket::where('status', 'approved')
                    ->whereDate('updated_at', $today)->count(),
                'ticketsRejectedToday' => Ticket::where('status', 'rejected')
                    ->whereDate('updated_at', $today)->count(),
                'newUsersToday' => User::whereDate('created_at', $today)->count(),
            ];
        });
    }

    #[Computed(persist: true, seconds: 120)] // 2 minutes cache
    public function attentionRequired(): array
    {
        return Cache::remember('superadmin_dashboard_attention', 120, function () {
            // Tickets awaiting OSA review (pending status)
            $pendingOsaReview = Ticket::where('status', 'pending')
                ->orderBy('created_at', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->ticket_id,
                        'title' => $ticket->title,
                        'type' => 'pending_review',
                        'days_waiting' => $ticket->created_at->diffInDays(now()),
                        'created_at' => $ticket->created_at->format('M d, Y'),
                    ];
                });

            // Tickets stuck in GSO review for more than 3 days
            $stuckGsoReview = Ticket::where('status', 'gso_review')
                ->where('updated_at', '<', now()->subDays(3))
                ->orderBy('updated_at', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->ticket_id,
                        'title' => $ticket->title,
                        'type' => 'stuck_gso',
                        'days_waiting' => $ticket->updated_at->diffInDays(now()),
                        'created_at' => $ticket->created_at->format('M d, Y'),
                    ];
                });

            // Tickets needing revision follow-up (for_revision for more than 5 days)
            $revisionFollowup = Ticket::where('status', 'for_revision')
                ->where('updated_at', '<', now()->subDays(5))
                ->orderBy('updated_at', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->ticket_id,
                        'title' => $ticket->title,
                        'type' => 'revision_overdue',
                        'days_waiting' => $ticket->updated_at->diffInDays(now()),
                        'created_at' => $ticket->created_at->format('M d, Y'),
                    ];
                });

            return [
                'pending_osa_review' => $pendingOsaReview->toArray(),
                'stuck_gso_review' => $stuckGsoReview->toArray(),
                'revision_followup' => $revisionFollowup->toArray(),
                'total_attention' => $pendingOsaReview->count() + $stuckGsoReview->count() + $revisionFollowup->count(),
            ];
        });
    }

    #[Computed(persist: true, seconds: 300)] // 5 minutes cache
    public function upcomingEvents(): array
    {
        return Cache::remember('superadmin_dashboard_upcoming_events', 300, function () {
            $today = now()->toDateString();
            $nextWeek = now()->addDays(7)->toDateString();

            return Event_Schedule::select(['schedule_id', 'event_id', 'start_date', 'start_time', 'end_time', 'venue'])
                ->with(['event:event_id,event_name'])
                ->whereBetween('start_date', [$today, $nextWeek])
                ->orderBy('start_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->limit(7)
                ->get()
                ->map(function ($schedule) {
                    $eventDate = Carbon::parse($schedule->start_date);
                    $isToday = $eventDate->isToday();
                    $isTomorrow = $eventDate->isTomorrow();

                    return [
                        'id' => $schedule->schedule_id,
                        'event_name' => $schedule->event ? $schedule->event->event_name : 'Unknown Event',
                        'venue' => $schedule->venue ?? 'TBD',
                        'date' => $eventDate->format('M d, Y'),
                        'day_label' => $isToday ? 'Today' : ($isTomorrow ? 'Tomorrow' : $eventDate->format('l')),
                        'time' => $schedule->start_time ? Carbon::parse($schedule->start_time)->format('g:i A') : 'All Day',
                        'is_today' => $isToday,
                        'is_tomorrow' => $isTomorrow,
                    ];
                })
                ->toArray();
        });
    }
}
