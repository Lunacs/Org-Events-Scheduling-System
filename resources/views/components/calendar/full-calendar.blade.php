@props([
    'events' => [],
    'viewMode' => 'dayGridMonth',
    'calendarId' => 'calendar-' . uniqid(),
    'height' => 'min-h-[600px] lg:min-h-[750px]',
    'showNavigation' => true,
    'showViewModes' => true,
    'showFilters' => false,
    'onEventClick' => null,
    'filterSlot' => null,
    'updateEvent' => 'calendar-updated'
])

@php
    // Sanitize calendar ID for use in JavaScript function names
    $jsCalendarId = str_replace('-', '_', $calendarId);
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    {{-- Calendar Controls --}}
    @if($showNavigation || $showViewModes || $showFilters)
        <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                {{-- Navigation --}}
                @if($showNavigation)
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <x-mary-button onclick="window['{{ $calendarId }}']?.prev(); window['updateCalendarTitle_{{ $jsCalendarId }}']();"
                                           class="btn-ghost btn-sm" icon="o-chevron-left" />
                            <h2 class="text-lg font-semibold min-w-[200px] text-center" id="{{ $calendarId }}-title" wire:ignore>
                                Loading...
                            </h2>
                            <x-mary-button onclick="window['{{ $calendarId }}']?.next(); window['updateCalendarTitle_{{ $jsCalendarId }}']();"
                                           class="btn-ghost btn-sm" icon="o-chevron-right" />
                        </div>
                        <x-mary-button onclick="window['{{ $calendarId }}']?.today(); window['updateCalendarTitle_{{ $jsCalendarId }}']();"
                                       class="btn-outline btn-sm">
                            Today
                        </x-mary-button>
                    </div>
                @endif

                {{-- View Mode & Filters --}}
                <div class="flex items-center gap-4">
                    @if($showViewModes)
                        <div class="flex gap-1" id="{{ $calendarId }}-view-buttons">
                            <x-mary-button onclick="window['changeCalendarView_{{ $jsCalendarId }}']('dayGridMonth')" data-view="dayGridMonth"
                                           class="btn-sm view-mode-btn {{ $viewMode === 'dayGridMonth' ? 'btn-primary' : 'btn-ghost' }}">
                                Month
                            </x-mary-button>
                            <x-mary-button onclick="window['changeCalendarView_{{ $jsCalendarId }}']('timeGridWeek')" data-view="timeGridWeek"
                                           class="btn-sm view-mode-btn {{ $viewMode === 'timeGridWeek' ? 'btn-primary' : 'btn-ghost' }}">
                                Week
                            </x-mary-button>
                            <x-mary-button onclick="window['changeCalendarView_{{ $jsCalendarId }}']('timeGridDay')" data-view="timeGridDay"
                                           class="btn-sm view-mode-btn {{ $viewMode === 'timeGridDay' ? 'btn-primary' : 'btn-ghost' }}">
                                Day
                            </x-mary-button>
                            <x-mary-button onclick="window['changeCalendarView_{{ $jsCalendarId }}']('listWeek')" data-view="listWeek"
                                           class="btn-sm view-mode-btn {{ $viewMode === 'listWeek' ? 'btn-primary' : 'btn-ghost' }}">
                                List
                            </x-mary-button>
                        </div>
                    @endif

                    @if($showFilters && isset($filterSlot))
                        {{ $filterSlot }}
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- FullCalendar --}}
    <div class="bg-base-100 rounded-box shadow-lg overflow-hidden p-6">
        <div id="{{ $calendarId }}" wire:ignore class="{{ $height }}"></div>
    </div>

    {{-- Custom Calendar Styles --}}
    @once
        @push('styles')
            <style>
                .fc-scroller {
                    overflow-y: auto !important;
                    overflow-x: hidden !important;
                }
                .fc-scroller::-webkit-scrollbar { width: 8px; }
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
                @media (min-width: 1024px) {
                    .fc .fc-timegrid-slot { height: 3em; }
                    .fc .fc-timegrid-slot-label { font-size: 0.95em; }
                }
                .fc-list-event { cursor: pointer; }
                .fc-list-event:hover { background: oklch(var(--b3)) !important; }
            </style>
        @endpush
    @endonce

    {{-- FullCalendar Scripts --}}
    @push('scripts')
        <script>
            window['updateCalendarTitle_{{ $jsCalendarId }}'] = function() {
                const titleEl = document.getElementById('{{ $calendarId }}-title');
                if (titleEl && window['{{ $calendarId }}']) {
                    titleEl.textContent = window['{{ $calendarId }}'].view.title;
                }
            };

            window['changeCalendarView_{{ $jsCalendarId }}'] = function(viewMode) {
                if (window['{{ $calendarId }}']) {
                    window['{{ $calendarId }}'].changeView(viewMode);
                    window['updateCalendarTitle_{{ $jsCalendarId }}']();

                    const buttons = document.querySelectorAll('#{{ $calendarId }}-view-buttons .view-mode-btn');
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

            window['initializeCalendar_{{ $jsCalendarId }}'] = function() {
                if (typeof window.FullCalendar === 'undefined' || typeof window.FullCalendarPlugins === 'undefined') {
                    console.error('FullCalendar npm packages not loaded!');
                    return;
                }

                const calendarEl = document.getElementById('{{ $calendarId }}');
                if (!calendarEl) {
                    console.error('Calendar element not found:', '{{ $calendarId }}');
                    return;
                }

                try {
                    // Destroy existing instance safely
                    const existingCalendar = window['{{ $calendarId }}'];
                    if (existingCalendar && typeof existingCalendar.destroy === 'function') {
                        existingCalendar.destroy();
                    }

                    const calendarHeight = window.innerWidth >= 1024 ? 750 : 600;
                    const calendar = new window.FullCalendar.Calendar(calendarEl, {
                        plugins: [
                            window.FullCalendarPlugins.dayGridPlugin,
                            window.FullCalendarPlugins.timeGridPlugin,
                            window.FullCalendarPlugins.listPlugin,
                            window.FullCalendarPlugins.interactionPlugin
                        ],
                        initialView: '{{ $viewMode }}',
                        headerToolbar: false,
                        height: calendarHeight,
                        events: @json($events),
                        themeSystem: 'standard',
                        nowIndicator: true,
                        eventClick: function(info) {
                            @if($onEventClick)
                            @this.call('{{ $onEventClick }}', info.event.id);
                            @endif
                        },
                        eventDisplay: 'block',
                        eventTextColor: '#ffffff',
                        slotMinTime: '07:00:00',
                        slotMaxTime: '22:00:00',
                        slotDuration: '01:00:00',
                        scrollTime: '08:00:00',
                        scrollTimeReset: true,
                        firstDay: 1,
                        listDayFormat: { weekday: 'long', month: 'short', day: 'numeric' },
                        listDaySideFormat: false,
                        eventDidMount: function(info) {
                            const props = info.event.extendedProps;
                            info.el.title = `${info.event.title}\n${props.organization}\n${props.eventType}\n${props.venue}`;
                            info.el.style.borderRadius = '6px';
                            info.el.style.fontSize = '12px';
                            info.el.style.padding = '2px 4px';
                        },
                        viewDidMount: function(info) {
                            const titleEl = document.getElementById('{{ $calendarId }}-title');
                            if (titleEl) titleEl.textContent = info.view.title;
                        }
                    });

                    calendar.render();
                    window['{{ $calendarId }}'] = calendar;
                    console.log('Calendar initialized:', '{{ $calendarId }}');

                    // Handle window resize
                    let resizeTimeout;
                    window.addEventListener('resize', () => {
                        clearTimeout(resizeTimeout);
                        resizeTimeout = setTimeout(() => {
                            if (window['{{ $calendarId }}']) {
                                const newHeight = window.innerWidth >= 1024 ? 750 : 600;
                                window['{{ $calendarId }}'].setOption('height', newHeight);
                            }
                        }, 250);
                    });
                } catch (error) {
                    console.error('Error initializing calendar {{ $calendarId }}:', error);
                }
            };

            // Initialize on Livewire ready
            document.addEventListener('livewire:initialized', () => {
                console.log('Livewire initialized, setting up calendar {{ $calendarId }}');
                setTimeout(() => window['initializeCalendar_{{ $jsCalendarId }}'](), 200);

                // Listen for update events from Livewire component (MOVED INSIDE)
                Livewire.on('{{ $updateEvent }}', () => {
                    console.log('Update event received for {{ $calendarId }}');
                    if (window['{{ $calendarId }}']) {
                        const currentDate = window['{{ $calendarId }}'].getDate();
                        const currentView = window['{{ $calendarId }}'].view.type;

                        setTimeout(() => {
                            window['initializeCalendar_{{ $jsCalendarId }}']();
                            if (currentDate && window['{{ $calendarId }}']) {
                                window['{{ $calendarId }}'].gotoDate(currentDate);
                                window['{{ $calendarId }}'].changeView(currentView);
                                setTimeout(() => window['updateCalendarTitle_{{ $jsCalendarId }}'](), 100);
                            }
                        }, 100);
                    } else {
                        window['initializeCalendar_{{ $jsCalendarId }}']();
                    }
                });
            });
        </script>
    @endpush


</div>

