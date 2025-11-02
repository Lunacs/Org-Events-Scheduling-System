<div>
    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Ticket Review & Approvals</h1>
                    <p class="text-base-content/70 mt-1">Review event proposals and final approvals</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-primary">{{ $tickets->total() }} Tickets</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-base-100 rounded-box shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tickets..."
                class="input input-bordered w-full" />

            <select wire:model.live="statusFilter" class="select select-bordered w-full">
                <option value="">Filter by Status</option>
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

            <div class="flex gap-2">
                <button wire:click="clearFilters" type="button" class="btn btn-ghost" title="Clear Filters">
                    <span wire:loading.remove wire:target="clearFilters">Clear</span>
                    <span wire:loading wire:target="clearFilters">Clearing...</span>
                </button>
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
                        <span class="badge badge-sm {{ $statusClasses[$ticket->status] ?? 'badge-neutral' }}">
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
                                <span
                                    class="text-success font-medium">{{ $ticket->events->first()->eventSchedules->first()->start_date?->format('M d, Y') ?? 'TBD' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span
                                    class="text-success font-medium">{{ $ticket->events->first()->eventSchedules->first()->venue ?? 'TBD' }}</span>
                            </div>
                        @else
                            {{-- Show requested dates --}}
                            <div class="flex items-center gap-2 text-sm">
                                <span>{{ $ticket->date_from ? \Carbon\Carbon::parse($ticket->date_from)->format('M d, Y') : 'TBD' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <span>{{ $ticket->venue_requested ?? 'TBD' }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Spacer to push bottom content down --}}
                    <div class="flex-1"></div>

                    {{-- Bottom Section --}}
                    <div class="space-y-4">
                        {{-- Attachments Info --}}
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-base-content/70">
                                {{ $ticket->attachments->count() }} attachment(s)
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2">
                            <a href="{{ route('osa.ticket-review.show', $ticket->ticket_number) }}"
                                class="btn btn-primary btn-sm flex-1" wire:navigate title="Review">
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
