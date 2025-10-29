<div>
    {{-- Header
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Event Calendar</h1>
                    <p class="text-base-content/70 mt-1">View all approved and scheduled events</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-mary-badge value="{{ count($events) }} Events" class="badge-primary" />
                </div>
            </div>
        </div>
    </div> --}}

    {{-- Header --}}
    <div class="mb-6">
        <div
            class="bg-gradient-to-r from-primary/10 via-primary/5 to-base-100 rounded-box shadow-sm border border-primary/20 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                {{-- Title Section --}}
                <div class="flex items-center gap-4">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-lg w-14 h-14">
                            <x-mary-icon name="o-calendar-days" class="w-8 h-8" />
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-base-content flex items-center gap-2">
                            Event Calendar Management
                            <x-mary-badge value="OSA" class="badge-primary badge-sm" />
                        </h1>
                        <p class="text-sm text-base-content/70 mt-1">
                            Monitor and manage all university event schedules
                        </p>
                    </div>
                </div>

                {{-- Action Section --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Event Count with Status Breakdown --}}
                    <div class="stats stats-horizontal shadow-sm bg-base-200/50 border border-base-300">
                        <div class="stat py-3 px-4">
                            <div class="stat-title text-xs">Total Events</div>
                            <div class="stat-value text-2xl text-primary">{{ count($events) }}</div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-primary btn-sm gap-2">
                            <x-mary-icon name="o-ellipsis-vertical" class="w-4 h-4" />
                            Actions
                        </label>
                        <ul tabindex="0"
                            class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 mt-2">
                            <li>
                                <a wire:navigate href="/admin/event-req">
                                    <x-mary-icon name="o-document-text" class="w-4 h-4" />
                                    Manage Requests
                                </a>
                            </li>
                            <li>
                                <a wire:navigate href="/admin/reports">
                                    <x-mary-icon name="o-chart-bar" class="w-4 h-4" />
                                    View Reports
                                </a>
                            </li>
                            <li>
                                <a wire:navigate href="/admin/archive">
                                    <x-mary-icon name="o-archive-box" class="w-4 h-4" />
                                    Event Archive
                                </a>
                            </li>
                            <div class="divider my-0"></div>
                            <li>
                                <a onclick="window.print()">
                                    <x-mary-icon name="o-printer" class="w-4 h-4" />
                                    Print Calendar
                                </a>
                            </li>
                            <li>
                                <a wire:click="exportCalendar">
                                    <x-mary-icon name="o-arrow-down-tray" class="w-4 h-4" />
                                    Export Events
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- Refresh Button --}}
                    <x-mary-button wire:click="$refresh" class="btn-ghost btn-sm btn-circle" tooltip="Refresh Calendar"
                        tooltip-bottom>
                        <x-mary-icon name="o-arrow-path" class="w-5 h-5" />
                    </x-mary-button>
                </div>
            </div>

            {{-- Quick Stats Bar (Optional - can be toggled) --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 pt-4 border-t border-primary/10">
                @php
                    $approvedCount = collect($events)
                        ->filter(
                            fn($e) => isset($e['extendedProps']['status']) &&
                                $e['extendedProps']['status'] === 'approved',
                        )
                        ->count();
                    $rescheduledCount = collect($events)
                        ->filter(
                            fn($e) => isset($e['extendedProps']['status']) &&
                                $e['extendedProps']['status'] === 'rescheduled',
                        )
                        ->count();
                    $thisMonthCount = collect($events)
                        ->filter(function ($e) {
                            return isset($e['start']) && \Carbon\Carbon::parse($e['start'])->isCurrentMonth();
                        })
                        ->count();
                    $todayCount = collect($events)
                        ->filter(function ($e) {
                            return isset($e['start']) && \Carbon\Carbon::parse($e['start'])->isToday();
                        })
                        ->count();
                @endphp

                <div class="flex items-center gap-2 p-2 rounded-lg bg-success/10">
                    <x-mary-icon name="o-check-circle" class="w-5 h-5 text-success" />
                    <div>
                        <div class="text-xs text-base-content/70">Approved</div>
                        <div class="text-lg font-bold text-success">{{ $approvedCount }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 p-2 rounded-lg bg-warning/10">
                    <x-mary-icon name="o-arrow-path" class="w-5 h-5 text-warning" />
                    <div>
                        <div class="text-xs text-base-content/70">Rescheduled</div>
                        <div class="text-lg font-bold text-warning">{{ $rescheduledCount }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 p-2 rounded-lg bg-info/10">
                    <x-mary-icon name="o-calendar" class="w-5 h-5 text-info" />
                    <div>
                        <div class="text-xs text-base-content/70">This Month</div>
                        <div class="text-lg font-bold text-info">{{ $thisMonthCount }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 p-2 rounded-lg bg-primary/10">
                    <x-mary-icon name="o-clock" class="w-5 h-5 text-primary" />
                    <div>
                        <div class="text-xs text-base-content/70">Today</div>
                        <div class="text-lg font-bold text-primary">{{ $todayCount }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Calendar Controls --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            {{-- Navigation --}}
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <x-mary-button onclick="window.fullCalendar?.prev(); updateCalendarTitle();"
                        class="btn-ghost btn-sm" icon="o-chevron-left" />
                    <h2 class="text-lg font-semibold min-w-[200px] text-center" id="calendar-title" wire:ignore>
                        Loading...
                    </h2>
                    <x-mary-button onclick="window.fullCalendar?.next(); updateCalendarTitle();"
                        class="btn-ghost btn-sm" icon="o-chevron-right" />
                </div>
                <x-mary-button onclick="window.fullCalendar?.today(); updateCalendarTitle();"
                    class="btn-outline btn-sm">
                    Today
                </x-mary-button>
            </div>

            {{-- View Mode & Filters --}}
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-4">
                {{-- View Mode Buttons --}}
                <div class="flex gap-1 lg:shrink-0" id="view-mode-buttons">
                    <x-mary-button wire:ignore onclick="changeCalendarView('dayGridMonth')" data-view="dayGridMonth"
                        class="btn-sm view-mode-btn flex-1 lg:flex-none {{ $viewMode === 'dayGridMonth' ? 'btn-primary' : 'btn-ghost' }}">
                        Month
                    </x-mary-button>
                    <x-mary-button wire:ignore onclick="changeCalendarView('timeGridWeek')" data-view="timeGridWeek"
                        class="btn-sm view-mode-btn flex-1 lg:flex-none {{ $viewMode === 'timeGridWeek' ? 'btn-primary' : 'btn-ghost' }}">
                        Week
                    </x-mary-button>
                    <x-mary-button wire:ignore onclick="changeCalendarView('timeGridDay')" data-view="timeGridDay"
                        class="btn-sm view-mode-btn flex-1 lg:flex-none {{ $viewMode === 'timeGridDay' ? 'btn-primary' : 'btn-ghost' }}">
                        Day
                    </x-mary-button>
                    <x-mary-button wire:ignore onclick="changeCalendarView('listWeek')" data-view="listWeek"
                        class="btn-sm view-mode-btn flex-1 lg:flex-none {{ $viewMode === 'listWeek' ? 'btn-primary' : 'btn-ghost' }}">
                        List
                    </x-mary-button>
                </div>

                {{-- Filter Actions --}}
                <div class="flex gap-2 lg:ml-auto">
                    {{-- Active Filters Badge --}}
                    @php
                        $activeFiltersCount = collect([$statusFilter, $organizationFilter, $eventTypeFilter])
                            ->filter()
                            ->count();
                    @endphp

                    @if ($activeFiltersCount > 0)
                        <x-mary-badge value="{{ $activeFiltersCount }} Active" class="badge-primary self-center" />
                    @endif

                    <x-mary-button icon="o-funnel" class="btn-ghost btn-sm" @click="$wire.filterDrawerOpen = true"
                        tooltip="Open Filters">
                        Filters
                    </x-mary-button>

                    @if ($activeFiltersCount > 0)
                        <x-mary-button wire:click="clearFilters" class="btn-ghost btn-sm" icon="o-x-mark"
                            tooltip="Clear All Filters" />
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Drawer --}}
    <x-mary-drawer wire:model="filterDrawerOpen" title="Filter Events" subtitle="Refine your calendar view"
        class="w-11/12 lg:w-1/3" right>

        <div class="space-y-6">
            {{-- Status Filter --}}
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Status</span>
                </label>
                <x-mary-select wire:model.live="statusFilter" placeholder="All Statuses" :options="[
                    ['id' => '', 'name' => 'All Statuses'],
                    ['id' => 'approved', 'name' => 'Approved'],
                    ['id' => 'rescheduled', 'name' => 'Rescheduled'],
                ]"
                    option-value="id" option-label="name" class="select-bordered w-full" />
            </div>

            {{-- Organization Filter --}}
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Organization</span>
                </label>
                <x-mary-select wire:model.live="organizationFilter" placeholder="All Organizations" :options="$organizations"
                    option-value="org_id" option-label="org_name" class="select-bordered w-full" />
            </div>

            {{-- Event Type Filter --}}
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Event Type</span>
                </label>
                <x-mary-select wire:model.live="eventTypeFilter" placeholder="All Event Types" :options="$eventTypes"
                    option-value="event_type_id" option-label="type_name" class="select-bordered w-full" />
            </div>

            {{-- Active Filters Summary --}}
            @php
                $activeFilters = [];
                if ($statusFilter) {
                    $activeFilters[] =
                        collect([
                            ['id' => 'approved', 'name' => 'Approved'],
                            ['id' => 'rescheduled', 'name' => 'Rescheduled'],
                        ])->firstWhere('id', $statusFilter)['name'] ?? $statusFilter;
                }
                if ($organizationFilter) {
                    $org = $organizations->firstWhere('org_id', $organizationFilter);
                    if ($org) {
                        $activeFilters[] = $org->org_name;
                    }
                }
                if ($eventTypeFilter) {
                    $type = $eventTypes->firstWhere('event_type_id', $eventTypeFilter);
                    if ($type) {
                        $activeFilters[] = $type->type_name;
                    }
                }
            @endphp

            @if (count($activeFilters) > 0)
                <div class="bg-base-200 rounded-lg p-4">
                    <h4 class="font-semibold text-sm mb-2">Active Filters</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($activeFilters as $filter)
                            <x-mary-badge value="{{ $filter }}" class="badge-primary" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-mary-button label="Clear All" wire:click="clearFilters" class="btn-ghost" />
            <x-mary-button label="Apply" @click="$wire.filterDrawerOpen = false" class="btn-primary" />
        </x-slot:actions>
    </x-mary-drawer>

    {{-- Hidden input to store events data for JavaScript access --}}
    <input type="hidden" id="calendar-events-data" value="{{ json_encode($events) }}">

    {{-- Hidden input to store initial date for JavaScript access --}}
    <input type="hidden" id="calendar-initial-date" value="{{ $currentDate->format('Y-m-d') }}">

    {{-- FullCalendar --}}
    <div class="bg-base-100 rounded-box shadow-lg overflow-hidden p-6">
        <div id="calendar" wire:ignore class="min-h-[600px] lg:min-h-[750px]"></div>
    </div>

    {{-- Event Details Modal --}}
    <x-mary-modal wire:model="showModal" title="Event Details" class="modal-lg">
        @if ($selectedEvent && $selectedEvent->ticket)
            <div class="space-y-6">
                {{-- Event Header --}}
                <div class="border-b border-base-300 pb-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold">{{ $selectedEvent->ticket->title }}</h2>
                            <p class="text-base-content/70">
                                {{ $selectedEvent->ticket->user->studentOrganization->org_name ?? 'No Organization' }}
                            </p>
                        </div>
                        @php
                            $badgeClass = match ($selectedEvent->ticket->status) {
                                'approved' => 'badge-success',
                                'rescheduled' => 'badge-warning',
                                default => 'badge-primary',
                            };
                        @endphp
                        <x-mary-badge
                            value="{{ str_replace('_', ' ', ucwords($selectedEvent->ticket->status, '_')) }}"
                            class="{{ $badgeClass }}" />
                    </div>
                </div>

                {{-- Event Information --}}
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold mb-3">Event Details</h3>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="font-medium text-base-content/70">Ticket #:</span>
                                <span>{{ $selectedEvent->ticket->ticket_number }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-base-content/70">Type:</span>
                                <span>{{ $selectedEvent->eventType?->type_name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-base-content/70">Venue:</span>
                                <span>{{ $selectedEvent->ticket->venue_requested ?? 'TBD' }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold mb-3">Schedule</h3>
                        <div class="space-y-2">
                            @foreach ($selectedEvent->eventSchedules as $schedule)
                                <div class="bg-base-200 rounded-lg p-3">
                                    <div class="flex items-center gap-2 text-sm">
                                        <x-mary-icon name="o-calendar-days" class="w-4 h-4 text-primary" />
                                        <span>{{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm mt-1">
                                        <x-mary-icon name="o-clock" class="w-4 h-4 text-primary" />
                                        <span>{{ $schedule->start_time }} - {{ $schedule->end_time }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                @if ($selectedEvent->ticket->description)
                    <div>
                        <h3 class="font-semibold mb-3">Description</h3>
                        <p class="text-sm text-base-content/80">{{ $selectedEvent->ticket->description }}</p>
                    </div>
                @endif
            </div>
        @else
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <div class="loading loading-spinner loading-lg text-primary mb-4"></div>
                    <p class="text-base-content/70">Loading event details...</p>
                    @if ($selectedEvent)
                        <p class="text-xs text-base-content/50 mt-2">Debug: Event loaded but ticket missing</p>
                    @else
                        <p class="text-xs text-base-content/50 mt-2">Debug: No event selected</p>
                    @endif
                </div>
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button wire:click="closeModal" class="btn-ghost">Close</x-mary-button>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Custom Calendar Styles --}}
    @push('styles')
        <style>
            /* Custom scrollbar styling for calendar */
            .fc-scroller {
                overflow-y: auto !important;
                overflow-x: hidden !important;
            }

            .fc-scroller::-webkit-scrollbar {
                width: 8px;
            }

            .fc-scroller::-webkit-scrollbar-track {
                background: oklch(var(--b2));
                border-radius: 4px;
            }

            .fc-scroller::-webkit-scrollbar-thumb {
                background: oklch(var(--bc) / 0.3);
                border-radius: 4px;
            }

            .fc-scroller::-webkit-scrollbar-thumb:hover {
                background: oklch(var(--bc) / 0.5);
            }

            /* Improve calendar appearance on large screens */
            @media (min-width: 1024px) {
                .fc .fc-timegrid-slot {
                    height: 3em;
                }

                .fc .fc-timegrid-slot-label {
                    font-size: 0.95em;
                }
            }

            /* Ensure list view has proper spacing */
            .fc-list-event {
                cursor: pointer;
            }

            .fc-list-event:hover {
                background: oklch(var(--b3)) !important;
            }
        </style>
    @endpush

    {{-- FullCalendar Scripts --}}
    @push('scripts')
        <script>
            // Use window object to avoid redeclaration issues
            if (!window.calendarInitialized) {
                window.calendarInitialized = false;
            }

            // Flag to prevent URL updates during initialization
            window.suppressUrlUpdate = true;

            // Helper function to update calendar title
            window.updateCalendarTitle = function() {
                const titleEl = document.getElementById('calendar-title');
                if (titleEl && window.fullCalendar) {
                    titleEl.textContent = window.fullCalendar.view.title;
                }
            };

            // Helper function to update URL parameters
            window.updateCalendarUrl = function(view, date) {
                if (!window.fullCalendar || window.suppressUrlUpdate) return;

                const params = new URLSearchParams(window.location.search);

                // Update view parameter
                if (view && view !== 'dayGridMonth') {
                    params.set('viewMode', view);
                } else {
                    params.delete('viewMode');
                }

                // Update date parameter
                if (date) {
                    // Format date in local time to avoid timezone issues
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const dateStr = `${year}-${month}-${day}`;
                    params.set('date', dateStr);
                } else {
                    params.delete('date');
                }

                // Update URL without reload
                const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, '', newUrl);
            };

            // Helper function to change calendar view and update button styles
            window.changeCalendarView = function(viewMode) {
                if (window.fullCalendar) {
                    // Change calendar view
                    window.fullCalendar.changeView(viewMode);

                    // Update title immediately
                    window.updateCalendarTitle();

                    // Update button styles instantly
                    const buttons = document.querySelectorAll('.view-mode-btn');
                    buttons.forEach(btn => {
                        const btnView = btn.closest('button').getAttribute('data-view');
                        if (btnView === viewMode) {
                            btn.classList.remove('btn-ghost');
                            btn.classList.add('btn-primary');
                        } else {
                            btn.classList.remove('btn-primary');
                            btn.classList.add('btn-ghost');
                        }
                    });

                    // URL update will be handled by datesSet event
                }
            };

            function initializeCalendar() {
                // Check if FullCalendar is loaded from npm
                if (typeof window.FullCalendar === 'undefined' || typeof window.FullCalendarPlugins === 'undefined') {
                    console.error('FullCalendar npm packages not loaded! Make sure to run: npm install && npm run dev');
                    return;
                }

                const calendarEl = document.getElementById('calendar');
                if (!calendarEl) {
                    console.error('Calendar element not found!');
                    return;
                }

                // Prevent multiple initializations
                if (window.calendarInitialized && window.fullCalendar) {
                    return;
                }

                try {
                    // Destroy existing calendar if it exists
                    if (window.fullCalendar) {
                        window.fullCalendar.destroy();
                        window.fullCalendar = null;
                    }

                    // Determine calendar height based on screen size
                    const calendarHeight = window.innerWidth >= 1024 ? 750 : 600;

                    // Create calendar using npm packages
                    const calendar = new window.FullCalendar.Calendar(calendarEl, {
                        plugins: [
                            window.FullCalendarPlugins.dayGridPlugin,
                            window.FullCalendarPlugins.timeGridPlugin,
                            window.FullCalendarPlugins.listPlugin,
                            window.FullCalendarPlugins.interactionPlugin
                        ],
                        initialView: '{{ $viewMode }}',
                        headerToolbar: false, // Use our custom controls
                        height: calendarHeight,
                        events: @json($events),

                        // Theme system - using standard for custom CSS control
                        themeSystem: 'standard',

                        // Timezone - use local timezone to avoid date shifts
                        timeZone: 'local',

                        // Enable now indicator
                        nowIndicator: true,

                        // Event interactions
                        eventClick: function(info) {
                            // Use Livewire's $wire to call the method
                            @this.viewEvent(info.event.id);
                        },

                        // Event rendering
                        eventDisplay: 'block',
                        eventTextColor: '#ffffff',

                        // Time settings
                        slotMinTime: '07:00:00',
                        slotMaxTime: '22:00:00',
                        slotDuration: '01:00:00',
                        slotLabelInterval: '01:00:00',
                        scrollTime: '08:00:00', // Initial scroll position

                        // Enable scrolling for time views
                        scrollTimeReset: true,

                        // Week settings
                        firstDay: 1, // Monday

                        // List view settings
                        listDayFormat: {
                            weekday: 'long',
                            month: 'short',
                            day: 'numeric'
                        },
                        listDaySideFormat: false,

                        // Event tooltip
                        eventDidMount: function(info) {
                            const event = info.event;
                            const props = event.extendedProps;

                            // Add tooltip
                            info.el.title =
                                `${event.title}\n${props.organization}\n${props.eventType}\n${props.venue}`;

                            // Add custom styling
                            info.el.style.borderRadius = '6px';
                            info.el.style.fontSize = '12px';
                            info.el.style.padding = '2px 4px';

                            // Apply dynamic colors for list view
                            if (info.view.type === 'listWeek') {
                                const eventColor = event.backgroundColor || event.borderColor || '#10b981';

                                // Set CSS custom property for dynamic colors (only for circle and accent line)
                                info.el.style.setProperty('--event-color', eventColor);

                                // Keep event background neutral - don't change background color
                                info.el.style.backgroundColor = '';
                                info.el.style.borderColor = '';
                                info.el.style.color = '';
                            }
                        },

                        // Update title when view changes
                        viewDidMount: function(info) {
                            const titleEl = document.getElementById('calendar-title');
                            if (titleEl) {
                                titleEl.textContent = info.view.title;
                            }
                        },

                        // Update URL when date or view changes
                        datesSet: function(arg) {
                            // Sync Livewire properties with current state (only after initialization)
                            // This will automatically update the URL through #[Url] attributes
                            if (!window.suppressUrlUpdate) {
                                // Use the calendar's current date instead of arg.start
                                // arg.start is the first visible date, not necessarily the current date
                                const currentDate = calendar.getDate();

                                // Format date in local time to avoid timezone issues
                                const year = currentDate.getFullYear();
                                const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                                const day = String(currentDate.getDate()).padStart(2, '0');
                                const dateStr = `${year}-${month}-${day}`;

                                // Get current view mode
                                const currentView = calendar.view.type;

                                // Update URL immediately with both date and viewMode
                                const params = new URLSearchParams(window.location.search);

                                // Update date parameter
                                params.set('date', dateStr);

                                // Update view parameter
                                if (currentView && currentView !== 'dayGridMonth') {
                                    params.set('viewMode', currentView);
                                } else {
                                    params.delete('viewMode');
                                }

                                // Update URL without reload
                                const newUrl = window.location.pathname + (params.toString() ? '?' + params
                                    .toString() : '');
                                window.history.replaceState({}, '', newUrl);

                                // Sync Livewire state (but URL is already updated above)
                                @this.set('dateParam', dateStr);
                                @this.set('viewMode', currentView);
                            }
                        }
                    });

                    calendar.render();

                    // Store calendar globally for Livewire access
                    window.fullCalendar = calendar;

                    // Mark as initialized
                    window.calendarInitialized = true;

                    // Update title immediately after render
                    const titleEl = document.getElementById('calendar-title');
                    if (titleEl) {
                        titleEl.textContent = calendar.view.title;
                    }

                    // Read initial date from hidden input and navigate to it
                    const initialDateInput = document.getElementById('calendar-initial-date');
                    if (initialDateInput && initialDateInput.value) {
                        // Parse date in local timezone to avoid date shifts
                        const [year, month, day] = initialDateInput.value.split('-').map(Number);
                        const initialDate = new Date(year, month - 1, day);
                        calendar.gotoDate(initialDate);
                    }

                    // Allow URL updates now that initialization is complete
                    // Use setTimeout to ensure the datesSet event from initial date has fired
                    setTimeout(() => {
                        window.suppressUrlUpdate = false;
                    }, 100);

                    // Livewire event listeners
                    Livewire.on('calendar-prev', () => {
                        calendar.prev();
                        window.updateCalendarTitle();
                        // URL update will be handled by datesSet event
                    });

                    Livewire.on('calendar-next', () => {
                        calendar.next();
                        window.updateCalendarTitle();
                        // URL update will be handled by datesSet event
                    });

                    Livewire.on('calendar-today', () => {
                        calendar.today();
                        window.updateCalendarTitle();
                        // URL update will be handled by datesSet event
                    });

                    Livewire.on('calendar-change-view', (data) => {
                        calendar.changeView(data.view);
                        window.updateCalendarTitle();
                        // URL update will be handled by datesSet event
                    });

                    // Update calendar events when filters change
                    Livewire.on('calendar-refetch', () => {
                        if (window.fullCalendar) {
                            // Store current date and view before updating
                            const currentDate = window.fullCalendar.getDate();
                            const currentView = window.fullCalendar.view.type;

                            // Small delay to ensure Livewire has updated the events data
                            setTimeout(() => {
                                // Get updated events from the hidden input
                                const eventsDataInput = document.getElementById('calendar-events-data');
                                if (eventsDataInput) {
                                    try {
                                        const updatedEvents = JSON.parse(eventsDataInput.value);

                                        // Remove all existing events
                                        window.fullCalendar.removeAllEvents();

                                        // Add the new filtered events
                                        if (updatedEvents && updatedEvents.length > 0) {
                                            window.fullCalendar.addEventSource(updatedEvents);
                                        }

                                        // Restore the previous date and view
                                        window.fullCalendar.gotoDate(currentDate);
                                        window.fullCalendar.changeView(currentView);

                                        // Update title
                                        window.updateCalendarTitle();
                                    } catch (error) {
                                        console.error('Error parsing events data:', error);
                                    }
                                }
                            }, 100);
                        } else {
                            // If calendar is not initialized, try to initialize it
                            setTimeout(() => {
                                initializeCalendar();
                            }, 200);
                        }
                    });

                    // Handle window resize to update calendar height
                    let resizeTimeout;
                    window.addEventListener('resize', () => {
                        clearTimeout(resizeTimeout);
                        resizeTimeout = setTimeout(() => {
                            if (window.fullCalendar) {
                                const newHeight = window.innerWidth >= 1024 ? 750 : 600;
                                window.fullCalendar.setOption('height', newHeight);
                            }
                        }, 250);
                    });

                } catch (error) {
                    console.error('Error initializing calendar:', error);
                }
            }

            // Initialize when Livewire is ready
            document.addEventListener('livewire:initialized', () => {
                initializeCalendar();
            });

            // Handle Livewire navigation (when navigating to this page)
            document.addEventListener('livewire:navigated', () => {
                // Check if we're on the calendar page
                if (document.getElementById('calendar')) {
                    // Reset initialization flag and reinitialize
                    window.calendarInitialized = false;
                    if (window.fullCalendar) {
                        window.fullCalendar.destroy();
                        window.fullCalendar = null;
                    }
                    setTimeout(initializeCalendar, 100);
                }
            });

            // Handle Livewire component updates
            document.addEventListener('livewire:updated', () => {
                // Check if we're on the calendar page and calendar exists
                if (document.getElementById('calendar') && window.fullCalendar) {
                    // Small delay to ensure DOM is updated
                    setTimeout(() => {
                        // Check if the events data input has been updated
                        const eventsDataInput = document.getElementById('calendar-events-data');
                        if (eventsDataInput) {
                            try {
                                const updatedEvents = JSON.parse(eventsDataInput.value);

                                // Update calendar events
                                window.fullCalendar.removeAllEvents();
                                if (updatedEvents && updatedEvents.length > 0) {
                                    window.fullCalendar.addEventSource(updatedEvents);
                                }
                            } catch (error) {
                                console.error('Error updating calendar events:', error);
                            }
                        }
                    }, 50);
                }
            });


            // Only reinitialize calendar when filters change, not on every component update
            // This prevents the calendar from resetting to current month when modal closes

            // Fallback: Initialize after DOM is fully loaded
            if (document.readyState === 'complete') {
                setTimeout(initializeCalendar, 100);
            } else {
                window.addEventListener('load', () => {
                    setTimeout(initializeCalendar, 100);
                });
            }
        </script>
    @endpush
</div>
