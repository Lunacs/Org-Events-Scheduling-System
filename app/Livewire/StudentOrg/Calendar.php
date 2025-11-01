<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\Event_Schedule;
use Mary\Traits\Toast;

class Calendar extends Component
{
    use Toast;

    #[Title('Event Calendar - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

    public $viewMode = 'dayGridMonth';
    public $selectedEvent = null;
    public $showModal = false;

    #[Url(except: '')]
    public $eventTypeFilter = '';

    #[Url(except: '')]
    public $venueFilter = '';

    public function viewEvent($eventId)
    {
        $this->showModal = false;
        $this->selectedEvent = null;

        $this->selectedEvent = \App\Models\Event::select(['event_id', 'ticket_id', 'event__type_id', 'notes'])
            ->with([
                'ticket' => fn($q) => $q->select(['ticket_id', 'ticket_number', 'title', 'description', 'venue_requested', 'user_id', 'status'])
                    ->with([
                        'user' => fn($q) => $q->select(['user_id', 'org_id'])
                            ->with('studentOrganization:org_id,org_name')
                    ]),
                'eventSchedules:schedule_id,event_id,start_date,end_date,start_time,end_time',
                'eventType:event_type_id,type_name'
            ])
            ->find($eventId);

        if (!$this->selectedEvent) {
            $this->dispatch('toast-error', message: 'Event not found');
            return;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedEvent = null;
    }

    public function resetFilters()
    {
        $this->eventTypeFilter = '';
        $this->venueFilter = '';
        $this->dispatch('student-calendar-updated');
    }

    public function updatedEventTypeFilter()
    {
        $this->dispatch('student-calendar-updated');
    }

    public function updatedVenueFilter()
    {
        $this->dispatch('student-calendar-updated');
    }

    #[Computed]
    public function eventsForCalendar()
    {
        $eventSchedules = Event_Schedule::select(['schedule_id', 'event_id', 'start_date', 'end_date', 'start_time', 'end_time', 'venue', 'status'])
            ->with([
                'event' => fn($q) => $q->select(['event_id', 'ticket_id', 'event__type_id'])
                    ->with([
                        'ticket' => fn($q) => $q->select(['ticket_id', 'title', 'description', 'venue_requested', 'user_id', 'status', 'ticket_number'])
                            ->with([
                                'user' => fn($q) => $q->select(['user_id', 'org_id'])
                                    ->with('studentOrganization:org_id,org_name')
                            ]),
                        'eventType:event_type_id,type_name'
                    ])
            ])
            ->where('status', 'approved')
            ->whereHas('event.ticket', fn($query) => $query->where('status', 'approved'))
            ->when($this->eventTypeFilter, fn($query) => $query->whereHas('event', fn($q) => $q->where('event__type_id', $this->eventTypeFilter)))
            ->when($this->venueFilter, fn($query) => $query->where('venue', $this->venueFilter))
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
                ]
            ];
        })->toArray();
    }

    private function getEventColor($event)
    {
        $colors = [
            '#10b981', '#3b82f6', '#8b5cf6', '#f59e0b',
            '#ef4444', '#06b6d4', '#84cc16', '#f97316',
        ];
        $orgId = $event->ticket->user->org_id ?? 0;
        return $colors[$orgId % count($colors)];
    }

    #[Computed]
    public function eventTypes()
    {
        return \App\Models\Event_Type::select('event_type_id', 'type_name')
            ->orderBy('type_name')
            ->get();
    }

    #[Computed]
    public function venues()
    {
        return collect([
            ['id' => '', 'name' => 'All Venues'],
            ['id' => 'auditorium', 'name' => 'University Auditorium'],
            ['id' => 'student_center', 'name' => 'Student Center'],
            ['id' => 'gymnasium', 'name' => 'Gymnasium'],
            ['id' => 'library', 'name' => 'Library Hall'],
        ]);
    }

    #[Computed]
    public function upcomingEvents()
    {
        return Event_Schedule::select(['schedule_id', 'event_id', 'start_date', 'end_date', 'start_time', 'end_time', 'venue', 'status'])
            ->with([
                'event' => fn($q) => $q->select(['event_id', 'ticket_id', 'event__type_id'])
                    ->with([
                        'ticket' => fn($q) => $q->select(['ticket_id', 'title', 'description', 'venue_requested', 'user_id', 'status', 'ticket_number'])
                            ->with([
                                'user' => fn($q) => $q->select(['user_id', 'org_id'])
                                    ->with('studentOrganization:org_id,org_name')
                            ]),
                        'eventType:event_type_id,type_name'
                    ])
            ])
            ->where('status', 'approved')
            ->whereHas('event.ticket', fn($query) => $query->where('status', 'approved'))
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.student-org.calendar', [
            'events' => $this->eventsForCalendar,
            'eventTypes' => $this->eventTypes,
            'venues' => $this->venues,
            'upcomingEvents' => $this->upcomingEvents
        ]);
    }
}
