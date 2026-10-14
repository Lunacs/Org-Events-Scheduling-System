@props(['tickets'])

{{--
    One ticket in the organization's "My tickets" list: status + next action up front,
    key facts in one line, and the workflow timeline so progress is always visible.
--}}
@php
    $ticket = $tickets;
    $status = strtolower((string) $ticket->status);
    $detailsUrl = route('student-org.ticket-details', $ticket->ticket_number);
    $needsRevision = in_array($status, ['for_revision', 'needs_revision'], true);
@endphp

<article {{ $attributes->class(['rounded-box border border-base-300 bg-base-100 p-4 sm:p-5']) }}
    aria-labelledby="ticket-{{ $ticket->ticket_id }}-title">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <x-tickets.progress-badge :status="$ticket->status" size="sm" />
                <span class="font-mono text-xs text-base-content/70">{{ $ticket->ticket_number }}</span>
            </div>
            <h3 id="ticket-{{ $ticket->ticket_id }}-title" class="mt-1.5 text-base font-semibold text-base-content">
                <a href="{{ $detailsUrl }}" wire:navigate class="hover:underline">{{ $ticket->title }}</a>
            </h3>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            @if ($needsRevision)
                <x-ui.button label="Revise" icon="o-pencil-square" class="btn-primary btn-sm"
                    wire:click="$dispatch('resubmit-ticket', { ticketId: {{ $ticket->ticket_id }} })" />
            @elseif ($status === 'approved')
                <x-ui.button label="Reschedule" icon="o-arrow-path" class="btn-sm"
                    link="{{ route('student-org.reschedule', ['ticket' => $ticket->ticket_number]) }}" />
            @endif
            <x-ui.button icon="o-chat-bubble-left-right" class="btn-ghost btn-sm btn-square"
                link="{{ $detailsUrl }}#comments" aria-label="Comments on {{ $ticket->title }}" tooltip="Comments" />
        </div>
    </div>

    @if (filled($ticket->description))
        <p class="mt-2 line-clamp-2 max-w-[75ch] text-sm text-base-content/70">{{ $ticket->description }}</p>
    @endif

    <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-base-content/80">
        <div class="flex items-center gap-1.5">
            <dt><x-ui.icon name="o-calendar" class="size-4 text-base-content/60" /><span class="sr-only">Date</span></dt>
            <dd>
                {{ $ticket->date_from ? \Carbon\Carbon::parse($ticket->date_from)->format('M j, Y') : 'No date' }}
                @if ($ticket->time_from)
                    &middot; {{ \Carbon\Carbon::parse($ticket->time_from)->format('g:i A') }}
                @endif
            </dd>
        </div>
        <div class="flex min-w-0 items-center gap-1.5">
            <dt><x-ui.icon name="o-map-pin" class="size-4 text-base-content/60" /><span class="sr-only">Venue</span></dt>
            <dd class="truncate">{{ $ticket->venue_display_name ?? 'Venue to be confirmed' }}</dd>
        </div>
        @if ($ticket->total_participants)
            <div class="flex items-center gap-1.5">
                <dt><x-ui.icon name="o-users" class="size-4 text-base-content/60" /><span class="sr-only">Participants</span></dt>
                <dd class="tabular-nums">{{ number_format($ticket->total_participants) }} expected</dd>
            </div>
        @endif
    </dl>

    <x-tickets.status-timeline :status="$ticket->status" class="mt-4 border-t border-base-300 pt-4" />

    <x-tickets.latest-remark :status="$ticket->status" :ticket="$ticket" />

    <p class="mt-3 text-xs text-base-content/70">
        Submitted {{ \Carbon\Carbon::parse($ticket->created_at)->format('M j, Y') }}
        &middot; Updated {{ \Carbon\Carbon::parse($ticket->updated_at)->diffForHumans() }}
    </p>
</article>
