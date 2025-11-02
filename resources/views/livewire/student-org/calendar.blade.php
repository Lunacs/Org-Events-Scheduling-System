<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Event Calendar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Calendar Header --}}
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">University Event Calendar</h3>
                    <p class="text-sm text-gray-600">View all approved events to avoid scheduling conflicts</p>
                </div>
                <div class="flex space-x-3">
                    <x-mary-button label="Submit New Ticket" icon="s-document-plus" class="btn-primary"
                        link="/student-org/submit-ticket" wire:navigate />
                    <x-mary-button label="My Tickets" icon="s-ticket" class="btn-secondary"
                        link="/student-org/my-tickets" wire:navigate />
                </div>
            </div>

            {{-- Info Banner --}}
            <x-mary-card>
                <div class="bg-info/10 p-4 rounded-lg border-l-4 border-info">
                    <div class="flex items-start space-x-3">
                        <x-mary-icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5" />
                        <div class="text-sm">
                            <p class="font-medium mb-1 text-info-content">Calendar Information:</p>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li>This calendar shows only approved events from all student organizations</li>
                                <li>Use this to check for potential conflicts before submitting your event request</li>
                                <li>Events are color-coded by category (Academic, Cultural, Sports, etc.)</li>
                                <li>Click on any event to view more details</li>
                                <li>Your organization's events are highlighted with a special border</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </x-mary-card>

            <x-calendar.full-calendar
                :events="$events"
                :view-mode="$viewMode"
                on-event-click="viewEvent"
                calendar-id="student-calendar"
                update-event="student-calendar-updated"
                :show-filters="true"
                wire:key="student-calendar-{{ $eventTypeFilter }}-{{ $venueFilter }}">

                <x-slot:filterSlot>
                    <div class="flex flex-wrap gap-2">
                        <x-mary-select wire:model.live="eventTypeFilter" placeholder="Event Type" :options="$eventTypes"
                                       option-value="event_type_id" option-label="type_name" class="select-sm min-w-[150px]" />

                        <x-mary-select wire:model.live="venueFilter" placeholder="Venue" :options="$venues"
                                       option-value="id" option-label="name" class="select-sm min-w-[150px]" />

                        <x-mary-button wire:click="resetFilters" class="btn-ghost btn-sm" icon="o-x-mark" tooltip="Clear Filters" />
                    </div>
                </x-slot:filterSlot>
            </x-calendar.full-calendar>

            {{-- Legend --}}
            <x-mary-card title="Legend" subtitle="Event categories and colors">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-blue-100 border border-blue-300 rounded"></div>
                        <span class="text-sm">Cultural Events</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
                        <span class="text-sm">Sports Events</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-purple-100 border border-purple-300 rounded"></div>
                        <span class="text-sm">Academic/Workshop</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></div>
                        <span class="text-sm">Social Events</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-primary-100 border-2 border-primary-300 border-dashed rounded"></div>
                        <span class="text-sm font-medium">Your Organization</span>
                    </div>
                </div>
            </x-mary-card>

            {{-- Upcoming Events List --}}
            <x-mary-card title="Upcoming Events This Month" subtitle="Detailed list of scheduled events">
                <div class="space-y-4">
                    <div class="flex items-start space-x-4 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="s-musical-note" class="w-6 h-6 text-blue-600" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-blue-900">Fundraising Concert</h4>
                            <p class="text-sm text-blue-700 mt-1">Music Club Annual Concert for Scholarship Fund</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-blue-600">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-calendar" class="w-4 h-4" />
                                    <span>Oct 1, 2025 • 6:00 PM - 10:00 PM</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                                    <span>University Auditorium</span>
                                </span>
                            </div>
                            <x-mary-badge value="Cultural" class="badge-info mt-2" />
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 p-4 bg-green-50 rounded-lg border-l-4 border-green-400">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="s-trophy" class="w-6 h-6 text-green-600" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-green-900">Sports Festival</h4>
                            <p class="text-sm text-green-700 mt-1">Inter-Organization Sports Competition</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-green-600">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-calendar" class="w-4 h-4" />
                                    <span>Oct 8, 2025 • 8:00 AM - 5:00 PM</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                                    <span>University Gymnasium</span>
                                </span>
                            </div>
                            <x-mary-badge value="Sports" class="badge-success mt-2" />
                        </div>
                    </div>

                    <div
                        class="flex items-start space-x-4 p-4 bg-primary-50 rounded-lg border-l-4 border-primary-400 border-dashed">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="s-building-office" class="w-6 h-6 text-primary-600" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-primary-900">Your Organization Meeting</h4>
                            <p class="text-sm text-primary-700 mt-1">Annual General Assembly • Status: Under Review</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-primary-600">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-calendar" class="w-4 h-4" />
                                    <span>Oct 15, 2025 • 2:00 PM - 5:00 PM</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                                    <span>Student Center Room 201</span>
                                </span>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <x-mary-badge value="Meeting" class="badge-primary" />
                                <x-mary-badge value="Pending Approval" class="badge-warning" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 p-4 bg-purple-50 rounded-lg border-l-4 border-purple-400">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="s-academic-cap" class="w-6 h-6 text-purple-600" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-purple-900">Leadership Workshop Series</h4>
                            <p class="text-sm text-purple-700 mt-1">3-Day Skills Development Workshop</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-purple-600">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-calendar" class="w-4 h-4" />
                                    <span>Oct 20-22, 2025 • 9:00 AM - 4:00 PM</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                                    <span>Building A, Room 301</span>
                                </span>
                            </div>
                            <x-mary-badge value="Workshop" class="badge-secondary mt-2" />
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-400">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <x-mary-icon name="s-sparkles" class="w-6 h-6 text-yellow-600" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-yellow-900">Cultural Night</h4>
                            <p class="text-sm text-yellow-700 mt-1">Diversity and Culture Celebration</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-yellow-600">
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-calendar" class="w-4 h-4" />
                                    <span>Oct 25, 2025 • 7:00 PM - 11:00 PM</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                                    <span>Student Center Plaza</span>
                                </span>
                            </div>
                            <x-mary-badge value="Cultural" class="badge-warning mt-2" />
                        </div>
                    </div>
                </div>
            </x-mary-card>

            {{-- Upcoming Events List --}}
            {{-- <x-mary-card title="Upcoming Events This Month" subtitle="Detailed list of scheduled events">
                <div class="space-y-4">
                    @forelse($upcomingEvents as $schedule)
                        @php
                            $event = $schedule->event;
                            $eventColor = $this->getEventColor($event);
                            $isMyOrg = auth()->user()->org_id === $event->ticket->user->org_id;
                        @endphp
                        <div class="flex items-start space-x-4 p-4 rounded-lg border-l-4 {{ $isMyOrg ? 'bg-primary-50 border-primary-400 border-dashed' : 'bg-base-200 border-base-300' }}">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: {{ $eventColor }}20;">
                                    <x-mary-icon name="s-calendar" class="w-6 h-6" style="color: {{ $eventColor }};" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <h4 class="font-semibold">{{ $event->ticket->title }}</h4>
                                    @if($isMyOrg)
                                        <x-mary-badge value="Your Org" class="badge-primary badge-sm" />
                                    @endif
                                </div>
                                <p class="text-sm text-base-content/70 mt-1">
                                    {{ $event->ticket->user->studentOrganization->org_name ?? 'No Organization' }}
                                </p>
                                <div class="flex items-center space-x-4 mt-2 text-sm text-base-content/60">
                        <span class="flex items-center space-x-1">
                            <x-mary-icon name="s-calendar" class="w-4 h-4" />
                            <span>{{ $schedule->start_date->format('M d, Y') }} • {{ $schedule->start_time }} - {{ $schedule->end_time }}</span>
                        </span>
                                    <span class="flex items-center space-x-1">
                            <x-mary-icon name="s-map-pin" class="w-4 h-4" />
                            <span>{{ $schedule->venue ?? $event->ticket->venue_requested ?? 'TBD' }}</span>
                        </span>
                                </div>
                                <x-mary-badge value="{{ $event->eventType?->type_name ?? 'N/A' }}" class="badge-info badge-sm mt-2" />
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-base-content/50">
                            <x-mary-icon name="o-calendar-days" class="w-12 h-12 mx-auto mb-2" />
                            <p>No upcoming events scheduled</p>
                        </div>
                    @endforelse
                </div>
            </x-mary-card> --}}

        </div>
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
</div>
