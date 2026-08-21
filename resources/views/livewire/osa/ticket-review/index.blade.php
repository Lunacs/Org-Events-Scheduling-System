<div x-data="{
    firstLoad: true,
    showFilters: true,
    hasActiveFilters: @entangle('search').live || @entangle('statusFilter').live || @entangle('organizationFilter').live
}" x-init="$nextTick(() => firstLoad = false)">

    {{-- Skeleton Loading State (First Load Only) --}}
    <div x-show="firstLoad" x-cloak>
        @include('livewire.osa.placeholders.ticket-review')
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
                        <h1 class="text-3xl font-heading font-bold text-base-content">Ticket Review & Approvals</h1>
                        <p class="text-base-content/70 mt-1">Review event proposals and final approvals</p>
                    </div>
                    <div class="flex items-center gap-2 relative z-10">
                        <span class="badge badge-primary">
                            <span wire:loading.remove
                                wire:target="search,statusFilter,organizationFilter">{{ $tickets->total() }}
                                Tickets</span>
                            <span wire:loading wire:target="search,statusFilter,organizationFilter"
                                class="loading loading-spinner loading-xs"></span>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Search Filter --}}
        <x-ui.card>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search tickets..."
                    icon="s-magnifying-glass" wire:loading.class="opacity-70" wire:target="search" />

                <x-ui.select wire:model.live="statusFilter" wire:loading.class="opacity-70"
                    wire:target="statusFilter" :options="[
                        ['id' => '', 'name' => 'Active Tickets'],
                        ['id' => 'received', 'name' => 'Received'],
                        ['id' => 'gso_review', 'name' => 'GSO Review'],
                        ['id' => 'pending_osa_approval', 'name' => 'Pending Final Approval'],
                        ['id' => 'amended', 'name' => 'Amended'],
                        ['id' => 'approved', 'name' => 'Approved'],
                        ['id' => 'for_revision', 'name' => 'For Revision'],
                        ['id' => 'completed', 'name' => 'Completed'],
                    ]" />

                <x-ui.select wire:model.live="organizationFilter" wire:loading.class="opacity-70"
                    wire:target="organizationFilter" placeholder="All Organizations" :options="$this->organizations->map(fn($org) => [
                        'org_id' => $org->org_id,
                        'org_name' => $org->org_name . ($org->deleted_at ? ' (Deleted)' : ''),
                    ])" option-value="org_id" option-label="org_name" />

                <x-ui.button label="Clear Filters" icon="s-x-mark" class="btn-ghost"
                    x-show="$wire.search || $wire.statusFilter || $wire.organizationFilter" x-transition
                    wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters" />
            </div>
        </x-ui.card>

        {{-- Tickets Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 relative min-h-[400px]">
            {{-- Skeleton Loader (Filtering/Searching) --}}
            <div wire:loading wire:target="search,statusFilter,organizationFilter,clearFilters" class="col-span-full">
                @include('livewire.osa.placeholders.ticket-cards')
            </div>

            <div wire:loading.remove wire:target="search,statusFilter,organizationFilter,clearFilters"
                class="col-span-full grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($tickets as $ticket)
                    <div class="flex flex-col bg-base-100 rounded-box shadow-lg overflow-hidden hover:shadow-xl hover:ring-2 ring-primary/20 transition-all duration-200"
                        wire:key="ticket-review-{{ $ticket->ticket_id }}">
                        <div class="p-6 flex-1 flex flex-col">
                            {{-- Header --}}
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1 min-h-18">
                                    <h3 class="font-bold text-lg text-base-content line-clamp-2">{{ $ticket->title }}
                                    </h3>
                                    @php $orgDeleted = $ticket->user?->studentOrganization?->trashed(); @endphp
                                    <p class="text-sm text-base-content/70 mt-1 {{ $orgDeleted ? 'italic' : '' }}">
                                        {{ $orgDeleted ? 'Deleted Organization' : $ticket->user?->studentOrganization?->org_name ?? 'No Organization' }}
                                    </p>
                                </div>
                                @php
                                    $statusClasses = [
                                        'received' => 'badge-info',
                                        'gso_review' => 'badge-secondary',
                                        'pending_osa_approval' => 'badge-warning',
                                        'amended' => 'badge-info',
                                        'approved' => 'badge-success',
                                        'for_revision' => 'badge-warning',
                                        'completed' => 'badge-neutral',
                                    ];
                                @endphp
                                <span
                                    class="badge badge-sm {{ $statusClasses[$ticket->status] ?? 'badge-neutral' }} text-white">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </div>

                            {{-- Description --}}
                            <p class="text-sm text-base-content/80 mb-4 line-clamp-3">{{ $ticket->description }}</p>

                            {{-- Event Details --}}
                            <div class="space-y-2 mb-4">
                                @if ($ticket->events->isNotEmpty() && $ticket->events->first()->eventSchedules->isNotEmpty())
                                    {{-- Show approved event schedule --}}
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <span
                                            class="text-success font-medium">{{ $ticket->events->first()->eventSchedules->first()->start_date?->format('M d, Y') ?? 'TBD' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span
                                            class="text-success font-medium">{{ $ticket->events->first()->eventSchedules->first()->venue ?? 'TBD' }}</span>
                                    </div>
                                @else
                                    {{-- Show requested dates --}}
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-base-content/60" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <span
                                            class="text-base-content/80">{{ $ticket->date_from ? \Carbon\Carbon::parse($ticket->date_from)->format('M d, Y') : 'TBD' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-base-content/60" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span
                                            class="text-base-content/80">{{ $ticket->venue_display_name ?? 'TBD' }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Spacer to push bottom content down --}}
                            <div class="flex-1"></div>

                            {{-- Bottom Section --}}
                            <div class="space-y-4">
                                {{-- Attachments Info --}}
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                        </path>
                                    </svg>
                                    <span class="text-sm text-base-content/70">
                                        {{ $ticket->attachments->count() }} attachment(s)
                                    </span>
                                </div>

                                {{-- Actions --}}
                                <div class="flex gap-2">
                                    <a href="{{ route('osa.ticket-review.show', $ticket->ticket_number) }}"
                                        class="btn btn-primary btn-sm flex-1 group" wire:navigate
                                        title="Review Ticket">
                                        <span>Review</span>
                                        <svg class="w-4 h-4 transition duration-300 ease-in-out transform group-hover:translate-x-2"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="bg-base-200 px-6 py-3 border-t border-base-300">
                            <div class="flex items-center justify-between text-xs text-base-content/70">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $ticket->created_at?->diffForHumans() ?? 'N/A' }}
                                </span>
                                <span class="font-mono text-xs">{{ $ticket->ticket_number }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <div class="flex flex-col items-center gap-4">
                            <svg class="w-16 h-16 text-base-content/20" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                </path>
                            </svg>
                            <div>
                                <h3 class="text-lg font-semibold text-base-content/70">No tickets found</h3>
                                <p class="text-sm text-base-content/50 mt-1">
                                    <span x-show="$wire.search || $wire.statusFilter || $wire.organizationFilter">Try
                                        adjusting your filters</span>
                                    <span x-show="!$wire.search && !$wire.statusFilter && !$wire.organizationFilter">No
                                        tickets have been submitted
                                        yet</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>


        </div>
        {{-- Pagination --}}
        <x-tickets.ticket-pagination :tickets="$tickets" />
    </div>
</div>
