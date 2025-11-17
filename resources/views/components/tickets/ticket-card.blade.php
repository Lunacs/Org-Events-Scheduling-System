@props(['ticket', 'role' => 'osa'])

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
                    'received' => 'border-info text-info',
                    'gso_review' => 'border-secondary text-secondary',
                    'pending_osa_approval' => 'border-warning text-warning',
                    'for_rescheduling' => 'border-warning text-warning',
                    'rescheduled' => 'border-success text-success',
                    'needs_revision' => 'border-warning text-warning',
                    'amended' => 'border-info text-info',
                    'approved' => 'border-success text-success',
                    'rejected' => 'border-error text-error',
                    'completed' => 'border-neutral text-neutral',
                ];
            @endphp
            <span
                class="badge-outline rounded-full border badge-sm {{ $statusClasses[$ticket->status] ?? 'badge-neutral' }}">
                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
            </span>
        </div>

        {{-- Description --}}
        <p class="text-sm text-base-content/80 mb-4 line-clamp-3">{{ $ticket->description }}</p>

        {{-- Spacer to push bottom content down --}}
        <div class="flex-grow"></div>

        {{-- Event Details --}}
        <div class="space-y-2 mb-4">
            @if ($ticket->events->isNotEmpty() && $ticket->events->first()->eventSchedules->isNotEmpty())
                {{-- Show approved event schedule --}}
                <div class="flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span
                        class="text-success font-medium">{{ $ticket->events->first()->eventSchedules->first()->start_date?->format('M d, Y') ?? 'TBD' }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span
                        class="text-base-content/80">{{ $ticket->date_from ? \Carbon\Carbon::parse($ticket->date_from)->format('M d, Y') : 'TBD' }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="grow"></div>

        {{-- Bottom Section --}}
        <div class="space-y-4">
            @if ($role == 'gso')
                {{-- from osa remarks --}}
                <div class="bg-warning/10 border border-warning/30 rounded-lg p-3">
                    <p class="text-sm font-semibold text-base-content/70 mb-2">Remarks from OSA: </p>
                    <p class="text-xs text-base-content/70">
                        {{ $ticket->latestOsaApproval?->remarks ?? 'No remarks' }}
                    </p>
                </div>
            @endif
            {{-- Attachments Info --}}
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-base-content/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
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
