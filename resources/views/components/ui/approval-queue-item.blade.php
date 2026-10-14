@props([
    'title',
    'description' => null,
    // Ticket status key; renders the shared status badge (preferred over label/tone).
    'status' => null,
    // Legacy free-text badge + tone, used when there is no ticket status.
    'label' => null,
    'tone' => 'warning',
    'daysWaiting' => null,
    // Identifier shown in monospace (e.g. TKT-2026-0001).
    'reference' => null,
    'meta' => [],
])

{{--
    x-ui.approval-queue-item — one "item awaiting action" row for divided queue lists.

    Used by the OSA "Needs your review" queue and the SuperAdmin "Pending approvals"
    list so a queued item looks identical everywhere. Flat by design: the parent list
    draws the dividers and the hover state. `meta` accepts
    ['icon' => ..., 'text' => ...] entries rendered as small inline details.
--}}
@php
    $toneMap = [
        'warning' => 'badge-soft badge-warning',
        'info' => 'badge-soft badge-info',
        'error' => 'badge-soft badge-error',
        'success' => 'badge-soft badge-success',
    ];
    $badgeClass = $toneMap[$tone] ?? $toneMap['warning'];
@endphp

<div {{ $attributes->class(['flex items-center justify-between gap-4 py-3.5']) }}>
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <h3 class="truncate text-sm font-semibold text-base-content">{{ $title }}</h3>
            @if ($status)
                <x-tickets.progress-badge :status="$status" size="sm" />
            @elseif ($label)
                <span class="badge badge-sm {{ $badgeClass }}">{{ $label }}</span>
            @endif
        </div>
        @if ($description)
            <p class="mt-0.5 truncate text-sm text-base-content/70">{{ $description }}</p>
        @endif
        @if ($reference || ! empty($meta) || $daysWaiting !== null)
            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-base-content/70">
                @if ($reference)
                    <span class="font-mono">{{ $reference }}</span>
                @endif
                @foreach ($meta as $item)
                    <span class="flex min-w-0 items-center gap-1">
                        @if (! empty($item['icon']))
                            <x-ui.icon :name="$item['icon']" class="size-3.5 shrink-0" aria-hidden="true" />
                        @endif
                        <span class="truncate">{{ $item['text'] }}</span>
                    </span>
                @endforeach
                @if ($daysWaiting !== null)
                    <span>Waiting {{ $daysWaiting }} {{ Str::plural('day', $daysWaiting) }}</span>
                @endif
            </div>
        @endif
    </div>
    <x-ui.icon name="o-chevron-right" class="size-4 shrink-0 text-base-content/40" aria-hidden="true" />
</div>
