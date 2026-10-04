@props([
    'label',
    'value',
    'icon',
    'meta' => null,
    // Semantic tone for the icon only: neutral | info | success | warning | error | primary.
    'color' => 'neutral',
    'link' => null,
    // Optional link label shown to assistive tech (the whole tile is the link target).
    'linkLabel' => null,
])

{{--
    x-ui.metric-card — a flat stat tile (DESIGN.md "Stat Strip").

    Small icon + label, one tabular value, optional meta line. When `link` is set the
    whole tile is the click target (Fitts's Law) and opens the matching filtered list.
    Color is reserved for status: only the icon takes a tone, never the tile.
--}}
@php
    $toneMap = [
        'primary' => 'text-primary',
        'secondary' => 'text-base-content/70',
        'accent' => 'text-base-content/70',
        'neutral' => 'text-base-content/70',
        'info' => 'text-info',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'error' => 'text-error',
    ];
    $iconTone = $toneMap[$color] ?? $toneMap['neutral'];
    $tag = $link ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($link) href="{{ $link }}" wire:navigate @endif
    {{ $attributes->class([
        'group block rounded-box border border-base-300 bg-base-100 p-4',
        'transition-colors hover:border-base-content/25 hover:bg-base-200/40' => $link,
    ]) }}>
    <div class="flex items-center gap-2 text-sm font-medium text-base-content/70">
        <x-ui.icon :name="$icon" class="size-4 shrink-0 {{ $iconTone }}" aria-hidden="true" />
        <span class="truncate">{{ $label }}</span>
        @if ($link)
            <x-ui.icon name="o-arrow-right"
                class="ms-auto size-4 shrink-0 text-base-content/40 transition-transform group-hover:translate-x-0.5 group-hover:text-base-content/70"
                aria-hidden="true" />
        @endif
    </div>
    <p class="mt-2 text-2xl font-semibold leading-none tabular-nums text-base-content">{{ $value }}</p>
    @if ($meta)
        <p class="mt-1.5 text-xs text-base-content/70">{{ $meta }}</p>
    @endif
    @if ($link && $linkLabel)
        <span class="sr-only">{{ $linkLabel }}</span>
    @endif
</{{ $tag }}>
