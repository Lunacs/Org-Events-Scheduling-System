<div x-data="{ firstLoad: true, isOpen: false, currentId: @entangle('selectedEventId'), cached: {} }" x-init="$nextTick(() => firstLoad = false)">

    {{-- Skeleton Loading State (First Load Only) --}}
    <div x-show="firstLoad" x-cloak>
        @include('livewire.osa.placeholders.ticket-management')
    </div>

    {{-- Actual Content --}}
    <div x-show="!firstLoad" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">

        {{-- Header --}}
        <section
            class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm mb-8">
            <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-heading font-bold text-base-content">Archive Access</h1>
                        <p class="text-base-content/70 mt-1">View past events and historical decisions</p>
                    </div>
                    <div class="flex items-center gap-2 relative z-10">
                        <span class="badge badge-neutral">{{ $archivedEvents->total() }} Archived Events</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Filters --}}
        <x-ui.card>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <x-ui.input wire:model.defer="search" placeholder="Search events..." icon="s-magnifying-glass"
                    class="md:col-span-2" />

                <x-ui.select wire:model.defer="organizationFilter" placeholder="Organization" :options="$organizations"
                    option-value="org_id" option-label="org_name" />

                <x-ui.select wire:model.defer="yearFilter" placeholder="Year" :options="$availableYears->map(fn($year) => ['id' => $year, 'name' => $year])" />

                <div class="flex gap-2">
                    <x-ui.button wire:click="applyFilters" class="btn-primary flex-1" label="Apply" />
                    @if ($search !== '' || $statusFilter !== '' || $organizationFilter !== '' || $yearFilter != \Carbon\Carbon::now()->year)
                        <x-ui.button wire:click="clearFilters" class="btn-ghost" icon="s-x-mark"
                            tooltip="Clear Filters" />
                    @endif
                </div>
            </div>
        </x-ui.card>

        {{-- Archive Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <x-ui.metric-card label="Completed" value="{{ $archivedEvents->where('ticket.status', 'completed')->count() }}"
                icon="s-check-circle" color="primary" />

            <x-ui.metric-card label="Organizations"
                value="{{ $archivedEvents->pluck('ticket.user.org_id')->filter()->unique()->count() }}"
                icon="s-building-office" color="info" />
        </div>

        {{-- Archived Events List --}}
        <div class="bg-base-100 border border-base-300 rounded-box shadow-sm overflow-hidden">
            {{-- Skeleton Loading State during filter operations --}}
            <div wire:loading.delay
                wire:target="applyFilters,clearFilters,search,statusFilter,organizationFilter,yearFilter,eventTypeFilter">
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
                        <tbody class="animate-pulse">
                            @for ($i = 0; $i < 5; $i++)
                                <tr>
                                    <td>
                                        <div class="space-y-2">
                                            <div class="h-4 bg-base-200 rounded w-3/4"></div>
                                            <div class="h-3 bg-base-200 rounded w-full"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="h-4 bg-base-200 rounded w-32"></div>
                                    </td>
                                    <td>
                                        <div class="h-4 bg-base-200 rounded w-24"></div>
                                    </td>
                                    <td>
                                        <div class="h-6 bg-base-200 rounded w-20"></div>
                                    </td>
                                    <td>
                                        <div class="h-4 bg-base-200 rounded w-28"></div>
                                    </td>
                                    <td>
                                        <div class="h-8 bg-base-200 rounded w-16"></div>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Actual Content --}}
            <div wire:loading.remove.delay
                wire:target="applyFilters,clearFilters,search,statusFilter,organizationFilter,yearFilter,eventTypeFilter">
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
                                            @if ($event->ticket->venue_requested || $event->eventSchedules?->first()?->venue)
                                                <div class="text-xs text-base-content/60 flex items-center gap-1 mt-1">
                                                    {{ $event->ticket->venue_requested ?? $event->eventSchedules?->first()?->venue }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 shrink-0">
                                                <img src="{{ $event->ticket->user->studentOrganization->logo_url }}"
                                                    alt="{{ $event->ticket->user->studentOrganization->org_name }} logo"
                                                    class="w-8 h-8 object-cover rounded-full bg-base-200">
                                            </div>
                                            <div>
                                                <div class="font-medium">
                                                    {{ $event->ticket->user->studentOrganization->org_name ?? 'No Organization' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($event->eventSchedules?->first())
                                            <div>
                                                {{ \Carbon\Carbon::parse($event->eventSchedules->first()->start_date)->format('M d, Y') }}
                                            </div>
                                            <div class="text-sm text-base-content/70">
                                                {{ $event->eventSchedules->first()->start_time }}
                                            </div>
                                        @else
                                            <span class="text-base-content/50">TBD</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'approved' => 'badge-success',
                                                'for_revision' => 'badge-error',
                                                'completed' => 'badge-primary',
                                            ];
                                        @endphp
                                        <span
                                            class="badge {{ $statusClasses[$event->ticket->status] ?? 'badge-neutral' }} text-white">{{ ucfirst($event->ticket->status) }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $event->ticket->updated_at->format('M d, Y') }}</div>
                                        <div class="text-sm text-base-content/70">
                                            {{ $event->ticket->updated_at->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-1">
                                            <button
                                                @click="isOpen = true; $wire.viewArchivedEvent({{ $event->event_id }});"
                                                type="button" class="btn btn-sm btn-ghost"
                                                title="View Details">View</button>
                                            @if ($event->ticket_attachments_count > 0)
                                                <span class="badge badge-ghost">{{ $event->ticket_attachments_count }}
                                                    attachments</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="text-base-content/70">No archived events found</span>
                                            <span class="text-sm text-base-content/50">Try adjusting your
                                                filters</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-tickets.ticket-pagination :items="$archivedEvents" label="events" />
            </div>
        </div>

        {{-- Event Details Modal (DaisyUI via Alpine.js for show/hide) --}}
        <div x-cloak x-show="isOpen" x-transition.opacity x-transition.duration.200ms class="modal"
            :class="{ 'modal-open': isOpen }">
            <div class="modal-box max-w-4xl">
                <h3 class="font-bold text-lg">Archived Event Details</h3>
                <div class="py-4">
                    @if ($selectedEventId)
                        <livewire:osa.archived-event-details :event-id="$selectedEventId"
                            wire:key="archived-event-{{ $selectedEventId }}" />
                    @else
                        <div class="space-y-3">
                            <div class="h-6 bg-base-200 rounded animate-pulse"></div>
                            <div class="h-4 bg-base-200 rounded animate-pulse"></div>
                            <div class="h-4 bg-base-200 rounded animate-pulse w-3/4"></div>
                        </div>
                    @endif
                </div>
                <div class="modal-action">
                    <button class="btn btn-ghost" @click="isOpen = false; $wire.closeModal()">Close</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="isOpen = false; $wire.closeModal()"></div>
        </div>
    </div>
</div>
