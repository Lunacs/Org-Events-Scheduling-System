<?php

namespace App\Livewire\Components;

use App\Models\Event;
use App\Models\Event_Schedule;
use App\Models\Event_Type;
use App\Models\Student_Organization;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Mary\Traits\Toast;

#[Lazy]
class EventCalendar extends Component
{
    use Toast;

    private const CALENDAR_CACHE_TTL_SECONDS = 600;

    #[Url(except: 'dayGridMonth')]
    public $viewMode = 'dayGridMonth';

    #[Url(as: 'date', except: '')]
    public $dateParam = '';

    public $currentDate;

    public $showModal = false;

    public bool $filterDrawerOpen = false;

    public ?Event $selectedEvent = null;

    #[Url(except: 'all')]
    public $statusFilter = 'all';

    #[Url(except: '')]
    public $organizationFilter = '';

    #[Url(except: '')]
    public $eventTypeFilter = '';

    #[Url(except: false)]
    public $showPastEvents = false;

    // Server-side cache for event details to avoid repeat DB queries
    protected array $eventDetailsCache = [];

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

    public function placeholder()
    {
        return view('livewire.osa.placeholders.event-calendar');
    }

    public function getEventDetails($eventId)
    {
        // Serve from cache if available
        if (isset($this->eventDetailsCache[$eventId])) {
            return $this->eventDetailsCache[$eventId];
        }

        $event = Event::select(['event_id', 'ticket_id', 'event__type_id', 'notes'])
            ->with([
                'ticket' => fn ($q) => $q->select(['ticket_id', 'ticket_number', 'title', 'description', 'venue_requested', 'venue_other', 'user_id', 'status'])
                    ->with([
                        'user' => fn ($q) => $q->withTrashed()->select(['user_id', 'org_id'])
                            ->with('studentOrganization:org_id,org_name,logo'),
                    ]),
                'eventSchedules:schedule_id,event_id,start_date,end_date,start_time,end_time,venue',
                'eventType:event_type_id,type_name',
            ])
            ->find($eventId);

        if (! $event) {
            return null;
        }

        $org = $event->ticket->user->studentOrganization ?? null;
        $details = [
            'id' => $event->event_id,
            'title' => $event->ticket->title ?? 'Untitled Event',
            'organization' => $org->org_name ?? 'No Organization',
            'organizationLogo' => $org ? $org->logo_url : asset('images/default-org-logo.svg'),
            'status' => $event->ticket->status ?? 'approved',
            'ticketNumber' => $event->ticket->ticket_number ?? null,
            'type' => $event->eventType?->type_name ?? 'N/A',
            'venue' => $event->ticket->venue_display_name ?? 'TBD',
            'description' => $event->ticket->description ?? null,
            'schedules' => $event->eventSchedules->map(function ($s) {
                // Use getRawOriginal to get exact database time values
                $rawStartTime = $s->getRawOriginal('start_time');
                $rawEndTime = $s->getRawOriginal('end_time');

                return [
                    'start_date' => optional($s->start_date)->format('Y-m-d'),
                    'end_date' => optional($s->end_date)->format('Y-m-d'),
                    'start_time' => $rawStartTime ?: null,
                    'end_time' => $rawEndTime ?: null,
                    'venue' => $s->venue,
                ];
            })->values()->all(),
        ];

        // Cache and return
        $this->eventDetailsCache[$eventId] = $details;

        return $details;
    }

    public function invalidateEventCache($eventId = null): void
    {
        if ($eventId === null) {
            $this->eventDetailsCache = [];

            return;
        }
        unset($this->eventDetailsCache[$eventId]);
    }

    public function clearFilters()
    {
        $this->statusFilter = 'all';
        $this->organizationFilter = '';
        $this->eventTypeFilter = '';
        $this->showPastEvents = false;
        $this->filterDrawerOpen = false; // Close drawer when clearing filters

        // Clear computed property caches to force recomputation
        unset($this->uniqueEventsCount, $this->upcomingEventsThisMonth, $this->eventsForCalendar);

        $this->dispatch('calendar-refetch');
    }

