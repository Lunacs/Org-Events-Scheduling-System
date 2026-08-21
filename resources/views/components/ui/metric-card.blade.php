@props([
    'label',
    'value',
    'icon',
    'meta' => null,
    'color' => 'primary',
    'link' => null,
])

{{--
    x-ui.metric-card — dashboard KPI tile.

    Solid icon chip + big value, tokens only (no raw gray, no side-stripe border).
    `color` selects the DaisyUI semantic token used for the icon chip and link accent.
--}}
<div {{ $attributes->class(['card bg-base-100 border border-base-300 p-5 shadow-sm']) }}>
    <div class="flex items-center justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium text-base-content/60 mb-1">{{ $label }}</p>
            <p class="text-3xl font-bold font-heading text-base-content">{{ $value }}</p>
            @if ($meta)
                <p class="text-xs text-base-content/50 mt-1">{{ $meta }}</p>
            @endif
        </div>
        <div class="shrink-0 w-12 h-12 rounded-xl bg-{{ $color }}/10 text-{{ $color }} flex items-center justify-center">
            <x-ui.icon :name="$icon" class="w-6 h-6" />
        </div>
    </div>

    @if ($link)
        <a href="{{ $link }}" wire:navigate
            class="mt-3 pt-3 border-t border-base-200 flex items-center gap-1 text-xs font-semibold text-{{ $color }} hover:underline">
            View details
            <x-ui.icon name="o-arrow-right" class="w-3 h-3" />
        </a>
    @endif
</div>
