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

                <div class="flex flex-wrap gap-2">
                    <x-mary-select wire:model.live="statusFilter" placeholder="Status" :options="[
                        ['id' => 'approved', 'name' => 'Approved'],
                        ['id' => 'rescheduled', 'name' => 'Rescheduled'],
                    ]"
                        option-value="id" option-label="name" class="select-sm min-w-[140px]" />

                    <x-mary-select wire:model.live="organizationFilter" placeholder="Organization" :options="$organizations"
                        option-value="org_id" option-label="org_name"
                        class="select-sm text-xs min-w-[200px] max-w-[300px]" />

                    <x-mary-select wire:model.live="eventTypeFilter" placeholder="Event Type" :options="$eventTypes"
                        option-value="event_type_id" option-label="type_name" class="select-sm min-w-[150px]" />

                    <x-mary-button wire:click="clearFilters" class="btn-ghost btn-sm" icon="o-x-mark"
                        tooltip="Clear Filters" />
                </div>
            </div>
        </div>
    </div>

    {{-- FullCalendar --}}
    <div class="bg-base-100 rounded-box shadow-lg overflow-hidden p-6"
        wire:key="calendar-{{ $statusFilter }}-{{ $organizationFilter }}-{{ $eventTypeFilter }}">
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
                        <x-mary-badge value="{{ str_replace('_', ' ', ucwords($selectedEvent->ticket->status, '_')) }}"
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
            // Flag to prevent unnecessary reinitialization
            let calendarInitialized = false;

            // Helper function to update calendar title
            window.updateCalendarTitle = function() {
                const titleEl = document.getElementById('calendar-title');
                if (titleEl && window.fullCalendar) {
                    titleEl.textContent = window.fullCalendar.view.title;
                    console.log('Title updated to:', window.fullCalendar.view.title);
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
                console.log('Initializing FullCalendar from npm packages...');

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

                try {
                    // Destroy existing calendar if it exists
                    if (window.fullCalendar) {
                        console.log('Destroying existing calendar...');
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

                        // Enable now indicator
                        nowIndicator: true,

                        // Event interactions
                        eventClick: function(info) {
                            console.log('Event clicked:', info.event.id, info.event);
                            console.log('Event data:', info.event.extendedProps);
                            console.log('Livewire component:', @this);
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
                        },

                        // Update title when view changes
                        viewDidMount: function(info) {
                            const titleEl = document.getElementById('calendar-title');
                            if (titleEl) {
                                titleEl.textContent = info.view.title;
                                console.log('View mounted, title set to:', info.view.title);
                            }
                        }
                    });

                    calendar.render();
                    console.log('Calendar rendered successfully!');

                    // Store calendar globally for Livewire access
                    window.fullCalendar = calendar;

                    // Update title immediately after render
                    const titleEl = document.getElementById('calendar-title');
                    if (titleEl) {
                        titleEl.textContent = calendar.view.title;
                        console.log('Calendar title updated to:', calendar.view.title);
                    }

                    // Add a more robust title update function
                    window.updateCalendarTitle = function() {
                        const titleEl = document.getElementById('calendar-title');
                        if (titleEl && window.fullCalendar) {
                            titleEl.textContent = window.fullCalendar.view.title;
                            console.log('Title updated via helper function:', window.fullCalendar.view.title);
                        }
                    };

                    // Livewire event listeners
                    Livewire.on('calendar-prev', () => {
                        calendar.prev();
                        window.updateCalendarTitle();
                    });

                    Livewire.on('calendar-next', () => {
                        calendar.next();
                        window.updateCalendarTitle();
                    });

                    Livewire.on('calendar-today', () => {
                        calendar.today();
                        window.updateCalendarTitle();
                    });

                    Livewire.on('calendar-change-view', (data) => {
                        calendar.changeView(data.view);
                        window.updateCalendarTitle();
                    });

                    // Re-initialize calendar when filters change
                    Livewire.on('calendar-refetch', () => {
                        // Store current date before destroying calendar
                        let currentDate = null;
                        if (window.fullCalendar) {
                            currentDate = window.fullCalendar.getDate();
                            window.fullCalendar.destroy();
                        }
                        // Small delay to ensure Livewire has updated
                        setTimeout(() => {
                            initializeCalendar();
                            // Restore the previous date after reinitializing
                            if (currentDate && window.fullCalendar) {
                                window.fullCalendar.gotoDate(currentDate);
                                // Update title using the helper function
                                setTimeout(() => {
                                    window.updateCalendarTitle();
                                }, 100);
                            }
                        }, 100);
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
                console.log('Livewire initialized, starting calendar initialization...');
                initializeCalendar();
            });

            // Handle Livewire navigation (when navigating to this page)
            document.addEventListener('livewire:navigated', () => {
                console.log('Livewire navigated, checking calendar...');
                if (!window.fullCalendar && document.getElementById('calendar')) {
                    initializeCalendar();
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
