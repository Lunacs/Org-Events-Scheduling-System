<div>
    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Archive Access</h1>
                    <p class="text-base-content/70 mt-1">View past events and historical decisions</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-mary-badge value="{{ $archivedEvents->total() }} Archived Events" class="badge-neutral" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-mary-input wire:model.live.debounce.300ms="search" placeholder="Search events..."
                icon="o-magnifying-glass" clearable />

            <x-mary-select wire:model.live="statusFilter" placeholder="Status" :options="[
                ['id' => '', 'name' => 'All Statuses'],
                ['id' => 'approved', 'name' => 'Approved'],
                ['id' => 'rejected', 'name' => 'Rejected'],
                ['id' => 'completed', 'name' => 'Completed'],
            ]" option-value="id"
                option-label="name" />

            <x-mary-select wire:model.live="organizationFilter" placeholder="Organization" :options="App\Models\Student_Organization::select('org_id', 'org_name')->get()"
                option-value="org_id" option-label="org_name" />

            <x-mary-select wire:model.live="yearFilter" placeholder="Year" :options="$availableYears->map(fn($year) => ['id' => $year, 'name' => $year])" option-value="id"
                option-label="name" />

            <x-mary-button wire:click="clearFilters" class="btn-ghost" icon="o-x-mark">
                Clear Filters
            </x-mary-button>
        </div>
    </div>

    {{-- Archive Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-base-100 rounded-box shadow-lg p-4">
            <div class="flex items-center gap-3">
                <div class="bg-success/10 p-2 rounded-full">
                    <x-mary-icon name="o-check-circle" class="w-5 h-5 text-success" />
                </div>
                <div>
                    <div class="text-lg font-bold">{{ $archivedEvents->where('ticket.status', 'approved')->count() }}
                    </div>
                    <div class="text-sm text-base-content/70">Approved</div>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-box shadow-lg p-4">
            <div class="flex items-center gap-3">
                <div class="bg-error/10 p-2 rounded-full">
                    <x-mary-icon name="o-x-circle" class="w-5 h-5 text-error" />
                </div>
                <div>
                    <div class="text-lg font-bold">{{ $archivedEvents->where('ticket.status', 'rejected')->count() }}
                    </div>
                    <div class="text-sm text-base-content/70">Rejected</div>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-box shadow-lg p-4">
            <div class="flex items-center gap-3">
                <div class="bg-primary/10 p-2 rounded-full">
                    <x-mary-icon name="o-calendar-days" class="w-5 h-5 text-primary" />
                </div>
                <div>
                    <div class="text-lg font-bold">{{ $archivedEvents->where('ticket.status', 'completed')->count() }}
                    </div>
                    <div class="text-sm text-base-content/70">Completed</div>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-box shadow-lg p-4">
            <div class="flex items-center gap-3">
                <div class="bg-info/10 p-2 rounded-full">
                    <x-mary-icon name="o-building-office" class="w-5 h-5 text-info" />
                </div>
                <div>
                    <div class="text-lg font-bold">
                        {{ $archivedEvents->pluck('ticket.user.org_id')->filter()->unique()->count() }}</div>
                    <div class="text-sm text-base-content/70">Organizations</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Archived Events List --}}
    <div class="bg-base-100 rounded-box shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200">
                    <tr>
                        <th>Event Details</th>
                        <th>Organization</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Decision Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archivedEvents as $event)
                        <tr class="hover">
                            <td>
                                <div>
                                    <div class="font-semibold">{{ $event->ticket->title }}</div>
                                    <div class="text-sm text-base-content/70">
                                        {{ Str::limit($event->ticket->description, 60) }}
                                    </div>
                                    @if ($event->ticket->venue_requested || $event->eventSchedules->first()?->venue)
                                        <div class="text-xs text-base-content/60 flex items-center gap-1 mt-1">
                                            <x-mary-icon name="o-map-pin" class="w-3 h-3" />
                                            {{ $event->ticket->venue_requested ?? $event->eventSchedules->first()->venue }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content rounded-full w-8">
                                            <span
                                                class="text-xs">{{ $event->ticket->user->studentOrganization ? substr($event->ticket->user->studentOrganization->org_name, 0, 2) : 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">
                                            {{ $event->ticket->user->studentOrganization->org_name ?? 'No Organization' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($event->event_schedules->first())
                                    <div>
                                        {{ \Carbon\Carbon::parse($event->event_schedules->first()->start_date)->format('M d, Y') }}
                                    </div>
                                    <div class="text-sm text-base-content/70">
                                        {{ $event->event_schedules->first()->start_time }}</div>
                                @else
                                    <span class="text-base-content/50">TBD</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'approved' => 'badge-success',
                                        'rejected' => 'badge-error',
                                        'completed' => 'badge-primary',
                                    ];
                                @endphp
                                <x-mary-badge value="{{ ucfirst($event->ticket->status) }}"
                                    class="{{ $statusClasses[$event->ticket->status] ?? 'badge-neutral' }}" />
                            </td>
                            <td>
                                <div>{{ $event->ticket->updated_at->format('M d, Y') }}</div>
                                <div class="text-sm text-base-content/70">
                                    {{ $event->ticket->updated_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <x-mary-button wire:click="viewArchivedEvent({{ $event->event_id }})"
                                        icon="o-eye" class="btn-sm btn-ghost" tooltip="View Details" />
                                    @if ($event->ticket->attachments->count() > 0)
                                        <x-mary-button icon="o-paper-clip" class="btn-sm btn-ghost"
                                            tooltip="{{ $event->ticket->attachments->count() }} attachments" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <div class="flex flex-col items-center gap-2">
                                    <x-mary-icon name="o-archive-box" class="w-12 h-12 text-base-content/30" />
                                    <span class="text-base-content/70">No archived events found</span>
                                    <span class="text-sm text-base-content/50">Try adjusting your filters</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($archivedEvents->hasPages())
            <div class="p-4 border-t border-base-300">
                {{ $archivedEvents->links() }}
            </div>
        @endif
    </div>

    {{-- Event Details Modal --}}
    <x-mary-modal wire:model="showModal" title="Archived Event Details" class="modal-lg">
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
                        @php
                            $statusClasses = [
                                'approved' => 'badge-success',
                                'rejected' => 'badge-error',
                                'completed' => 'badge-primary',
                            ];
                        @endphp
                        <x-mary-badge value="{{ ucfirst($selectedEvent->ticket->status) }}"
                            class="{{ $statusClasses[$selectedEvent->ticket->status] ?? 'badge-neutral' }}" />
                    </div>
                </div>

                {{-- Event Information --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold mb-3">Event Details</h3>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="font-medium text-base-content/70">Event Type:</span>
                                <span>{{ $selectedEvent->eventType?->type_name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-base-content/70">Expected Attendees:</span>
                                <span>{{ $selectedEvent->ticket->total_participants ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-base-content/70">Venue:</span>
                                <span>{{ $selectedEvent->ticket->venue_requested ?? ($selectedEvent->eventSchedules->first()?->venue ?? 'N/A') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-base-content/70">Submitted:</span>
                                <span>{{ $selectedEvent->ticket->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold mb-3">Decision History</h3>
                        <div class="space-y-3">
                            @if ($selectedEvent->ticket->osaApproval)
                                <div class="bg-base-200 rounded-lg p-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <x-mary-icon name="o-user-circle" class="w-4 h-4 text-primary" />
                                        <span class="font-medium">OSA Decision</span>
                                    </div>
                                    <div class="text-sm space-y-1">
                                        <div>
                                            <span class="font-medium">Status:</span>
                                            <x-mary-badge
                                                value="{{ ucfirst($selectedEvent->ticket->osaApproval->status) }}"
                                                class="{{ $selectedEvent->ticket->osaApproval->status === 'approved' ? 'badge-success' : 'badge-error' }} badge-sm" />
                                        </div>
                                        <div>
                                            <span class="font-medium">Date:</span>
                                            {{ $selectedEvent->ticket->osaApproval->approved_at?->format('M d, Y h:i A') }}
                                        </div>
                                        @if ($selectedEvent->ticket->osaApproval->comments)
                                            <div>
                                                <span class="font-medium">Comments:</span>
                                                <p class="mt-1 text-base-content/80">
                                                    {{ $selectedEvent->ticket->osaApproval->comments }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Schedule --}}
                @if ($selectedEvent->event_schedules->count() > 0)
                    <div>
                        <h3 class="font-semibold mb-3">Event Schedule</h3>
                        <div class="space-y-2">
                            @foreach ($selectedEvent->event_schedules as $schedule)
                                <div class="bg-base-200 rounded-lg p-3">
                                    <div class="flex items-center gap-2 text-sm">
                                        <x-mary-icon name="o-calendar-days" class="w-4 h-4 text-primary" />
                                        <span>{{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }}</span>
                                        <x-mary-icon name="o-clock" class="w-4 h-4 text-primary ml-4" />
                                        <span>{{ $schedule->start_time }} - {{ $schedule->end_time }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Description --}}
                @if ($selectedEvent->ticket->description)
                    <div>
                        <h3 class="font-semibold mb-3">Description</h3>
                        <p class="text-sm text-base-content/80 bg-base-200 rounded-lg p-4">
                            {{ $selectedEvent->ticket->description }}</p>
                    </div>
                @endif

                {{-- Attachments --}}
                @if ($selectedEvent->ticket->attachments->count() > 0)
                    <div>
                        <h3 class="font-semibold mb-3">Attachments
                            ({{ $selectedEvent->ticket->attachments->count() }})</h3>
                        <div class="space-y-2">
                            @foreach ($selectedEvent->ticket->attachments as $attachment)
                                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <x-mary-icon name="o-document" class="w-5 h-5 text-primary" />
                                        <div>
                                            <p class="font-medium">{{ $attachment->original_name }}</p>
                                            <p class="text-sm text-base-content/70">{{ $attachment->file_size }} •
                                                {{ $attachment->file_type }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-mary-button class="btn-sm btn-ghost" icon="o-eye" tooltip="Preview" />
                                        <x-mary-button class="btn-sm btn-ghost" icon="o-arrow-down-tray"
                                            tooltip="Download" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:actions>
            <x-mary-button wire:click="closeModal" class="btn-ghost">Close</x-mary-button>
        </x-slot:actions>
    </x-mary-modal>
</div>
