<?php

namespace App\Livewire\Superadmin\Calendar;

use App\Livewire\Components\EventCalendar as ComponentsEventCalendar;
use App\Models\Event;
use App\Models\Event_Schedule;
use App\Models\Ticket;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

class Index extends ComponentsEventCalendar
{
    use Toast;

    #[Title('Event Calendar - SuperAdmin')]
    #[Layout('components.layouts.superadmin')]

    // SuperAdmin can view all statuses
    // public $statusFilter = 'all';

    // Additional SuperAdmin controls
    public $selectedEventForAction = null;

    public $showActionModal = false;

    public $actionType = ''; // cancel, reschedule, delete

    // Override to include all events (not just approved)
    // public function mount()
    // {
    //     parent::mount();
    //     $this->statusFilter = 'all'; // SuperAdmin sees all
    // }

    protected function getRoleSpecificData(): array
    {
        return [
            'canEdit' => true,
            'canDelete' => true,
            'canForceApprove' => true,
        ];
    }

    public function openActionModal($eventId, $action)
    {
        $this->selectedEventForAction = Event::with(['ticket', 'eventSchedules'])->find($eventId);

        if (! $this->selectedEventForAction) {
            $this->error('Event not found!', position: 'toast-top');

            return;
        }

        $this->actionType = $action;
        $this->showActionModal = true;
    }

    public function closeActionModal()
    {
        $this->showActionModal = false;
        $this->selectedEventForAction = null;
        $this->actionType = '';
    }

