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
    {{-- niga --}}
    {{-- Calendar Component --}}
    <x-calendar.full-calendar :events="$events" :view-mode="$viewMode" on-event-click="viewEvent" calendar-id="osa-calendar"
        update-event="osa-calendar-updated" :show-filters="true"
        wire:key="osa-calendar-{{ $statusFilter }}-{{ $organizationFilter }}-{{ $eventTypeFilter }}">

        <x-slot:filterSlot>
            <div class="flex flex-wrap gap-2">
                <x-mary-select wire:model.live="statusFilter" placeholder="Status" :options="[
                    ['id' => 'approved', 'name' => 'Approved'],
                    ['id' => 'rescheduled', 'name' => 'Rescheduled'],
                ]" option-value="id"
                    option-label="name" class="select-sm min-w-[140px]" />

                <x-mary-select wire:model.live="organizationFilter" placeholder="Organization" :options="$organizations"
                    option-value="org_id" option-label="org_name"
                    class="select-sm text-xs min-w-[200px] max-w-[300px]" />

                <x-mary-select wire:model.live="eventTypeFilter" placeholder="Event Type" :options="$eventTypes"
                    option-value="event_type_id" option-label="type_name" class="select-sm min-w-[150px]" />

                <x-mary-button wire:click="clearFilters" class="btn-ghost btn-sm" icon="o-x-mark"
                    tooltip="Clear Filters" />
            </div>
        </x-slot:filterSlot>
    </x-calendar.full-calendar>

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
</div>
