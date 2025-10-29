<?php

namespace App\Livewire\Osa;

use App\Models\Event;
use App\Models\Event_Schedule;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Mary\Traits\Toast;

class EventCalendar extends Component
{
    use Toast;

    #[Title('Event Calendar - OSA Admin')]
    #[Layout('components.layouts.app')]

    #[Url(except: 'dayGridMonth')]
    public $viewMode = 'dayGridMonth';

    #[Url(as: 'date', except: '')]
    public $dateParam = '';

    public $currentDate;

    // public $selectedEvent = null;

    public $showModal = false;
    public bool $filterDrawerOpen = false;

    public ?Event $selectedEvent = null;

    #[Url(except: 'approved')]
    public $statusFilter = 'approved';

    #[Url(except: '')]
    public $organizationFilter = '';

    #[Url(except: '')]
    public $eventTypeFilter = '';

    public function mount()
    {
        // Parse date parameter from URL, fallback to current date if empty or invalid
        if ($this->dateParam) {
            try {
                $this->currentDate = Carbon::parse($this->dateParam);
            } catch (\Exception $e) {
                $this->currentDate = Carbon::now();
            }
        } else {
            $this->currentDate = Carbon::now();
        }
    }

    public function clearFilters()
    {
        $this->statusFilter = 'approved';
        $this->organizationFilter = '';
        $this->eventTypeFilter = '';
        $this->filterDrawerOpen = false; // Close drawer when clearing filters
        $this->dispatch('calendar-refetch');
    }

    public function previousPeriod()
    {
        $this->dispatch('calendar-prev');
    }

    public function nextPeriod()
    {
        $this->dispatch('calendar-next');
    }

    public function today()
    {
        $this->dispatch('calendar-today');
    }

    public function viewEvent($eventId)
    {
        // Reset modal state first
        $this->showModal = false;
        $this->selectedEvent = null;

        $this->selectedEvent = Event::select(['event_id', 'ticket_id', 'event__type_id', 'notes'])
            ->with([
                'ticket' => fn($q) => $q->select(['ticket_id', 'ticket_number', 'title', 'description', 'venue_requested', 'user_id', 'status'])
                    ->with([
                        'user' => fn($q) => $q->select(['user_id', 'org_id'])
                            ->with('studentOrganization:org_id,org_name'),
                    ]),
                'eventSchedules:schedule_id,event_id,start_date,end_date,start_time,end_time',
                'eventType:event_type_id,type_name',
            ])
            ->find($eventId);

        if (! $this->selectedEvent) {
            $this->dispatch('toast-error', message: 'Event not found');

            return;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedEvent = null;
        // Don't dispatch calendar-refetch when closing modal
        // This prevents the calendar from resetting to current month
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->dispatch('calendar-change-view', view: $mode);
    }

    public function updateDateParam($date)
    {
        try {
            $parsedDate = Carbon::parse($date);
            $this->dateParam = $parsedDate->format('Y-m-d');
        } catch (\Exception $e) {
            $this->dateParam = Carbon::now()->format('Y-m-d');
        }
    }

    // public function clearFilters()
    // {
    //     $this->statusFilter = 'approved';
    //     $this->organizationFilter = '';
    //     $this->eventTypeFilter = '';
    //     $this->dispatch('calendar-refetch');

    //     // Show toast notification for clearing filters
    //     $this->success('All filters cleared: Showing all approved events', timeout: 5000);
    // }

    public function getUpdatedEvents()
    {
        return $this->eventsForCalendar;
    }

    public function updated($property)
    {
        // This method is called whenever any property is updated
        if (in_array($property, ['statusFilter', 'organizationFilter', 'eventTypeFilter'])) {
            $this->dispatch('calendar-refetch');

            // Show toast notification for filter changes
            $this->showFilterToast($property);
        }
    }

    private function showFilterToast($property)
    {
        $message = '';

        switch ($property) {
            case 'statusFilter':
                $statusName = $this->statusFilter === 'approved' ? 'Approved' : 'Rescheduled';
                $message = "Filter applied: Showing {$statusName} events";
                break;

            case 'organizationFilter':
                if ($this->organizationFilter) {
                    $org = $this->organizations->firstWhere('org_id', $this->organizationFilter);
                    $orgName = $org ? $org->org_name : 'Selected Organization';
                    $message = "Filter applied: Showing events from {$orgName}";
                } else {
                    $message = 'Filter cleared: Showing events from all organizations';
                }
                break;

            case 'eventTypeFilter':
                if ($this->eventTypeFilter) {
                    $eventType = $this->eventTypes->firstWhere('event_type_id', $this->eventTypeFilter);
                    $typeName = $eventType ? $eventType->type_name : 'Selected Event Type';
                    $message = "Filter applied: Showing {$typeName} events";
                } else {
                    $message = 'Filter cleared: Showing all event types';
                }
                break;
        }

        if ($message) {
            $this->success($message, timeout: 5000);
        }
    }

    #[Computed]
    public function eventsForCalendar()
    {
        // Fetch only approved event schedules (from Event_Schedules table)
        $eventSchedules = Event_Schedule::select(['schedule_id', 'event_id', 'start_date', 'end_date', 'start_time', 'end_time', 'venue', 'status'])
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
            // Always show only approved event schedules
            ->where('status', 'approved')
            // Filter by ticket status (approved or rescheduled)
            ->whereHas('event.ticket', fn($query) => $query->where('status', $this->statusFilter))
            // Apply organization filter if set
            ->when($this->organizationFilter, fn($query) => $query->whereHas('event.ticket.user', fn($q) => $q->where('org_id', $this->organizationFilter)))
            // Apply event type filter if set
            ->when($this->eventTypeFilter, fn($query) => $query->whereHas('event', fn($q) => $q->where('event__type_id', $this->eventTypeFilter)))
            ->get();

        return $eventSchedules->map(function ($schedule) {
            $event = $schedule->event;
            $startDate = $schedule->start_date->format('Y-m-d');
            $endDate = $schedule->end_date ? $schedule->end_date->format('Y-m-d') : $startDate;
            $startTime = $schedule->start_time ?? '09:00';
            $endTime = $schedule->end_time ?? '17:00';

            return [
                'id' => $event->event_id,
                'title' => $event->ticket->title,
                'start' => $startDate . 'T' . $startTime,
                'end' => $endDate . 'T' . $endTime,
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
                ],
            ];
        })->toArray();
    }

    private function getEventColor($event)
    {
        // Color coding based on organization or event type
        $colors = [
            '#10b981',
            '#3b82f6',
            '#8b5cf6',
            '#f59e0b',
            '#ef4444',
            '#06b6d4',
            '#84cc16',
            '#f97316',
        ];

        // Use event type ID for consistent color assignment
        $eventTypeId = $event->event__type_id ?? 0;

        // Fallback to organization ID if no event type
        if ($eventTypeId === 0) {
            $orgId = $event->ticket->user->org_id ?? 0;

            return $colors[$orgId % count($colors)];
        }

        return $colors[$eventTypeId % count($colors)];
    }

    public function render()
    {
        return view('livewire.osa.event-calendar', [
            'events' => $this->eventsForCalendar,
            'organizations' => $this->organizations,
            'eventTypes' => $this->eventTypes,
        ]);
    }

    #[Computed]
    public function organizations()
    {
        return \App\Models\Student_Organization::select('org_id', 'org_name')
            ->where('status', 'active')
            ->orderBy('org_name')
            ->get();
    }

    #[Computed]
    public function eventTypes()
    {
        return \App\Models\Event_Type::select('event_type_id', 'type_name')
            ->orderBy('type_name')
            ->get();
    }
}
