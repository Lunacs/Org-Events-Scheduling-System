<div>
    {{-- Header with Admin Actions --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Event Calendar - SuperAdmin</h1>
                    <p class="text-base-content/70 mt-1">Manage all events across all organizations</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-mary-badge value="{{ $uniqueEventsCount }} Events" class="badge-primary" />
                    <x-mary-button icon="o-arrow-path" class="btn-outline btn-sm" wire:click.async="$refresh">
                        Refresh
                    </x-mary-button>
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
                    <div x-data x-init="if (window.Alpine && !Alpine.store('filters')) { Alpine.store('filters', { status: '{{ $statusFilter }}', org: '', etype: '' }) }" class="relative">
                        {{-- Notification Badge --}}
                        <div x-show="($store.filters?.status && $store.filters?.status !== 'all') || $store.filters?.org || $store.filters?.etype"
                            class="absolute -top-2 -right-2 z-10">
                            <div
                                class="badge badge-primary badge-sm h-5 w-5 p-0 flex items-center justify-center text-neutral-content text-xs font-bold">
                                <span
                                    x-text="[($store.filters?.status && $store.filters?.status !== 'all' ? $store.filters?.status : null), $store.filters?.org, $store.filters?.etype].filter(v => v).length"></span>
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

    {{-- SuperAdmin Filter Drawer (Alpine-controlled) --}}
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
                        {{-- Status Filter - SuperAdmin can see "All" --}}
                        <div>
                            <label class="label">
                                <span class="label-text font-semibold">Status</span>
                            </label>
                            <select x-model="status" class="select select-bordered w-full">
                                <option value="all">All Statuses</option>
                                <option value="approved">Approved</option>
                                <option value="rescheduled">Rescheduled</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
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
                            if ($statusFilter && $statusFilter !== 'all') {
                                $statusLabel =
                                    collect([
                                        ['id' => 'approved', 'name' => 'Approved'],
                                        ['id' => 'rescheduled', 'name' => 'Rescheduled'],
                                        ['id' => 'pending', 'name' => 'Pending'],
                                        ['id' => 'cancelled', 'name' => 'Cancelled'],
                                    ])->firstWhere('id', $statusFilter)['name'] ?? ucfirst($statusFilter);
                                $activeFilters[] = $statusLabel;
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
                                'badge-info': (data?.status || '') === 'pending',
                                'badge-error': (data?.status || '') === 'cancelled',
                                'badge-primary': ['approved', 'rescheduled', 'pending', 'cancelled'].indexOf(data
                                    ?.status || '') === -1
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
        @include('livewire.components.event-calendar-scripts')
    @endpush

    {{-- SuperAdmin Action Modal --}}
    <x-mary-modal wire:model="showActionModal" title="Event Action" persistent>
        @if ($selectedEventForAction)
            <div class="space-y-4">
                <div class="alert alert-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>This action cannot be undone!</span>
                </div>

                <div>
                    <p class="font-semibold">Event: {{ $selectedEventForAction->ticket->title ?? 'N/A' }}</p>
                    <p class="text-sm text-base-content/60">Ticket:
                        {{ $selectedEventForAction->ticket->ticket_number ?? 'N/A' }}</p>
                </div>

                @if ($actionType === 'approve')
                    <p>Force approve this event and all its schedules?</p>
                @elseif($actionType === 'cancel')
                    <p>Cancel this event? This will mark all schedules as cancelled.</p>
                @elseif($actionType === 'delete')
                    <p class="text-error font-semibold">Permanently delete this event and all associated data?</p>
                @endif
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.closeActionModal()" />

                @if ($actionType === 'approve')
                    <x-mary-button label="Force Approve" class="btn-success"
                        wire:click="forceApproveEvent({{ $selectedEventForAction->event_id }})"
                        spinner="forceApproveEvent" />
                @elseif($actionType === 'cancel')
                    <x-mary-button label="Cancel Event" class="btn-warning"
                        wire:click="cancelEvent({{ $selectedEventForAction->event_id }})" spinner="cancelEvent" />
                @elseif($actionType === 'delete')
                    <x-mary-button label="Delete Permanently" class="btn-error"
                        wire:click="forceDeleteEvent({{ $selectedEventForAction->event_id }})"
                        spinner="forceDeleteEvent" />
                @endif
            </x-slot:actions>
        @endif
    </x-mary-modal>
</div>