    public function forceApproveEvent($eventId)
    {
        try {
            $event = Event::with('ticket')->find($eventId);

            if (! $event) {
                $this->error('Event not found!', position: 'toast-top');

                return;
            }

            // Update ticket status
            if ($event->ticket) {
                $event->ticket->update(['status' => 'approved']);
            }

            // Update event schedules
            Event_Schedule::where('event_id', $eventId)->update(['status' => 'approved']);

            // Log action
            TransactionLogService::log(
                'APPROVE',
                "SuperAdmin force-approved event: {$event->ticket->title}",
                Auth::user()->user_id
            );

            $this->success('Event force-approved successfully!', position: 'toast-top');
            $this->dispatch('calendar-refetch');
            $this->closeActionModal();
        } catch (\Exception $e) {
            $this->error('Failed to approve event: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function cancelEvent($eventId)
    {
        try {
            $event = Event::with('ticket')->find($eventId);

            if (! $event) {
                $this->error('Event not found!', position: 'toast-top');

                return;
            }

            // Update ticket status
            if ($event->ticket) {
                $event->ticket->update(['status' => 'cancelled']);
            }

            // Update event schedules
            Event_Schedule::where('event_id', $eventId)->update(['status' => 'cancelled']);

            // Log action
            TransactionLogService::log(
                'CANCEL',
                "SuperAdmin cancelled event: {$event->ticket->title}",
                Auth::user()->user_id
            );

            $this->success('Event cancelled successfully!', position: 'toast-top');
            $this->dispatch('calendar-refetch');
            $this->closeActionModal();
        } catch (\Exception $e) {
            $this->error('Failed to cancel event: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function forceDeleteEvent($eventId)
    {
        try {
            $event = Event::with('ticket')->find($eventId);

            if (! $event) {
                $this->error('Event not found!', position: 'toast-top');

                return;
            }

            $eventTitle = $event->ticket->title ?? 'Unknown Event';

            // Delete event schedules
            Event_Schedule::where('event_id', $eventId)->delete();

            // Delete ticket
            if ($event->ticket) {
                $event->ticket->delete();
            }

            // Delete event
            $event->delete();

            // Log action
            TransactionLogService::log(
                'DELETE',
                "SuperAdmin force-deleted event: {$eventTitle}",
                Auth::user()->user_id
            );

            $this->success('Event deleted successfully!', position: 'toast-top');
            $this->dispatch('calendar-refetch');
            $this->closeActionModal();
        } catch (\Exception $e) {
            $this->error('Failed to delete event: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    // Override the eventsForCalendar computed property to show all statuses for SuperAdmin
    #[Computed]
    public function eventsForCalendar()
    {
        // If statusFilter is 'all', don't filter by status
        if ($this->statusFilter === 'all') {
            $eventSchedules = Event_Schedule::select(['schedule_id', 'event_id', 'start_date', 'end_date', 'start_time', 'end_time', 'venue', 'status'])
                ->with([
                    'event' => fn($q) => $q->select(['event_id', 'ticket_id', 'event__type_id'])
                        ->with([
                            'ticket' => fn($q) => $q->select(['ticket_id', 'title', 'description', 'venue_requested', 'user_id', 'status', 'ticket_number'])
                                ->with([
                                    'user' => fn($q) => $q->select(['user_id', 'org_id'])
                                        ->with('studentOrganization:org_id,org_name,logo'),
                                ]),
                            'eventType:event_type_id,type_name',
                        ]),
                ])
                // Only show approved event schedules
                ->where('status', 'approved')
                // Apply organization filter if set
                ->when($this->organizationFilter, fn($query) => $query->whereHas('event.ticket.user', fn($q) => $q->where('org_id', $this->organizationFilter)))
                // Apply event type filter if set
                ->when($this->eventTypeFilter, fn($query) => $query->whereHas('event', fn($q) => $q->where('event__type_id', $this->eventTypeFilter)))
                ->get();
        } else {
            // Use parent method for specific status filtering
            return parent::eventsForCalendar();
        }

        // Process events (similar logic to parent)
        $allEvents = [];

        foreach ($eventSchedules as $schedule) {
            $event = $schedule->event;

            $rawStartTime = $schedule->getRawOriginal('start_time');
            $rawEndTime = $schedule->getRawOriginal('end_time');

            $startTime = $rawStartTime ?: '09:00:00';
            $endTime = $rawEndTime ?: '17:00:00';

            if (strlen($startTime) > 8) {
                $startTime = substr($startTime, 0, 8);
            }
            if (strlen($endTime) > 8) {
                $endTime = substr($endTime, 0, 8);
            }

            $startDate = $schedule->start_date->format('Y-m-d');
            $endDate = $schedule->end_date ? $schedule->end_date->format('Y-m-d') : $startDate;

            if ($startDate === $endDate) {
                $startISO = $startDate . 'T' . $startTime . '+08:00';
                $endISO = $endDate . 'T' . $endTime . '+08:00';

                $allEvents[] = [
                    'id' => $event->event_id,
                    'title' => $event->ticket->title,
                    'start' => $startISO,
                    'end' => $endISO,
                    'allDay' => false,
                    'backgroundColor' => $this->getEventColor($event),
                    'borderColor' => $this->getEventColor($event),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'organization' => $event->ticket->user->studentOrganization->org_name ?? 'No Organization',
                        'eventType' => $event->eventType?->type_name ?? 'N/A',
                        'venue' => $schedule->venue ?? $event->ticket->venue_requested ?? 'TBD',
                        'description' => $event->ticket->description,
                        'ticketNumber' => $event->ticket->ticket_number,
                        'status' => $event->ticket->status ?? 'pending',
                        'rawStartTime' => $startTime,
                        'rawEndTime' => $endTime,
                        'rawStartDate' => $startDate,
                        'rawEndDate' => $endDate,
                    ],
                ];
            } else {
                // Multi-day event logic (same as parent)
                // Calculate days of week from start_date to end_date
                $startCarbon = \Carbon\Carbon::parse($startDate);
                $endCarbon = \Carbon\Carbon::parse($endDate);
                $daysOfWeek = [];

                $current = $startCarbon->copy();
                while ($current->lte($endCarbon)) {
                    $dayOfWeek = $current->dayOfWeek;
                    if (! in_array($dayOfWeek, $daysOfWeek)) {
                        $daysOfWeek[] = $dayOfWeek;
                    }
                    $current->addDay();
                }

                if (count($daysOfWeek) >= 5) {
                    $daysOfWeek = [0, 1, 2, 3, 4, 5, 6];
                }

                $endRecurDate = \Carbon\Carbon::parse($endDate)->addDay()->format('Y-m-d');

                $commonProps = [
                    'title' => $event->ticket->title,
                    'backgroundColor' => $this->getEventColor($event),
                    'borderColor' => $this->getEventColor($event),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'organization' => $event->ticket->user->studentOrganization->org_name ?? 'No Organization',
                        'eventType' => $event->eventType?->type_name ?? 'N/A',
                        'venue' => $schedule->venue ?? $event->ticket->venue_requested ?? 'TBD',
                        'description' => $event->ticket->description,
                        'ticketNumber' => $event->ticket->ticket_number,
                        'status' => $event->ticket->status ?? 'pending',
                        'rawStartTime' => $startTime,
                        'rawEndTime' => $endTime,
                        'rawStartDate' => $startDate,
                        'rawEndDate' => $endDate,
                    ],
                ];

                // Spanning event for month view
                $allEvents[] = array_merge($commonProps, [
                    'id' => $event->event_id . '_span',
                    'groupId' => $event->event_id,
                    'start' => $startDate . 'T' . $startTime . '+08:00',
                    'end' => $endDate . 'T' . $endTime . '+08:00',
                    'allDay' => false,
                    'display' => 'block',
                    'extendedProps' => array_merge($commonProps['extendedProps'], [
                        'forMonthView' => true,
                    ]),
                ]);

                // Recurring event for week/day views
                $allEvents[] = array_merge($commonProps, [
                    'id' => $event->event_id . '_recur',
                    'groupId' => $event->event_id,
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'startRecur' => $startDate,
                    'endRecur' => $endRecurDate,
                    'daysOfWeek' => $daysOfWeek,
                    'allDay' => false,
                    'display' => 'block',
                    'extendedProps' => array_merge($commonProps['extendedProps'], [
                        'forTimeView' => true,
                    ]),
                ]);
            }
        }

        return $allEvents;
    }

    // Override uniqueEventsCount to handle 'all' status for SuperAdmin
    #[Computed]
    public function uniqueEventsCount()
    {
        // Count unique events that match the current filters
        $query = Event_Schedule::query()
            ->where('status', 'approved');

        // For SuperAdmin: If statusFilter is 'all', don't filter by ticket status
        if ($this->statusFilter !== 'all') {
            $query->whereHas('event.ticket', fn($q) => $q->where('status', $this->statusFilter));
        }

        // Apply organization filter if set
        $query->when($this->organizationFilter, fn($query) => $query->whereHas('event.ticket.user', fn($q) => $q->where('org_id', $this->organizationFilter)));

        // Apply event type filter if set
        $query->when($this->eventTypeFilter, fn($query) => $query->whereHas('event', fn($q) => $q->where('event__type_id', $this->eventTypeFilter)));

        // Count distinct event_ids to get unique events (MySQL compatible)
        return (int) $query->selectRaw('COUNT(DISTINCT event_id) as count')->value('count');
    }

    // Override upcomingEventsThisMonth to handle 'all' status for SuperAdmin
    #[Computed]
    public function upcomingEventsThisMonth()
    {
        $startOfMonth = $this->currentDate->copy()->startOfMonth();
        $endOfMonth = $this->currentDate->copy()->endOfMonth();

        $query = Event_Schedule::select(['schedule_id', 'event_id', 'start_date', 'end_date', 'start_time', 'end_time', 'venue', 'status'])
            ->with([
                'event' => fn($q) => $q->select(['event_id', 'ticket_id', 'event__type_id'])
                    ->with([
                        'ticket' => fn($q) => $q->select(['ticket_id', 'title', 'description', 'venue_requested', 'user_id', 'status', 'ticket_number'])
                            ->with([
                                'user' => fn($q) => $q->select(['user_id', 'org_id'])
                                    ->with('studentOrganization:org_id,org_name'),
                            ]),
                        'eventType:event_type_id,type_name',
                    ]),
            ])
            ->where('status', 'approved')
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth]);

        // For SuperAdmin: If statusFilter is 'all', show all ticket statuses
        if ($this->statusFilter !== 'all') {
            $query->whereHas('event.ticket', fn($q) => $q->where('status', $this->statusFilter));
        }

        $eventSchedules = $query->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        return $eventSchedules->map(function ($schedule) {
            $event = $schedule->event;
            $rawStartTime = $schedule->getRawOriginal('start_time');
            $rawEndTime = $schedule->getRawOriginal('end_time');

            // Format date
            $startDate = \Carbon\Carbon::parse($schedule->start_date);
            $endDate = $schedule->end_date ? \Carbon\Carbon::parse($schedule->end_date) : $startDate;

            // Format time
            $formatTime = function ($timeStr) {
                if (! $timeStr) {
                    return '';
                }
                try {
                    $parts = explode(':', $timeStr);
                    $hours = (int) $parts[0];
                    $minutes = (int) ($parts[1] ?? 0);
                    $period = $hours >= 12 ? 'PM' : 'AM';
                    $hour12 = $hours % 12 ?: 12;

                    return sprintf('%d:%02d %s', $hour12, $minutes, $period);
                } catch (\Exception $e) {
                    return $timeStr;
                }
            };

            $startTimeFormatted = $formatTime($rawStartTime);
            $endTimeFormatted = $formatTime($rawEndTime);
            $timeRange = $startTimeFormatted && $endTimeFormatted
                ? "{$startTimeFormatted} - {$endTimeFormatted}"
                : ($startTimeFormatted ?: 'TBD');

            // Date range formatting
            $dateDisplay = $startDate->format('M d, Y');
            if ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
                $dateDisplay = $startDate->format('M d') . ' - ' . $endDate->format('M d, Y');
            }

            // Get event color using the same method as calendar
            $hexColor = $this->getEventColor($event);
            $colorName = $this->hexToTailwindColor($hexColor);

            return [
                'title' => $event->ticket->title ?? 'Untitled Event',
                'description' => $event->ticket->description ?? null,
                'organization' => $event->ticket->user->studentOrganization->org_name ?? 'No Organization',
                'organizationLogo' => $event->ticket->user->studentOrganization->logo ?? asset('images/default-org-logo.svg'),
                'eventType' => $event->eventType?->type_name ?? 'N/A',
                'date' => $dateDisplay,
                'time' => $timeRange,
                'datetime' => "{$dateDisplay} • {$timeRange}",
                'venue' => $schedule->venue ?? $event->ticket->venue_requested ?? 'TBD',
                'color' => $colorName,
                'hexColor' => $hexColor,
                'icon' => $this->getEventTypeIcon($event->eventType?->type_name ?? ''),
                'start_date' => $schedule->start_date,
                'event_id' => $event->event_id,
            ];
        })->toArray();
    }

    // Helper method from parent (needed for upcomingEventsThisMonth)
    private function hexToTailwindColor($hexColor): string
    {
        $colorMap = [
            '#10b981' => 'green',
            '#3b82f6' => 'blue',
            '#8b5cf6' => 'purple',
            '#f59e0b' => 'yellow',
            '#ef4444' => 'red',
            '#06b6d4' => 'cyan',
            '#84cc16' => 'lime',
            '#f97316' => 'orange',
        ];

        return $colorMap[$hexColor] ?? 'blue';
    }

    // Helper method from parent (needed for upcomingEventsThisMonth)
    private function getEventTypeIcon($typeName): string
    {
        $iconMap = [
            'cultural' => 's-musical-note',
            'competition' => 's-trophy',
            'academic' => 's-academic-cap',
            'workshop' => 's-academic-cap',
            'meeting' => 's-building-office',
            'social' => 's-sparkles',
        ];

        $lowerType = strtolower($typeName);
        foreach ($iconMap as $key => $icon) {
            if (str_contains($lowerType, $key)) {
                return $icon;
            }
        }

        return 's-calendar-days';
    }
}
