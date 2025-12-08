<div x-data="{ firstLoad: true, isOpen: false, currentId: @entangle('selectedEventId'), cached: {} }" x-init="$nextTick(() => firstLoad = false)">

    {{-- Skeleton Loading State (First Load Only) --}}
    <div x-show="firstLoad" x-cloak>
        @include('livewire.osa.placeholders.ticket-management')
    </div>

    {{-- Actual Content --}}
    <div x-show="!firstLoad" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">

        {{-- Header --}}
        <div class="mb-8">
            <div class="bg-base-100 rounded-box shadow-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-heading font-bold text-base-content">Archive Access</h1>
                        <p class="text-base-content/70 mt-1">View past events and historical decisions</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge badge-neutral">{{ $archivedEvents->total() }} Archived Events</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input wire:model.defer="search" type="text" placeholder="Search events..."
                    class="input input-bordered w-full" />

                <select wire:model.defer="statusFilter" class="select select-bordered w-full">
                    <option value="">All Statuses</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Completed</option>
                </select>

                <select wire:model.defer="organizationFilter" class="select select-bordered w-full">
                    <option value="">Organization</option>
                    @foreach ($organizations as $org)
                        <option value="{{ $org->org_id }}">{{ $org->org_name }}</option>
                    @endforeach
                </select>

                <select wire:model.defer="yearFilter" class="select select-bordered w-full">
                    <option value="">Year</option>
                    @foreach ($availableYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>

                <button wire:click="applyFilters" type="button" class="btn btn-primary">Apply</button>

                <button wire:click="clearFilters" type="button" class="btn btn-ghost">Clear Filters</button>
            </div>
        </div>

        {{-- Archive Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-base-100 rounded-box shadow-lg p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 p-2 rounded-full"></div>
                    <div>
                        <div class="text-lg font-bold">
                            {{ $archivedEvents->where('ticket.status', 'approved')->count() }}
                        </div>
                        <div class="text-sm text-base-content/70">Approved</div>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 rounded-box shadow-lg p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-error/10 p-2 rounded-full"></div>
                    <div>
                        <div class="text-lg font-bold">
                            {{ $archivedEvents->where('ticket.status', 'rejected')->count() }}
                        </div>
                        <div class="text-sm text-base-content/70">Rejected</div>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 rounded-box shadow-lg p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 p-2 rounded-full"></div>
                    <div>
                        <div class="text-lg font-bold">
                            {{ $archivedEvents->where('ticket.status', 'completed')->count() }}
                        </div>
                        <div class="text-sm text-base-content/70">Completed</div>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 rounded-box shadow-lg p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 p-2 rounded-full"></div>
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
                                            <div class="avatar flex justify-center items-center">
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
                                                {{ $event->eventSchedules->first()->start_time }}</div>
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
                                        <span
                                            class="badge {{ $statusClasses[$event->ticket->status] ?? 'badge-neutral' }} text-white">{{ ucfirst($event->ticket->status) }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $event->ticket->updated_at->format('M d, Y') }}</div>
                                        <div class="text-sm text-base-content/70">
                                            {{ $event->ticket->updated_at->format('h:i A') }}</div>
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

                {{-- Pagination --}}
                @if ($archivedEvents->hasPages())
                    <div class="p-4 border-t border-base-300">
                        {{ $archivedEvents->links() }}
                    </div>
                @endif
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
