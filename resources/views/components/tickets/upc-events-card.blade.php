@props(['ticket'])

{{-- An upcoming approved event: calendar-leaf date + title + time and venue. --}}
@php
    $date = \Carbon\Carbon::parse($ticket->date_from);
@endphp

<div class="flex items-center gap-3">
    <div class="flex w-12 shrink-0 flex-col items-center rounded-field border border-base-300 bg-base-100 py-1 leading-none"
        aria-hidden="true">
        <span class="text-[0.6875rem] font-medium text-base-content/70">{{ $date->format('M') }}</span>
        <span class="mt-0.5 text-lg font-semibold tabular-nums text-base-content">{{ $date->format('j') }}</span>
    </div>
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-medium text-base-content">{{ $ticket->title }}</p>
        <p class="truncate text-xs text-base-content/70">
            <span class="sr-only">{{ $date->format('F j, Y') }}, </span>
            @if ($ticket->time_from)
                {{ \Carbon\Carbon::parse($ticket->time_from)->format('g:i A') }} &middot;
            @endif
            {{ $ticket->venue_display_name }}
        </p>
    </div>
</div>
