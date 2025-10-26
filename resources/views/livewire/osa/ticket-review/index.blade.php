<div>
    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Ticket Review & Attachments</h1>
                    <p class="text-base-content/70 mt-1">Review event proposals and check attached documents</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-mary-badge value="{{ $tickets->total() }} Tickets" class="badge-primary" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-mary-input wire:model.live.debounce.300ms="search" placeholder="Search tickets..."
                icon="o-magnifying-glass" clearable />

            <x-mary-select wire:model.live="statusFilter" placeholder="Filter by Status" :options="[
                ['id' => 'pending', 'name' => 'Pending Review'],
                ['id' => 'under_review', 'name' => 'Under Review'],
                ['id' => 'pending_osa_approval', 'name' => 'Pending OSA Approval'],
                ['id' => 'approved', 'name' => 'Approved'],
                ['id' => 'rejected', 'name' => 'Rejected'],
            ]"
                option-value="id" option-label="name" />

            <div class="flex gap-2">
                <x-mary-button wire:click="clearFilters" class="btn-ghost" icon="o-x-mark" tooltip="Clear Filters">
                    <span wire:loading.remove wire:target="clearFilters">Clear</span>
                    <span wire:loading wire:target="clearFilters">Clearing...</span>
                </x-mary-button>
            </div>
        </div>
    </div>

    {{-- Tickets Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" wire:loading.class="opacity-50"
        wire:target="search,statusFilter">
        @forelse($tickets as $ticket)
            <div class="flex flex-col bg-base-100 rounded-box shadow-lg overflow-hidden hover:shadow-xl hover:ring-1 ring-primary transition-all"
                wire:key="ticket-review-{{ $ticket->ticket_id }}">
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
                                'pending' => 'badge-warning',
                                'under_review' => 'badge-info',
                                'pending_osa_approval' => 'badge-secondary',
                                'pending_gso_approval' => 'badge-secondary',
                                'approved' => 'badge-success',
                                'rejected' => 'badge-error',
                            ];
                        @endphp
                        <x-mary-badge value="{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}"
                            class="{{ $statusClasses[$ticket->status] ?? 'badge-neutral' }} badge-sm" />
                    </div>

                    {{-- Description --}}
                    <p class="text-sm text-base-content/80 mb-4 line-clamp-3">{{ $ticket->description }}</p>


                    {{-- Event Details --}}
                    @if ($ticket->events->isNotEmpty() && $ticket->events->first()->eventSchedules->isNotEmpty())
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-sm">
                                <x-mary-icon name="o-calendar-days" class="w-4 h-4 text-primary" />
                                <span>{{ $ticket->events->first()->eventSchedules->first()->schedule_date?->format('M d, Y') ?? 'TBD' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <x-mary-icon name="o-map-pin" class="w-4 h-4 text-primary" />
                                <span>{{ $ticket->events->first()->eventSchedules->first()->schedule_venue ?? 'TBD' }}</span>
                            </div>
                        </div>
                    @elseif($ticket->events->isNotEmpty())
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-sm">
                                <x-mary-icon name="o-calendar-days" class="w-4 h-4 text-primary" />
                                <span>{{ $ticket->date_requested ? \Carbon\Carbon::parse($ticket->date_requested)->format('M d, Y') : 'TBD' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <x-mary-icon name="o-map-pin" class="w-4 h-4 text-primary" />
                                <span>{{ $ticket->venue_requested ?? 'TBD' }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Spacer to push bottom content down --}}
                    <div class="flex-1"></div>

                    {{-- Bottom Section --}}
                    <div class="space-y-4">
                        {{-- Attachments Info --}}
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-paper-clip" class="w-4 h-4 text-secondary" />
                            <span class="text-sm text-base-content/70">
                                {{ $ticket->attachments->count() }} attachment(s)
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2">
                            <a href="{{ route('osa.ticket-review.show', $ticket->ticket_number) }}"
                                class="btn btn-primary btn-sm flex-1" wire:navigate>
                                <x-mary-icon name="o-eye" class="w-4 h-4" />
                                Review
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-base-200 px-6 py-3">
                    <div class="flex items-center justify-between text-xs text-base-content/70">
                        <span>Submitted {{ $ticket->created_at?->diffForHumans() ?? 'N/A' }}</span>
                        <span>{{ $ticket->ticket_number }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="flex flex-col items-center gap-4">
                    <x-mary-icon name="o-document-text" class="w-16 h-16 text-base-content/30" />
                    <div>
                        <h3 class="text-lg font-semibold text-base-content/70">No tickets found</h3>
                        <p class="text-sm text-base-content/50">Try adjusting your filters</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($tickets->hasPages())
        <div class="mt-6">
            {{ $tickets->links() }}
        </div>
    @endif

</div>
