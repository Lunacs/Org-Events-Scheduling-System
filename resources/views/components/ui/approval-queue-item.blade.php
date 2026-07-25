@props([
    'title',
    'description' => null,
    'label' => null,
    'daysWaiting' => null,
    'meta' => [],
    'tone' => 'warning',
])

{{--
    x-ui.approval-queue-item — unified "item awaiting action" row.

    Used by both the OSA "Action Required" list and the SuperAdmin "Items Needing
    Attention" list so the same underlying UX idea (a queued item with a wait-time
    signal) looks identical everywhere it appears. `meta` accepts an array of
    ['icon' => ..., 'text' => ...] entries rendered as small inline details.
--}}
@php
    $toneMap = [
        'warning' => 'badge-warning',
        'info' => 'badge-info',
        'error' => 'badge-error',
        'success' => 'badge-success',
    ];
    $badgeClass = $toneMap[$tone] ?? 'badge-warning';
@endphp

<div {{ $attributes->class(['flex items-center justify-between gap-4 p-4 bg-base-200/50 hover:bg-base-200 rounded-xl border border-base-300 transition-colors']) }}>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1.5">
            @if ($label)
                <span class="badge {{ $badgeClass }} badge-sm">{{ $label }}</span>
            @endif
            @if ($daysWaiting !== null)
                <span class="text-xs text-base-content/50">Waiting {{ $daysWaiting }} {{ Str::plural('day', $daysWaiting) }}</span>
            @endif
        </div>
        <h4 class="font-semibold text-base-content truncate">{{ $title }}</h4>
        @if ($description)
            <p class="text-sm text-base-content/60 truncate">{{ $description }}</p>
        @endif
        @if (!empty($meta))
            <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-base-content/50">
                @foreach ($meta as $item)
                    <span class="flex items-center gap-1">
                        @if (!empty($item['icon']))
                            <x-ui.icon :name="$item['icon']" class="w-3 h-3" />
                        @endif
                        {{ $item['text'] }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
    <x-ui.icon name="o-chevron-right" class="w-5 h-5 text-base-content/30 shrink-0" />
</div>
