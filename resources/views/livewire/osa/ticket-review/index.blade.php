<div x-data="{
    showFilters: true,
    hasActiveFilters: @entangle('search').live || @entangle('statusFilter').live
}">
    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Ticket Review & Approvals</h1>
                    <p class="text-base-content/70 mt-1">Review event proposals and final approvals</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-primary">
                        <span wire:loading.remove wire:target="search,statusFilter">{{ $tickets->total() }}
                            Tickets</span>
                        <span wire:loading wire:target="search,statusFilter"
                            class="loading loading-spinner loading-xs"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tickets..."
                    class="input input-bordered w-full pr-10" />
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <span wire:loading.remove wire:target="search">
                        <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <span wire:loading wire:target="search" class="loading loading-spinner loading-sm"></span>
                </div>
            </div>

            <div class="relative">
                <select wire:model.live="statusFilter" class="select select-bordered w-full">
                    <option value="">All Statuses</option>
                    <option value="received">Received</option>
                    <option value="gso_review">GSO Review</option>
                    <option value="pending_osa_approval">Pending Final Approval</option>
                    <option value="for_rescheduling">For Rescheduling</option>
                    <option value="rescheduled">Rescheduled</option>
                    <option value="needs_revision">Needs Revision</option>
                    <option value="amended">Amended</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <div class="absolute inset-y-0 right-10 flex items-center pointer-events-none">
                    <span wire:loading wire:target="statusFilter" class="loading loading-spinner loading-sm"></span>
                </div>
            </div>

            <div class="flex gap-2">
                <button wire:click="clearFilters" type="button" class="btn btn-ghost flex-1"
                    x-show="$wire.search || $wire.statusFilter" x-transition wire:loading.attr="disabled"
                    wire:target="clearFilters">
                    <svg wire:loading.remove wire:target="clearFilters" class="w-4 h-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    <span wire:loading.remove wire:target="clearFilters">Clear Filters</span>
                    <span wire:loading wire:target="clearFilters" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Tickets Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 relative min-h-[400px]">
        {{-- Loading Overlay --}}
        <div wire:loading.flex wire:target="search,statusFilter,clearFilters"
            class="absolute inset-0 bg-base-100/60 backdrop-blur-sm z-10 items-center justify-center rounded-box">
            <div class="text-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="mt-2 text-sm text-base-content/70">Loading tickets...</p>
            </div>
        </div>

        @forelse($tickets as $ticket)
            <div class="flex flex-col bg-base-100 rounded-box shadow-lg overflow-hidden hover:shadow-xl hover:ring-2 ring-primary/20 transition-all duration-200"
                wire:key="ticket-review-{{ $ticket->ticket_id }}" x-data="{ isHovered: false }" @mouseenter="isHovered = true"
                @mouseleave="isHovered = false">
                <div class="p-6 flex-1 flex flex-col">
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1 min-h-18">
                            <h3 class="font-bold text-lg text-base-content line-clamp-2">{{ $ticket->title }}</h3>
                            <p class="text-sm text-base-content/70 mt-1">
                                {{ $ticket->user->studentOrganization->org_name ?? 'No Organization' }}</p>
                        </div>
                        @php
                            $statusClasses = [
                                'received' => 'badge-info',
                                'gso_review' => 'badge-secondary',
                                'pending_osa_approval' => 'badge-warning',
                                'for_rescheduling' => 'badge-warning',
                                'rescheduled' => 'badge-success',
                                'needs_revision' => 'badge-warning',
                                'amended' => 'badge-info',
                                'approved' => 'badge-success',
                                'rejected' => 'badge-error',
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
                                <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span
                                    class="text-base-content/80">{{ $ticket->date_from ? \Carbon\Carbon::parse($ticket->date_from)->format('M d, Y') : 'TBD' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-base-content/80">{{ $ticket->venue_requested ?? 'TBD' }}</span>
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
                                class="btn btn-primary btn-sm flex-1 group" wire:navigate title="Review Ticket">
                                <span>Review</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1"
                                    :class="{ 'translate-x-1': isHovered }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
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
                            <span x-show="$wire.search || $wire.statusFilter">Try adjusting your filters</span>
                            <span x-show="!$wire.search && !$wire.statusFilter">No tickets have been submitted
                                yet</span>
                        </p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($tickets->hasPages())
        <div class="mt-6" wire:key="pagination-{{ $tickets->currentPage() }}">
            {{ $tickets->links() }}
        </div>
    @endif

</div>