    public function togglePastEvents()
    {
        $this->showPastEvents = ! $this->showPastEvents;
        $this->dispatch('calendar-refetch');
    }

    #[Renderless]
    public function previousPeriod()
    {
        $this->dispatch('calendar-prev');
    }

    #[Renderless]
    public function nextPeriod()
    {
        $this->dispatch('calendar-next');
    }

    #[Renderless]
    public function today()
    {
        $this->dispatch('calendar-today');
    }

    public function viewEvent($eventId)
    {
        Log::info('ViewEvent called with ID: '.$eventId);

        // Reset modal state first
        $this->showModal = false;
        $this->selectedEvent = null;

        $this->selectedEvent = Event::select(['event_id', 'ticket_id', 'event__type_id', 'notes'])
            ->with([
                'ticket' => fn ($q) => $q->select(['ticket_id', 'ticket_number', 'title', 'description', 'venue_requested', 'user_id', 'status'])
                    ->with([
                        'user' => fn ($q) => $q->withTrashed()->select(['user_id', 'org_id'])
                            ->with('studentOrganization:org_id,org_name,logo'),
                    ]),
                'eventSchedules:schedule_id,event_id,start_date,end_date,start_time,end_time',
                'eventType:event_type_id,type_name',
            ])
            ->find($eventId);

        Log::info('Selected Event: '.($this->selectedEvent ? 'Found' : 'Not Found'));

        if (! $this->selectedEvent) {
            Log::error('Event not found with ID: '.$eventId);
            $this->dispatch('toast-error', message: 'Event not found');

            return;
        }

        Log::info('Event found, opening modal...');

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

    public function getUpdatedEvents()
    {
        return $this->eventsForCalendar;
    }

    public function setFiltersAndGetEvents($status = 'all', $organization = '', $eventType = '')
    {
        $this->statusFilter = $status ?? 'all';
        $this->organizationFilter = $organization ?? '';
        $this->eventTypeFilter = $eventType ?? '';

        return $this->eventsForCalendar;
    }

    public function updated($property)
    {
        // This method is called whenever any property is updated
        if (in_array($property, ['statusFilter', 'organizationFilter', 'eventTypeFilter'])) {
            $this->dispatch('calendar-refetch');
        }
    }

    #[Computed]
    public function eventsForCalendar()
    {
        return Cache::remember(
            $this->calendarFiltersCacheKey('events'),
            self::CALENDAR_CACHE_TTL_SECONDS,
            fn () => $this->buildCalendarEventsFromSchedules($this->loadFilteredEventSchedules()),
        );
    }

    protected function calendarFiltersCacheKey(string $suffix): string
    {
        return sprintf(
            'event_calendar:%s:%s:%s:%s:%s:%s',
            $suffix,
            $this->statusFilter ?: 'all',
            $this->organizationFilter ?: 'none',
            $this->eventTypeFilter ?: 'none',
            $this->showPastEvents ? 'past' : 'current_year',
            Carbon::now()->format('Y'),
        );
    }

    protected function baseCalendarSchedulesQuery(): Builder
    {
        return Event_Schedule::query()
            ->select(['schedule_id', 'event_id', 'start_date', 'end_date', 'start_time', 'end_time', 'venue', 'status'])
            ->with([
                'event' => fn ($q) => $q->select(['event_id', 'ticket_id', 'event__type_id'])
                    ->with([
                        'ticket' => fn ($q) => $q->select(['ticket_id', 'title', 'description', 'venue_requested', 'user_id', 'status', 'ticket_number'])
                            ->with([
                                'user' => fn ($q) => $q->withTrashed()
                                    ->select(['user_id', 'org_id'])
                                    ->with('studentOrganization:org_id,org_name,logo'),
                            ]),
                        'eventType:event_type_id,type_name',
                    ]),
            ])
            ->where('status', 'approved')
            ->whereHas('event.ticket', function ($query) {
                $query->whereHas('user', fn ($q) => $q->withTrashed());

                if ($this->statusFilter && $this->statusFilter !== 'all') {
                    $query->where('status', $this->statusFilter);
                } else {
                    $query->whereIn('status', ['approved', 'rescheduled', 'completed']);
                }
            })
            ->when($this->organizationFilter, fn ($query) => $query->whereHas('event.ticket.user', fn ($q) => $q->withTrashed()->where('org_id', $this->organizationFilter)))
            ->when($this->eventTypeFilter, fn ($query) => $query->whereHas('event', fn ($q) => $q->where('event__type_id', $this->eventTypeFilter)))
            ->when(! $this->showPastEvents, fn ($query) => $query->where('start_date', '>=', Carbon::now()->startOfYear()));
    }

    protected function loadFilteredEventSchedules(): Collection
    {
        return $this->baseCalendarSchedulesQuery()->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildCalendarEventsFromSchedules(Collection $eventSchedules): array
    {
        $allEvents = [];

        foreach ($eventSchedules as $schedule) {
            $event = $schedule->event;

            // Get raw time values from database without any conversion
            $rawStartTime = $schedule->getRawOriginal('start_time');
            $rawEndTime = $schedule->getRawOriginal('end_time');

            // Ensure we have valid time strings in HH:MM:SS format
            $startTime = $rawStartTime ?: '09:00:00';
            $endTime = $rawEndTime ?: '17:00:00';

            // Trim to exactly HH:MM:SS (8 characters) to remove microseconds if present
            if (strlen($startTime) > 8) {
                $startTime = substr($startTime, 0, 8);
            }
            if (strlen($endTime) > 8) {
                $endTime = substr($endTime, 0, 8);
            }

            // Format dates properly
            $startDate = $schedule->start_date->format('Y-m-d');
            $endDate = $schedule->end_date ? $schedule->end_date->format('Y-m-d') : $startDate;

            // ALTERNATIVE FIX: Use FullCalendar's built-in recurring events
            // This keeps ONE event entry instead of splitting into multiple entries
            //
            // Database stores times in HH:MM:SS format (no timezone info)
            // Times are assumed to be in Asia/Manila timezone (UTC+8)
            //
            // For multi-day events with same times each day, use recurring events:
            // - startTime/endTime: Time portion only (HH:MM:SS format)
            // - startRecur: First date of recurrence
            // - endRecur: Last date of recurrence (exclusive, so add 1 day)
            // - daysOfWeek: Array of day numbers (0=Sun, 1=Mon, ..., 6=Sat) - [0,1,2,3,4,5,6] for all days
            //
            // This approach:
            // ✅ Keeps single event entry in database representation
            // ✅ Displays correctly in Week/Day views with proper heights
            // ✅ No need to split into multiple event entries
            // ✅ FullCalendar handles the recurrence automatically

            if ($startDate === $endDate) {
                // Single-day event: Use standard start/end format
                $startISO = $startDate.'T'.$startTime.'+08:00';
                $endISO = $endDate.'T'.$endTime.'+08:00';

                $allEvents[] = [
                    'id' => $event->event_id,
                    'title' => $event->ticket->title,
                    'start' => $startISO,
                    'end' => $endISO,
                    'allDay' => false,
                    'display' => 'block', // Show as solid bar instead of dot
                    'backgroundColor' => $this->getEventColor($event),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'organization' => $event->ticket->user->studentOrganization->org_name ?? 'No Organization',
                        'eventType' => $event->eventType?->type_name ?? 'N/A',
                        'venue' => $schedule->venue ?? $event->ticket->venue_requested ?? 'TBD',
                        'description' => $event->ticket->description,
                        'ticketNumber' => $event->ticket->ticket_number,
                        'status' => $event->ticket->status ?? 'N/A',
                        'rawStartTime' => $startTime,
                        'rawEndTime' => $endTime,
                        'rawStartDate' => $startDate,
                        'rawEndDate' => $endDate,
                    ],
                ];
            } else {
                // Multi-day event: Provide BOTH formats for different views
                //
                // 1. Spanning event (for month view): Single bar spanning all days
                //    Format: start="2025-11-03T08:00:00+08:00" end="2025-11-04T18:00:00+08:00"
                //    This creates a single spanning bar in month view
                //
                // 2. Recurring event (for week/day views): Separate blocks per day with proper heights
                //    Format: startTime, endTime, startRecur, endRecur, daysOfWeek
                //    This creates proper height-based blocks in week/day views

                // Calculate days of week from start_date to end_date
                $startCarbon = Carbon::parse($startDate);
                $endCarbon = Carbon::parse($endDate);
                $daysOfWeek = [];

                $current = $startCarbon->copy();
                while ($current->lte($endCarbon)) {
                    // FullCalendar uses 0=Sunday, 1=Monday, ..., 6=Saturday
                    // Carbon's dayOfWeek uses 0=Sunday, 1=Monday, ..., 6=Saturday
                    $dayOfWeek = $current->dayOfWeek;
                    if (! in_array($dayOfWeek, $daysOfWeek)) {
                        $daysOfWeek[] = $dayOfWeek;
                    }
                    $current->addDay();
                }

                // If spanning many days, include all weekdays for simplicity
                // Otherwise, use calculated days
                if (count($daysOfWeek) >= 5) {
                    $daysOfWeek = [0, 1, 2, 3, 4, 5, 6]; // All days
                }

                // endRecur is exclusive, so we need to add 1 day to the end date
                $endRecurDate = Carbon::parse($endDate)->addDay()->format('Y-m-d');

                $commonProps = [
                    'title' => $event->ticket->title,
                    'backgroundColor' => $this->getEventColor($event), // 50% opacity
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'organization' => $event->ticket->user->studentOrganization->org_name ?? 'No Organization',
                        'eventType' => $event->eventType?->type_name ?? 'N/A',
                        'venue' => $schedule->venue ?? $event->ticket->venue_requested ?? 'TBD',
                        'description' => $event->ticket->description,
                        'ticketNumber' => $event->ticket->ticket_number,
                        'status' => $event->ticket->status ?? 'approved',
                        'rawStartTime' => $startTime,
                        'rawEndTime' => $endTime,
                        'rawStartDate' => $startDate,
                        'rawEndDate' => $endDate,
                    ],
                ];

                // 1. Spanning event for month view (single bar across days)
                $allEvents[] = array_merge($commonProps, [
                    'id' => $event->event_id.'_span',
                    'groupId' => $event->event_id,
                    'start' => $startDate.'T'.$startTime.'+08:00',
                    'end' => $endDate.'T'.$endTime.'+08:00',
                    'allDay' => false,
                    'display' => 'block', // Spanning bar in month view
                    'extendedProps' => array_merge($commonProps['extendedProps'], [
                        'forMonthView' => true, // Mark as month view event
                    ]),
                ]);

                // 2. Recurring event for week/day views (proper height blocks)
                $allEvents[] = array_merge($commonProps, [
                    'id' => $event->event_id.'_recur',
                    'groupId' => $event->event_id,
                    'startTime' => $startTime, // Time portion only (HH:MM:SS)
                    'endTime' => $endTime,     // Time portion only (HH:MM:SS)
                    'startRecur' => $startDate, // First date of recurrence
                    'endRecur' => $endRecurDate, // Last date (exclusive, so +1 day)
                    'daysOfWeek' => $daysOfWeek, // Array of day numbers
                    'allDay' => false,
                    'display' => 'block',
                    'extendedProps' => array_merge($commonProps['extendedProps'], [
                        'forTimeView' => true, // Mark as time view event
                    ]),
                ]);
            }
        }

        return $allEvents;
    }

