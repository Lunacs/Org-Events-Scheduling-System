<div>
    {{-- Header --}}
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
    </div>

    {{-- Calendar Controls --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            {{-- Navigation --}}
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <x-mary-button onclick="window.fullCalendar?.prev(); updateCalendarTitle();"
                        class="btn-ghost btn-sm" icon="o-chevron-left" />
                    <h2 class="text-lg font-semibold min-w-[200px] text-center" id="calendar-title">
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
            <div class="flex items-center gap-4">
                <div class="flex gap-1" id="view-mode-buttons">
                    <x-mary-button onclick="changeCalendarView('dayGridMonth')" data-view="dayGridMonth"
                        class="btn-sm view-mode-btn {{ $viewMode === 'dayGridMonth' ? 'btn-primary' : 'btn-ghost' }}">
                        Month
                    </x-mary-button>
                    <x-mary-button onclick="changeCalendarView('timeGridWeek')" data-view="timeGridWeek"
                        class="btn-sm view-mode-btn {{ $viewMode === 'timeGridWeek' ? 'btn-primary' : 'btn-ghost' }}">
                        Week
                    </x-mary-button>
                    <x-mary-button onclick="changeCalendarView('timeGridDay')" data-view="timeGridDay"
                        class="btn-sm view-mode-btn {{ $viewMode === 'timeGridDay' ? 'btn-primary' : 'btn-ghost' }}">
                        Day
                    </x-mary-button>
                    <x-mary-button onclick="changeCalendarView('listWeek')" data-view="listWeek"
                        class="btn-sm view-mode-btn {{ $viewMode === 'listWeek' ? 'btn-primary' : 'btn-ghost' }}">
                        List
                    </x-mary-button>
                </div>

                <div class="flex gap-2">
                    <x-mary-select wire:model.live="statusFilter" placeholder="Status" :options="[
                        ['id' => '', 'name' => 'All Statuses'],
                        ['id' => 'scheduled', 'name' => 'Scheduled'],
                        ['id' => 'ongoing', 'name' => 'Ongoing'],
                        ['id' => 'completed', 'name' => 'Completed'],
                        ['id' => 'cancelled', 'name' => 'Cancelled'],
                    ]"
                        option-value="id" option-label="name" class="select-sm" />

                    <x-mary-select wire:model.live="organizationFilter" placeholder="Organization" :options="$organizations"
                        option-value="org_id" option-label="org_name" class="select-sm text-xs" />

                    <x-mary-select wire:model.live="eventTypeFilter" placeholder="Event Type" :options="$eventTypes"
                        option-value="event_type_id" option-label="type_name" class="select-sm" />

                    <x-mary-button wire:click="clearFilters" class="btn-ghost btn-sm" icon="o-x-mark"
                        tooltip="Clear Filters" />
                </div>
            </div>
        </div>
    </div>

    {{-- FullCalendar --}}
    <div class="bg-base-100 rounded-box shadow-lg overflow-hidden p-6">
        <div id="calendar" wire:ignore></div>
    </div>

    {{-- Event Details Modal --}}
    <x-mary-modal wire:model="showModal" title="Event Details" class="modal-lg">
        @if ($selectedEvent)
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
                        <x-mary-badge value="{{ ucfirst($selectedEvent->ticket->status) }}" class="badge-primary" />
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
                                <span>{{ $selectedEvent->ticket->{'venue-requested'} ?? 'TBD' }}</span>
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
        @endif

        <x-slot:actions>
            <x-mary-button wire:click="closeModal" class="btn-ghost">Close</x-mary-button>
        </x-slot:actions>
    </x-mary-modal>

    {{-- FullCalendar Scripts --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.10/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.10/index.global.min.js"></script>

        <script>
            // Helper function to update calendar title
            window.updateCalendarTitle = function() {
                const titleEl = document.getElementById('calendar-title');
                if (titleEl && window.fullCalendar) {
                    titleEl.textContent = window.fullCalendar.view.title;
                }
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
                }
            };

            function initializeCalendar() {
                console.log('Initializing FullCalendar...');

                // Check if FullCalendar is loaded
                if (typeof FullCalendar === 'undefined') {
                    console.error('FullCalendar is not loaded!');
                    return;
                }

                const calendarEl = document.getElementById('calendar');
                if (!calendarEl) {
                    console.error('Calendar element not found!');
                    return;
                }

                try {
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: '{{ $viewMode }}',
                        headerToolbar: false, // Use our custom controls
                        height: 'auto',
                        events: @json($events),

                        // Theme colors matching DaisyUI
                        themeSystem: 'standard',

                        // Event interactions
                        eventClick: function(info) {
                            @this.viewEvent(info.event.id);
                        },

                        // Event rendering
                        eventDisplay: 'block',
                        eventTextColor: '#ffffff',

                        // Time settings
                        slotMinTime: '08:00:00',
                        slotMaxTime: '20:00:00',
                        slotDuration: '01:00:00',
                        slotLabelInterval: '01:00:00',

                        // Week settings
                        firstDay: 1, // Monday

                        // List view settings
                        listDayFormat: {
                            weekday: 'long',
                            month: 'short',
                            day: 'numeric'
                        },
                        listDaySideFormat: false,

                        // Responsive
                        aspectRatio: 1.8,

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
                        },

                        // Update title when view changes
                        viewDidMount: function(info) {
                            const titleEl = document.getElementById('calendar-title');
                            if (titleEl) {
                                titleEl.textContent = info.view.title;
                            }
                        }
                    });

                    calendar.render();
                    console.log('Calendar rendered successfully!');

                    // Update title immediately after render
                    const titleEl = document.getElementById('calendar-title');
                    if (titleEl) {
                        titleEl.textContent = calendar.view.title;
                    }

                    // Store calendar globally for Livewire access
                    window.fullCalendar = calendar;

                    // Livewire event listeners
                    Livewire.on('calendar-prev', () => {
                        calendar.prev();
                        const titleEl = document.getElementById('calendar-title');
                        if (titleEl) titleEl.textContent = calendar.view.title;
                    });

                    Livewire.on('calendar-next', () => {
                        calendar.next();
                        const titleEl = document.getElementById('calendar-title');
                        if (titleEl) titleEl.textContent = calendar.view.title;
                    });

                    Livewire.on('calendar-today', () => {
                        calendar.today();
                        const titleEl = document.getElementById('calendar-title');
                        if (titleEl) titleEl.textContent = calendar.view.title;
                    });

                    Livewire.on('calendar-change-view', (data) => {
                        calendar.changeView(data.view);
                        const titleEl = document.getElementById('calendar-title');
                        if (titleEl) titleEl.textContent = calendar.view.title;
                    });

                    Livewire.on('calendar-refetch', () => {
                        calendar.refetchEvents();
                    });

                } catch (error) {
                    console.error('Error initializing calendar:', error);
                }
            }

            // Initialize when Livewire is ready
            document.addEventListener('livewire:initialized', initializeCalendar);

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
