@props([
    'title' => null,
    'value' => null,
    'icon' => null,
    'description' => null,
    'color' => '',
    'tooltip' => null,
    'tooltipLeft' => null,
    'tooltipRight' => null,
    'tooltipBottom' => null,
])

{{--
    x-ui.stat — replaces `x-mary-stat`.

    DaisyUI stat block. Renders a title, prominent value (prop or default slot),
    optional icon (through the x-ui.icon shim) and optional description. Supports an
    optional tooltip (with directional variants) mirroring MaryUI's Stat component.
    Pass-through class is forwarded verbatim to the root element.
--}}
@php
    // Resolve tooltip text + position (parity with MaryUI's Stat component).
$resolvedTooltip = $tooltip ?? ($tooltipLeft ?? ($tooltipRight ?? $tooltipBottom));
$tooltipPosition = $tooltipLeft
    ? 'lg:tooltip-left'
    : ($tooltipRight
        ? 'lg:tooltip-right'
        : ($tooltipBottom
            ? 'lg:tooltip-bottom'
            : 'lg:tooltip-top'));
@endphp

<div {{ $attributes->class([
    'stats bg-base-100 w-full',
    "lg:tooltip {$tooltipPosition}" => filled($resolvedTooltip),
]) }}
    @if (filled($resolvedTooltip)) data-tip="{{ $resolvedTooltip }}" @endif>
    <div class="stat">
        @if (filled($icon))
            <div class="stat-figure {{ $color }}">
                <x-ui.icon :name="$icon" class="w-9 h-9" />
            </div>
        @endif

        @if (filled($title))
            <div class="stat-title whitespace-nowrap">{{ $title }}</div>
        @endif

        <div class="stat-value text-2xl">{{ $value ?? $slot }}</div>

        @if (filled($description))
            <div class="stat-desc">{{ $description }}</div>
        @endif
    </div>
</div>