    protected function getEventColor($event): string
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

    #[Computed]
    public function organizations()
    {
        return once(function () {
            return Student_Organization::select('org_id', 'org_name')
                ->where('status', 'active')
                ->orderBy('org_name')
                ->get();
        });
    }

    #[Computed]
    public function eventTypes()
    {
        return once(function () {
            return Event_Type::select('event_type_id', 'type_name')
                ->orderBy('type_name')
                ->get();
        });
    }

    #[Computed]
    public function uniqueEventsCount()
    {
        return Cache::remember(
            $this->calendarFiltersCacheKey('unique_count'),
            self::CALENDAR_CACHE_TTL_SECONDS,
            fn () => (int) $this->baseCalendarSchedulesQuery()
                ->reorder()
                ->withoutEagerLoads()
                ->select(DB::raw('COUNT(DISTINCT event_id) as count'))
                ->value('count'),
        );
    }

    #[Computed]
    public function upcomingEventsThisMonth()
    {
        $today = Carbon::today();
        $endOfMonth = $this->currentDate->copy()->endOfMonth();

        // If viewing a past month, return empty (no upcoming events)
        if ($endOfMonth->lt($today)) {
            return [];
        }

        // Start from today (or start of month if viewing a future month)
        $startDate = $this->currentDate->copy()->startOfMonth()->gt($today)
            ? $this->currentDate->copy()->startOfMonth()
            : $today;

        $eventSchedules = Event_Schedule::select(['schedule_id', 'event_id', 'start_date', 'end_date', 'start_time', 'end_time', 'venue', 'status'])
            ->with([
                'event' => fn ($q) => $q->select(['event_id', 'ticket_id', 'event__type_id'])
                    ->with([
                        'ticket' => fn ($q) => $q->select(['ticket_id', 'title', 'description', 'venue_requested', 'user_id', 'status', 'ticket_number'])
                            ->with([
                                'user' => fn ($q) => $q->withTrashed()->select(['user_id', 'org_id'])
                                    ->with('studentOrganization:org_id,org_name,logo'),
                            ]),
                        'eventType:event_type_id,type_name',
                    ]),
            ])
            ->where('status', 'approved')
            ->whereHas('event.ticket', fn ($query) => $query->whereIn('status', ['approved', 'rescheduled']))
            ->whereBetween('start_date', [$startDate, $endOfMonth])
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        return $eventSchedules->map(function ($schedule) {
            $event = $schedule->event;
            $rawStartTime = $schedule->getRawOriginal('start_time');
            $rawEndTime = $schedule->getRawOriginal('end_time');

            // Format date
            $startDate = Carbon::parse($schedule->start_date);
            $endDate = $schedule->end_date ? Carbon::parse($schedule->end_date) : $startDate;

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
                $dateDisplay = $startDate->format('M d').' - '.$endDate->format('M d, Y');
            }

            // Get event color using the same method as calendar
            $hexColor = $this->getEventColor($event);
            $colorName = $this->hexToTailwindColor($hexColor);

            $org = $event->ticket->user->studentOrganization ?? null;

            return [
                'title' => $event->ticket->title ?? 'Untitled Event',
                'description' => $event->ticket->description ?? null,
                'organization' => $org->org_name ?? 'No Organization',
                'organizationLogo' => $org ? $org->logo_url : asset('images/default-org-logo.svg'),
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

    private function getEventTypeIcon($typeName): string
    {
        $iconMap = [
            'General Assemblies and Similar Activities' => 's-user-group',
            'Organization Shirts / IGP' => 's-shopping-bag',
            'Off-Campus Activities' => 's-map-pin',
            'Online Activities' => 's-video-camera',
            'Training, Rehearsals, Practices' => 's-academic-cap',
        ];

        $lowerType = strtolower($typeName);
        foreach ($iconMap as $key => $icon) {
            if (str_contains($lowerType, strtolower($key))) {
                return $icon;
            }
        }

        return 's-calendar';
    }

    /**
     * Convert hex color from getEventColor() to Tailwind color name
     * Maps the calendar's hex colors to Tailwind utility classes
     */
    private function hexToTailwindColor($hexColor): string
    {
        $colorMap = [
            '#10b981' => 'green',    // success/green
            '#3b82f6' => 'blue',     // info/blue
            '#8b5cf6' => 'purple',   // secondary/purple
            '#f59e0b' => 'yellow',   // warning/yellow
            '#ef4444' => 'red',      // error/red
            '#06b6d4' => 'cyan',     // cyan
            '#84cc16' => 'lime',     // lime
            '#f97316' => 'orange',   // orange
        ];

        return $colorMap[$hexColor] ?? 'blue';
    }

    public function render()
    {
        return view('livewire.components.event-calendar', [
            'events' => $this->eventsForCalendar,
            'uniqueEventsCount' => $this->uniqueEventsCount,
            'organizations' => $this->organizations,
            'eventTypes' => $this->eventTypes,
        ]);
    }
}
