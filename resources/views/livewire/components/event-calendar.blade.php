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
                    <x-mary-badge value="{{ $uniqueEventsCount }} Events" class="badge-primary" />
                </div>
            </div>
        </div>
    </div>

    {{-- Calendar Controls --}}
    <div x-data="osaCalendar()" x-init="init()" x-cloak
        class="bg-base-100 rounded-box shadow-lg p-4 sm:p-6 mb-6">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            {{-- Navigation Section --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                {{-- Calendar Navigation --}}
                <div class="flex items-center justify-center sm:justify-start gap-2">
                    <x-mary-button @click="prev()" class="btn-ghost btn-sm" icon="o-chevron-left" />
                    <h2 class="text-base sm:text-lg font-semibold min-w-40 sm:min-w-[200px] text-center"
                        id="calendar-title" wire:ignore>
                        Loading...
                    </h2>
                    <x-mary-button @click="next()" class="btn-ghost btn-sm" icon="o-chevron-right" />
                </div>

                {{-- Today Button --}}
                <div class="flex justify-center sm:justify-start">
                    <x-mary-button @click="today()" class="btn-outline btn-sm">
                        Today
                    </x-mary-button>
                </div>
            </div>

            {{-- View Mode & Filters Section --}}
            <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 md:gap-4">
                {{-- View Mode Buttons --}}
                <div class="flex gap-1 order-2 md:order-1 flex-1" id="view-mode-buttons">
                    <x-mary-button @click="changeView('dayGridMonth')" data-view="dayGridMonth"
                        class="btn-sm view-mode-btn flex-1 md:flex-none"
                        x-bind:class="currentView === 'dayGridMonth' ? 'btn-primary' : 'btn-ghost'">
                        Month
                    </x-mary-button>
                    <x-mary-button @click="changeView('timeGridWeek')" data-view="timeGridWeek"
                        class="btn-sm view-mode-btn flex-1 md:flex-none"
                        x-bind:class="currentView === 'timeGridWeek' ? 'btn-primary' : 'btn-ghost'">
                        Week
                    </x-mary-button>
                    <x-mary-button @click="changeView('timeGridDay')" data-view="timeGridDay"
                        class="btn-sm view-mode-btn flex-1 md:flex-none"
                        x-bind:class="currentView === 'timeGridDay' ? 'btn-primary' : 'btn-ghost'">
                        Day
                    </x-mary-button>
                    <x-mary-button @click="changeView('listWeek')" data-view="listWeek"
                        class="btn-sm view-mode-btn flex-1 md:flex-none"
                        x-bind:class="currentView === 'listWeek' ? 'btn-primary' : 'btn-ghost'">
                        List
                    </x-mary-button>
                </div>

                {{-- Filter Button with Notification Badge --}}
                <div class="flex justify-center md:justify-end order-1 md:order-2 shrink-0">
                    <div x-data x-init="if (window.Alpine && !Alpine.store('filters')) { Alpine.store('filters', { status: 'approved', org: '', etype: '' }) }" class="relative">
                        {{-- Notification Badge --}}
                        <div x-show="[$store.filters?.status, $store.filters?.org, $store.filters?.etype].filter(v => v).length > 0"
                            class="absolute -top-2 -right-2 z-10">
                            <div
                                class="badge badge-primary badge-sm h-5 w-5 p-0 flex items-center justify-center text-neutral-content text-xs font-bold">
                                <span
                                    x-text="[$store.filters?.status, $store.filters?.org, $store.filters?.etype].filter(v => v).length"></span>
                            </div>
                        </div>

                        {{-- Filter Button --}}
                        <x-mary-button icon="o-funnel" class="btn-ghost btn-sm shrink-0"
                            @click="$dispatch('open-filters')" tooltip="Open Filters">
                            <span class="hidden sm:inline">Filters</span>
                            <span class="sm:hidden">Filter</span>
                        </x-mary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Drawer (Alpine-controlled) --}}
    <div x-data="filterPanel({
        initialStatus: '{{ $statusFilter }}',
        initialOrg: '{{ $organizationFilter }}',
        initialType: '{{ $eventTypeFilter }}'
    })" x-init="init()" x-on:open-filters.window="open = true" x-cloak
        x-on:clear-filters.window="clearAll()">
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 ">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div
                class="absolute right-0 top-0 h-full w-11/12 lg:w-1/3 bg-base-100 shadow-xl border-l border-base-300 flex flex-col rounded-l-2xl">
                <div class="px-6 py-4 border-b border-base-300">
                    <h3 class="text-base font-semibold">Filter Events</h3>
                    <p class="text-sm opacity-70">Refine your calendar view</p>
                </div>
                <div class="flex-1 overflow-y-auto p-6">

                    <div class="space-y-6">
                        {{-- Status Filter --}}
                        <div>
                            <label class="label">
                                <span class="label-text font-semibold">Status</span>
                            </label>
                            <select x-model="status" class="select select-bordered w-full">
                                <option value="">All Statuses</option>
                                <option value="approved">Approved</option>
                                <option value="rescheduled">Rescheduled</option>
                            </select>
                        </div>

                        {{-- Organization Filter --}}
                        <div>
                            <label class="label">
                                <span class="label-text font-semibold">Organization</span>
                            </label>
                            <select x-model="org" class="select select-bordered w-full">
                                <option value="">All Organizations</option>
                                @foreach ($organizations as $org)
                                    <option value="{{ $org->org_id }}">{{ $org->org_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Event Type Filter --}}
                        <div>
                            <label class="label">
                                <span class="label-text font-semibold">Event Type</span>
                            </label>
                            <select x-model="etype" class="select select-bordered w-full">
                                <option value="">All Event Types</option>
                                @foreach ($eventTypes as $t)
                                    <option value="{{ $t->event_type_id }}">{{ $t->type_name }}</option>
                                @endforeach
                            </select>
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

                </div>
                <div class="px-6 py-4 border-t border-base-300 flex justify-end gap-2">
                    <button type="button" class="btn btn-ghost" @click="clearAll()">Clear All</button>
                    <button type="button" class="btn btn-primary" @click="apply()">Apply</button>
                </div>
            </div>
        </div>

        {{-- Lightweight toast --}}
        <div x-show="toast.show" x-transition.opacity class="fixed bottom-4 right-4 z-50">
            <div class="alert alert-info shadow">
                <span x-text="toast.message"></span>
            </div>
        </div>
    </div>

    {{-- Hidden input to store events data for JavaScript access --}}
    <input type="hidden" id="calendar-events-data" value="{{ json_encode($events) }}">

    {{-- Hidden input to store initial date for JavaScript access --}}
    <input type="hidden" id="calendar-initial-date" value="{{ $currentDate->format('Y-m-d') }}">

    {{-- FullCalendar --}}
    <div id="osa-calendar" class="bg-base-100 rounded-box shadow-lg overflow-hidden p-3 sm:p-4 md:p-6">
        <div x-ref="cal" wire:ignore class="min-h-[500px] md:min-h-[650px] lg:min-h-[750px]"></div>
    </div>

    {{-- Upcoming Events This Month --}}
    <x-mary-card title="Upcoming Events This Month of {{ ucfirst($currentDate->format('F')) }}"
        subtitle="Detailed list of scheduled events" class="mt-6">
        @if (count($this->upcomingEventsThisMonth) > 0)
            <div class="space-y-4">
                @foreach ($this->upcomingEventsThisMonth as $index => $event)
                    @php
                        $color = $event['color'];
                        $bgColor = match ($color) {
                            'blue' => 'bg-blue-50',
                            'green' => 'bg-green-50',
                            'purple' => 'bg-purple-50',
                            'yellow' => 'bg-yellow-50',
                            'red' => 'bg-red-50',
                            'cyan' => 'bg-cyan-50',
                            'lime' => 'bg-lime-50',
                            'orange' => 'bg-orange-50',
                            default => 'bg-blue-50',
                        };
                        $borderColor = match ($color) {
                            'blue' => 'border-blue-400',
                            'green' => 'border-green-400',
                            'purple' => 'border-purple-400',
                            'yellow' => 'border-yellow-400',
                            'red' => 'border-red-400',
                            'cyan' => 'border-cyan-400',
                            'lime' => 'border-lime-400',
                            'orange' => 'border-orange-400',
                            default => 'border-blue-400',
                        };
                        $iconBg = match ($color) {
                            'blue' => 'bg-blue-100',
                            'green' => 'bg-green-100',
                            'purple' => 'bg-purple-100',
                            'yellow' => 'bg-yellow-100',
                            'red' => 'bg-red-100',
                            'cyan' => 'bg-cyan-100',
                            'lime' => 'bg-lime-100',
                            'orange' => 'bg-orange-100',
                            default => 'bg-blue-100',
                        };
                        $iconText = match ($color) {
                            'blue' => 'text-blue-600',
                            'green' => 'text-green-600',
                            'purple' => 'text-purple-600',
                            'yellow' => 'text-yellow-600',
                            'red' => 'text-red-600',
                            'cyan' => 'text-cyan-600',
                            'lime' => 'text-lime-600',
                            'orange' => 'text-orange-600',
                            default => 'text-blue-600',
                        };
                        $titleColor = match ($color) {
                            'blue' => 'text-blue-900',
                            'green' => 'text-green-900',
                            'purple' => 'text-purple-900',
                            'yellow' => 'text-yellow-900',
                            'red' => 'text-red-900',
                            'cyan' => 'text-cyan-900',
                            'lime' => 'text-lime-900',
                            'orange' => 'text-orange-900',
                            default => 'text-blue-900',
                        };
                        $textColor = match ($color) {
                            'blue' => 'text-blue-700',
                            'green' => 'text-green-700',
                            'purple' => 'text-purple-700',
                            'yellow' => 'text-yellow-700',
                            'red' => 'text-red-700',
                            'cyan' => 'text-cyan-700',
                            'lime' => 'text-lime-700',
                            'orange' => 'text-orange-700',
                            default => 'text-blue-700',
                        };
                        $metaColor = match ($color) {
                            'blue' => 'text-blue-600',
                            'green' => 'text-green-600',
                            'purple' => 'text-purple-600',
                            'yellow' => 'text-yellow-600',
                            'red' => 'text-red-600',
                            'cyan' => 'text-cyan-600',
                            'lime' => 'text-lime-600',
                            'orange' => 'text-orange-600',
                            default => 'text-blue-600',
                        };
                        $badgeClass = match ($color) {
                            'blue' => 'badge-info',
                            'green' => 'badge-success',
                            'purple' => 'badge-secondary',
                            'yellow' => 'badge-warning',
                            'red' => 'badge-error',
                            'cyan' => 'badge-info',
                            'lime' => 'badge-success',
                            'orange' => 'badge-warning',
                            default => 'badge-info',
                        };
                    @endphp
                    <div class="flex items-start space-x-4 p-4 {{ $bgColor }} rounded-lg border-l-4 {{ $borderColor }} hover:shadow-md transition-all cursor-pointer"
                        wire:key="upcoming-{{ $index }}"
                        onclick="window.dispatchEvent(new CustomEvent('open-event', { detail: { id: {{ $event['event_id'] }} } }))">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 {{ $iconBg }} rounded-lg flex items-center justify-center">
                                <x-mary-icon name="{{ $event['icon'] }}" class="w-6 h-6 {{ $iconText }}" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold {{ $titleColor }}">{{ $event['title'] }}</h4>
                            @if ($event['description'])
                                <p class="text-sm {{ $textColor }} mt-1">
                                    {{ Str::limit($event['description'], 80) }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-4 mt-2 text-sm {{ $metaColor }}">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="o-calendar" class="w-4 h-4" />
                                    <span>{{ $event['datetime'] }}</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="o-map-pin" class="w-4 h-4" />
                                    <span>{{ $event['venue'] }}</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="o-user-group" class="w-4 h-4" />
                                    <span>{{ $event['organization'] }}</span>
                                </span>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <x-mary-badge value="{{ $event['eventType'] }}" class="{{ $badgeClass }}" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <x-mary-icon name="o-calendar-days" class="w-16 h-16 mx-auto mb-3 text-gray-300" />
                <p class="text-sm text-gray-500">No upcoming events scheduled for this month</p>
            </div>
        @endif
    </x-mary-card>

    {{-- Event Details Modal (Alpine-controlled, opens instantly) --}}
    <div x-data="eventDetailsModal()" x-on:open-event.window="openById($event.detail.id)">
        <template x-if="open">
            <div class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="close()"></div>
                <div class="relative bg-base-100 w-11/12 max-w-3xl rounded-box shadow-xl border border-base-300 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold" x-text="data?.title || 'Event Details'"></h2>
                            <p class="text-base-content/70" x-text="data?.organization || ''"></p>
                        </div>
                        <span class="badge"
                            :class="{
                                'badge-success': (data?.status || '') === 'approved',
                                'badge-warning': (data?.status || '') === 'rescheduled',
                                'badge-primary': ['approved', 'rescheduled'].indexOf(data?.status || '') === -1
                            }"
                            x-text="(data?.status||'').replace('_',' ').replace(/\b\w/g, c => c.toUpperCase())"></span>
                    </div>

                    <div x-show="loading" class="flex items-center justify-center py-10">
                        <div class="loading loading-dots loading-xl text-primary"></div>
                    </div>

                    <div x-show="!loading" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-semibold mb-3">Event Details</h3>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-medium text-base-content/70">Ticket #:</span>
                                        <span x-text="data?.ticketNumber || '—'"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-base-content/70">Type:</span>
                                        <span x-text="data?.type || 'N/A'"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-base-content/70">Venue:</span>
                                        <span x-text="data?.venue || 'TBD'"></span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-semibold mb-3">Schedule</h3>
                                <div class="space-y-2">
                                    <template x-for="(s, idx) in (data?.schedules||[])" :key="idx">
                                        <div class="bg-base-200 rounded-lg p-3">
                                            <template x-if="s.start_date === s.end_date || !s.end_date">
                                                <div class="flex items-center gap-2 text-sm">
                                                    <x-mary-icon name="o-calendar-days"
                                                        class="w-4 h-4 text-primary" />
                                                    <span x-text="formatDate(s.start_date)"></span>
                                                </div>
                                            </template>
                                            <template x-if="s.start_date !== s.end_date && s.end_date">
                                                <div class="space-y-1">
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <x-mary-icon name="o-calendar-days"
                                                            class="w-4 h-4 text-primary" />
                                                        <span class="font-medium text-base-content/70">Start:</span>
                                                        <span x-text="formatDate(s.start_date)"></span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-sm">
                                                        <x-mary-icon name="o-calendar-days"
                                                            class="w-4 h-4 text-primary" />
                                                        <span class="font-medium text-base-content/70">End:</span>
                                                        <span x-text="formatDate(s.end_date)"></span>
                                                    </div>
                                                </div>
                                            </template>
                                            <div class="flex items-center gap-2 text-sm mt-1">
                                                <x-mary-icon name="o-clock" class="w-4 h-4 text-primary" />
                                                <span class="font-medium text-base-content/70">Time:</span>
                                                <span x-text="timeRange(s.start_time, s.end_time)"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <template x-if="data?.description">
                            <div>
                                <h3 class="font-semibold mb-3">Description</h3>
                                <p class="text-sm text-base-content/80" x-text="data.description"></p>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button" class="btn btn-ghost" @click="close()">Close</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Custom Calendar Styles for OSA Calendar Component --}}
    @push('styles')
        <style>
            /* Component-specific calendar enhancements */
            #osa-calendar .fc {
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            }

            /* Ensure proper height calculation */
            #osa-calendar .fc-view-harness {
                background-color: var(--fc-neutral-bg-color);
            }

            /* Enhanced event colors for better visibility */
            #osa-calendar .fc-event.event-approved {
                background-color: oklch(64.8% 0.15 160) !important;
                /* success */
                border-color: oklch(64.8% 0.15 160) !important;
            }

            #osa-calendar .fc-event.event-rescheduled {
                background-color: oklch(84.71% 0.199 83.87) !important;
                /* warning */
                border-color: oklch(84.71% 0.199 83.87) !important;
                color: oklch(0% 0 0) !important;
            }

            #osa-calendar .fc-event.event-pending {
                background-color: oklch(72.06% 0.191 231.6) !important;
                /* info */
                border-color: oklch(72.06% 0.191 231.6) !important;
            }

            #osa-calendar .fc-event.event-cancelled {
                background-color: oklch(71.76% 0.221 22.18) !important;
                /* error */
                border-color: oklch(71.76% 0.221 22.18) !important;
            }

            /* Loading skeleton animation */
            @keyframes shimmer {
                0% {
                    background-position: -1000px 0;
                }

                100% {
                    background-position: 1000px 0;
                }
            }

            #osa-calendar.loading .fc-view-harness {
                background: linear-gradient(90deg,
                        oklch(var(--b2)) 0%,
                        oklch(var(--b3)) 50%,
                        oklch(var(--b2)) 100%);
                background-size: 1000px 100%;
                animation: shimmer 2s infinite;
            }
        </style>
    @endpush

    {{-- FullCalendar Scripts (Alpine-powered) --}}
    @push('scripts')
        <script>
            window.osaCalendar = function() {
                return {
                    calendar: null,
                    currentView: '{{ $viewMode }}',
                    suppressUrlUpdate: true,
                    allEvents: @json($events), // Store events in component state for dynamic updates
                    init() {
                        if (typeof window.FullCalendar === 'undefined' || typeof window.FullCalendarPlugins ===
                            'undefined') {
                            console.error('FullCalendar not loaded. Run your asset build.');
                            return;
                        }

                        // Responsive height calculation helper
                        const getHeight = () => {
                            if (window.innerWidth >= 1024) return 750;
                            if (window.innerWidth >= 768) return 650;
                            return 500; // Mobile screens
                        };

                        // Store getHeight for resize handler
                        this.getHeight = getHeight;

                        const height = getHeight();
                        const initialView = '{{ $viewMode }}';
                        const component = this; // Store component reference for use in callbacks

                        this.calendar = new window.FullCalendar.Calendar(this.$refs.cal, {
                            plugins: [
                                window.FullCalendarPlugins.dayGridPlugin,
                                window.FullCalendarPlugins.timeGridPlugin,
                                window.FullCalendarPlugins.listPlugin,
                                window.FullCalendarPlugins.interactionPlugin,
                            ],
                            initialView: initialView,
                            headerToolbar: false,
                            height,
                            // Filter events based on current view
                            // Month view: Show spanning events (forMonthView: true) or single-day events
                            // Week/Day/List views: Show recurring events (forTimeView: true) or single-day events
                            events: function(info, successCallback, failureCallback) {
                                // Safely get current view
                                let currentView = null;

                                // Try to get view from info parameter first
                                if (info && info.view && info.view.type) {
                                    currentView = info.view.type;
                                }
                                // Fallback to component's tracked current view
                                else if (component && component.currentView) {
                                    currentView = component.currentView;
                                }
                                // Fallback to calendar's view if available
                                else if (component && component.calendar && component.calendar.view && component
                                    .calendar.view.type) {
                                    currentView = component.calendar.view.type;
                                }
                                // Final fallback to initial view
                                else {
                                    currentView = initialView;
                                }

                                // Filter events based on view type
                                // Use component's allEvents which gets updated when filters change
                                const eventsToFilter = component.allEvents || [];
                                const filteredEvents = eventsToFilter.filter(event => {
                                    const props = event.extendedProps || {};
                                    const isMultiDay = props.forMonthView || props.forTimeView;

                                    // Single-day events: Show in all views
                                    if (!isMultiDay) {
                                        return true;
                                    }

                                    // Month view: Only show spanning events
                                    if (currentView === 'dayGridMonth') {
                                        return props.forMonthView === true;
                                    }

                                    // Week/Day/List views: Only show recurring events
                                    return props.forTimeView === true;
                                });

                                successCallback(filteredEvents);
                            },
                            themeSystem: 'standard',

                            // CRITICAL: Time handling configuration
                            // FullCalendar receives ISO strings WITH timezone offset (+08:00)
                            // Set timeZone to 'Asia/Manila' so FullCalendar interprets these times
                            // as Asia/Manila time WITHOUT converting to user's local timezone
                            // This ensures events display at the exact times stored in the database
                            // Example: "2025-11-05T08:00:00+08:00" will display as 8:00 AM in Manila time
                            timeZone: 'Asia/Manila',
                            nowIndicator: true,
                            eventDataTransform: function(eventData) {
                                // Ensure allDay is explicitly false for timed events
                                if (eventData.allDay === undefined && eventData.start && eventData.end) {
                                    eventData.allDay = false;
                                }

                                return eventData;
                            },

                            // View-specific options
                            views: {
                                dayGridMonth: {
                                    dayMaxEvents: 3, // Show max 3 events, rest in "more" link
                                    eventMaxStack: 3,
                                    moreLinkText: 'more',
                                    dayHeaderFormat: {
                                        weekday: 'short'
                                    },
                                },
                                timeGridWeek: {
                                    eventDisplay: 'auto',
                                    dayHeaderFormat: {
                                        weekday: 'short',
                                        month: 'numeric',
                                        day: 'numeric',
                                        omitCommas: true
                                    },
                                    // slotDuration: '00:30:00', // 30-minute slots
                                    // snapDuration: '00:15:00', // Snap to 15-minute intervals
                                    eventMinHeight: 15, // Minimum height in pixels
                                    eventShortHeight: 20, // Height for short events
                                    expandRows: false, // Let events size based on duration
                                    slotEventOverlap: true, // Allow overlapping events
                                },
                                timeGridDay: {
                                    dayHeaderFormat: {
                                        weekday: 'long',
                                        month: 'long',
                                        day: 'numeric',
                                        year: 'numeric'
                                    },
                                    slotLabelFormat: {
                                        hour: 'numeric',
                                        minute: '2-digit',
                                        meridiem: 'short'
                                    },
                                    slotDuration: '00:30:00', // 30-minute slots
                                    snapDuration: '00:15:00', // Snap to 15-minute intervals
                                    eventMinHeight: 15, // Minimum height in pixels
                                    eventShortHeight: 20, // Height for short events
                                    expandRows: false, // Let events size based on duration
                                    slotEventOverlap: true, // Allow overlapping events
                                },
                                listWeek: {
                                    listDayFormat: {
                                        weekday: 'long',
                                        month: 'short',
                                        day: 'numeric'
                                    },
                                    listDaySideFormat: false,
                                    noEventsContent: 'No events scheduled for this period'
                                }
                            },

                            // Interaction & behavior
                            eventClick: (info) => {
                                this.openEventModal(info.event.id)
                            },

                            // Display settings
                            eventDisplay: 'auto', // Auto sizing based on duration
                            eventTextColor: '#ffffff',
                            weekNumbers: false,
                            weekText: 'W',
                            allDaySlot: false, // Hide all-day slot since events have specific times
                            nextDayThreshold: '09:00:00', // Events ending before 9am count as ending on previous day

                            // Time settings
                            slotMinTime: '07:00:00',
                            slotMaxTime: '23:00:00',
                            slotLabelInterval: '01:00:00', // Show labels every hour
                            scrollTime: '08:00:00',
                            scrollTimeReset: true,
                            slotEventOverlap: true, // Allow overlapping events

                            // Event rendering - CRITICAL for height calculation
                            eventMinHeight: 15, // Minimum height in pixels
                            eventShortHeight: 20, // Height threshold for "short" events

                            // Ensure proper duration calculation
                            defaultTimedEventDuration: '01:00:00', // Default 1 hour if no end time
                            forceEventDuration: false, // Use actual end times, not forced duration

                            // Week settings
                            firstDay: 1, // Monday

                            // List view settings
                            listDayFormat: {
                                weekday: 'long',
                                month: 'short',
                                day: 'numeric'
                            },
                            listDaySideFormat: false,
                            eventDidMount: (info) => {
                                const e = info.event;
                                const p = e.extendedProps || {};

                                // Format time for tooltip using raw database times to avoid timezone conversion
                                // Raw times are stored in HH:MM:SS format from the database (Asia/Manila timezone)
                                const formatTimeFromString = (timeStr) => {
                                    if (!timeStr) return '';
                                    try {
                                        // Parse HH:MM:SS format
                                        const [hours, minutes] = timeStr.split(':');
                                        const h = parseInt(hours, 10);
                                        const m = parseInt(minutes, 10);

                                        // Convert to 12-hour format
                                        const period = h >= 12 ? 'PM' : 'AM';
                                        const hour12 = h % 12 || 12;

                                        return `${hour12}:${m.toString().padStart(2, '0')} ${period}`;
                                    } catch (e) {
                                        return timeStr; // Return as-is if parsing fails
                                    }
                                };

                                // Use raw times from extendedProps (database values) for accurate tooltip
                                const rawStartTime = p.rawStartTime || '';
                                const rawEndTime = p.rawEndTime || '';
                                const startTimeFormatted = formatTimeFromString(rawStartTime);
                                const endTimeFormatted = formatTimeFromString(rawEndTime);
                                const timeRange = (startTimeFormatted && endTimeFormatted) ?
                                    `\n${startTimeFormatted} - ${endTimeFormatted}` :
                                    '';

                                info.el.title =
                                    `${e.title}${timeRange}\n${p.organization || ''}\n${p.eventType || ''}\n${p.venue || ''}`;

                                // Apply consistent styling
                                info.el.style.borderRadius = '6px';
                                info.el.style.fontSize = '12px';
                                info.el.style.padding = '2px 4px';

                                // Add status-based CSS classes for better theming
                                if (p.status) {
                                    info.el.classList.add(`event-${p.status}`);
                                }

                                // Special styling for list view
                                if (info.view.type === 'listWeek') {
                                    const c = e.backgroundColor || e.borderColor || '#10b981';
                                    info.el.style.setProperty('--event-color', c);

                                    // Add colored indicator dot
                                    const dotEl = info.el.querySelector('.fc-list-event-dot');
                                    if (dotEl) {
                                        dotEl.style.borderColor = c;
                                        dotEl.style.backgroundColor = c;
                                    }
                                }

                                // Add accessibility attributes
                                info.el.setAttribute('role', 'button');
                                info.el.setAttribute('aria-label', `Event: ${e.title}`);
                                info.el.setAttribute('tabindex', '0');

                                // Keyboard accessibility
                                info.el.addEventListener('keydown', (ev) => {
                                    if (ev.key === 'Enter' || ev.key === ' ') {
                                        ev.preventDefault();
                                        this.openEventModal(e.id);
                                    }
                                });
                            },
                            viewDidMount: () => {
                                this.updateTitle()
                                this.currentView = this.calendar.view.type
                                // Refetch events when view changes to apply correct filtering
                                this.calendar.refetchEvents()
                            },
                            datesSet: () => {
                                if (this.suppressUrlUpdate) return;
                                const d = this.calendar.getDate();
                                const y = d.getFullYear();
                                const m = String(d.getMonth() + 1).padStart(2, '0');
                                const day = String(d.getDate()).padStart(2, '0');
                                const dateStr = `${y}-${m}-${day}`;
                                const view = this.calendar.view.type;
                                const params = new URLSearchParams(window.location.search);
                                params.set('date', dateStr);
                                if (view && view !== 'dayGridMonth') params.set('viewMode', view);
                                else params.delete('viewMode');
                                const newUrl = window.location.pathname + (params.toString() ? '?' + params
                                    .toString() : '');
                                window.history.replaceState({}, '', newUrl);
                                this.$wire.set('dateParam', dateStr);
                                this.$wire.set('viewMode', view);
                                this.currentView = view;
                            },
                        });

                        this.calendar.render();
                        // expose for other parts (e.g., filterPanel) and stability
                        window.fullCalendar = this.calendar;
                        window.osaCalendarInstance = this; // Expose component instance for event updates
                        this.updateTitle();

                        // Navigate to initial date from server
                        const initialDate = '{{ $currentDate->format('Y-m-d') }}';
                        if (initialDate) this.calendar.gotoDate(new Date(initialDate));

                        setTimeout(() => {
                            this.suppressUrlUpdate = false
                        }, 100);

                        // Livewire bridge
                        this.$wire.on('calendar-prev', () => this.prev());
                        this.$wire.on('calendar-next', () => this.next());
                        this.$wire.on('calendar-today', () => this.today());
                        this.$wire.on('calendar-change-view', (data) => this.changeView(data.view));
                        this.$wire.on('calendar-refetch', async () => {
                            const currentDate = this.calendar.getDate();
                            const currentView = this.calendar.view.type;
                            const updated = await this.$wire.getUpdatedEvents();
                            // Update component's allEvents state
                            this.allEvents = updated || [];
                            // Refetch events to apply view-based filtering
                            this.calendar.refetchEvents();
                            this.calendar.gotoDate(currentDate);
                            this.calendar.changeView(currentView);
                            this.updateTitle();
                        });

                        // Responsive resize handling
                        let resizeTimer;
                        const handleResize = () => {
                            clearTimeout(resizeTimer);
                            resizeTimer = setTimeout(() => {
                                // Update height for different screen sizes
                                const newHeight = this.getHeight();
                                this.calendar.setOption('height', newHeight);
                                // Call updateSize to recalculate calendar layout and adjust to container
                                this.calendar.updateSize();
                            }, 250);
                        };
                        window.addEventListener('resize', handleResize);
                        // Handle orientation change on mobile devices
                        window.addEventListener('orientationchange', () => {
                            setTimeout(() => {
                                handleResize();
                            }, 100);
                        });
                    },
                    updateTitle() {
                        const el = document.getElementById('calendar-title');
                        if (el && this.calendar) el.textContent = this.calendar.view.title;
                    },
                    prev() {
                        this.calendar.prev();
                        this.updateTitle()
                    },
                    next() {
                        this.calendar.next();
                        this.updateTitle()
                    },
                    today() {
                        this.calendar.today();
                        this.updateTitle()
                    },
                    changeView(v) {
                        this.currentView = v;
                        this.calendar.changeView(v);
                        this.updateTitle();
                    },
                    openEventModal(id) {
                        // Extract original event_id from compound IDs (event_id_span or event_id_recur)
                        // For example: "123_span" or "123_recur" -> "123"
                        let eventId = id;
                        if (typeof id === 'string' && id.includes('_')) {
                            const lastUnderscore = id.lastIndexOf('_');
                            const suffix = id.substring(lastUnderscore + 1);
                            if (suffix === 'span' || suffix === 'recur') {
                                // Extract everything before the last underscore
                                eventId = id.substring(0, lastUnderscore);
                                // Convert to number if it's numeric
                                const numericId = parseInt(eventId, 10);
                                if (!isNaN(numericId)) {
                                    eventId = numericId;
                                }
                            }
                        }

                        this.$dispatch('open-event', {
                            id: eventId
                        })
                    },
                }
            }

            window.filterPanel = function(init) {
                return {
                    open: false,
                    status: init.initialStatus || 'approved',
                    org: init.initialOrg || '',
                    etype: init.initialType || '',
                    toast: {
                        show: false,
                        message: ''
                    },
                    init() {
                        this.syncStore()
                    },
                    syncStore() {
                        if (window.Alpine) {
                            if (!Alpine.store('filters')) {
                                Alpine.store('filters', {
                                    status: this.status,
                                    org: this.org,
                                    etype: this.etype
                                });
                            } else {
                                Alpine.store('filters').status = this.status;
                                Alpine.store('filters').org = this.org;
                                Alpine.store('filters').etype = this.etype;
                            }
                        }
                    },
                    showToast(msg) {
                        this.toast.message = msg;
                        this.toast.show = true;
                        setTimeout(() => this.toast.show = false, 2000);
                    },
                    summaryLabel() {
                        const parts = [];
                        // Only include status if it's actually filtered (not empty)
                        if (this.status) {
                            parts.push(this.status === 'approved' ? 'Approved' : 'Rescheduled');
                        }
                        // Only include org if it's actually filtered (not empty)
                        if (this.org) {
                            parts.push('Selected Org');
                        }
                        // Only include event type if it's actually filtered (not empty)
                        if (this.etype) {
                            parts.push('Selected Type');
                        }
                        // Return message only if there are active filters
                        return parts.length > 0 ? `Filters applied: ${parts.join(' · ')}` : 'No filters applied';
                    },
                    async apply() {
                        // Instant toast
                        this.showToast(this.summaryLabel());
                        this.open = false;
                        this.syncStore();
                        // Single roundtrip: set filters server-side and fetch events
                        const updated = await this.$wire.setFiltersAndGetEvents(this.status, this.org, this.etype);
                        // Update calendar component's events state and refetch
                        if (window.osaCalendarInstance) {
                            window.osaCalendarInstance.allEvents = updated || [];
                            window.osaCalendarInstance.calendar.refetchEvents();
                        } else if (window.fullCalendar) {
                            // Fallback: update global calendar if component instance not available
                            const currentDate = window.fullCalendar.getDate();
                            const currentView = window.fullCalendar.view.type;
                            window.fullCalendar.removeAllEvents();
                            if (updated && updated.length) window.fullCalendar.addEventSource(updated);
                            window.fullCalendar.gotoDate(currentDate);
                            window.fullCalendar.changeView(currentView);
                        } else {
                            // Fallback: trigger existing refetch behavior
                            this.$wire.dispatch('calendar-refetch');
                        }
                    },
                    async clearAll() {
                        this.status = 'approved';
                        this.org = '';
                        this.etype = '';
                        this.showToast('All filters cleared');
                        this.open = false;
                        this.syncStore();
                        const updated = await this.$wire.setFiltersAndGetEvents(this.status, this.org, this.etype);
                        // Update calendar component's events state and refetch
                        if (window.osaCalendarInstance) {
                            window.osaCalendarInstance.allEvents = updated || [];
                            window.osaCalendarInstance.calendar.refetchEvents();
                        } else if (window.fullCalendar) {
                            // Fallback: update global calendar if component instance not available
                            const currentDate = window.fullCalendar.getDate();
                            const currentView = window.fullCalendar.view.type;
                            window.fullCalendar.removeAllEvents();
                            if (updated && updated.length) window.fullCalendar.addEventSource(updated);
                            window.fullCalendar.gotoDate(currentDate);
                            window.fullCalendar.changeView(currentView);
                        } else {
                            this.$wire.dispatch('calendar-refetch');
                        }
                    }
                }
            }
        </script>
        <script>
            window.eventDetailsModal = function() {
                return {
                    open: false,
                    loading: false,
                    data: null,
                    // simple in-memory cache: id -> data or Promise
                    cache: {},
                    async openById(id) {
                        this.open = true;
                        const cached = this.cache[id];
                        if (cached) {
                            // cached may be data or a pending Promise
                            if (cached.then) {
                                this.loading = true;
                                try {
                                    const details = await cached;
                                    this.data = details || null;
                                } catch (e) {
                                    console.error('Failed to load event details', e);
                                } finally {
                                    this.loading = false;
                                }
                            } else {
                                this.data = cached;
                                this.loading = false;
                            }
                            return;
                        }

                        this.loading = true;
                        this.data = null;
                        // store the pending promise to dedupe concurrent requests
                        const promise = this.$wire.getEventDetails(id)
                            .then((details) => {
                                this.cache[id] = details || null;
                                return this.cache[id];
                            })
                            .catch((e) => {
                                console.error('Failed to load event details', e);
                                // bust cache entry on error
                                delete this.cache[id];
                                return null;
                            });

                        this.cache[id] = promise;

                        try {
                            const details = await promise;
                            this.data = details;
                        } finally {
                            this.loading = false;
                        }
                    },
                    close() {
                        this.open = false;
                        this.data = null;
                        this.loading = false;
                    },
                    formatDate(d) {
                        if (!d) return '—';
                        try {
                            const dt = new Date(d);
                            return dt.toLocaleDateString(undefined, {
                                month: 'short',
                                day: '2-digit',
                                year: 'numeric'
                            });
                        } catch (_) {
                            return d;
                        }
                    },
                    timeRange(a, b) {
                        if (!a && !b) return '—';

                        // Format time from HH:MM:SS to readable format (e.g., "8:00 AM")
                        const formatTime = (timeStr) => {
                            if (!timeStr) return '';
                            try {
                                // Parse HH:MM:SS format
                                const [hours, minutes] = timeStr.split(':');
                                const h = parseInt(hours, 10);
                                const m = parseInt(minutes, 10);

                                // Convert to 12-hour format
                                const period = h >= 12 ? 'PM' : 'AM';
                                const hour12 = h % 12 || 12;

                                return `${hour12}:${m.toString().padStart(2, '0')} ${period}`;
                            } catch (e) {
                                return timeStr; // Return as-is if parsing fails
                            }
                        };

                        const startFormatted = formatTime(a);
                        const endFormatted = formatTime(b);

                        return `${startFormatted}${(startFormatted && endFormatted) ? ' - ' : ''}${endFormatted}`;
                    }
                }
            }
        </script>
    @endpush
</div>
