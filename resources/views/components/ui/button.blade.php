@props([
    'label' => null,
    'icon' => null,
    'iconRight' => null,
    'link' => null,
    'size' => null,
    'spinner' => null,
    'tooltip' => null,
    'tooltipLeft' => null,
    'tooltipRight' => null,
    'tooltipBottom' => null,
    'responsive' => false,
    'external' => false,
    'disabled' => false,
])

@php
    // Resolve tooltip text + position (parity with MaryUI's Button component).
$tooltip = $tooltip ?? ($tooltipLeft ?? ($tooltipRight ?? $tooltipBottom));
$tooltipPosition = $tooltipLeft
    ? 'lg:tooltip-left'
    : ($tooltipRight
        ? 'lg:tooltip-right'
        : ($tooltipBottom
            ? 'lg:tooltip-bottom'
            : 'lg:tooltip-top'));

// Map the size prop to a DaisyUI button size class.
$sizeClass = match ($size) {
    'xs' => 'btn-xs',
    'sm' => 'btn-sm',
    'md' => 'btn-md',
    'lg' => 'btn-lg',
    default => null,
};

// When spinner === true/1 the loading state targets the wire:click action;
// otherwise the provided spinner value is used as the wire:target.
$spinnerTarget =
    $spinner === true || $spinner === 1 || $spinner === '1'
        ? $attributes->whereStartsWith('wire:click')->first()
        : $spinner;
$hasSpinner = filled($spinner);

// Determine whether the control exposes visible text; icon-only controls need an accessible name.
$hasVisibleText = filled($label) || $slot->isNotEmpty();
$ariaLabel = !$hasVisibleText && $attributes->missing('aria-label') ? $tooltip ?? ($icon ?? $iconRight) : null;

// Class list merged once so btn/size/tooltip classes never render twice alongside a pass-through class.
$btnClasses = [
    'btn',
        $sizeClass => filled($sizeClass),
        "!inline-flex lg:tooltip {$tooltipPosition}" => filled($tooltip),
    ];
@endphp

@if (filled($link))
    <a href="{{ $link }}" {{ $attributes->class($btnClasses) }}
        @if ($external) target="_blank" rel="noopener noreferrer" @endif
        @if (!$external && $attributes->whereStartsWith('wire:navigate')->isEmpty()) wire:navigate @endif
        @if (filled($tooltip)) data-tip="{{ $tooltip }}" @endif
        @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
        @if ($disabled) aria-disabled="true" tabindex="-1" @endif>
    @else
        <button {{ $attributes->class($btnClasses)->merge(['type' => 'button']) }}
            @if (filled($tooltip)) data-tip="{{ $tooltip }}" @endif
            @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
            @if ($disabled) disabled @endif
            @if ($hasSpinner) wire:target="{{ $spinnerTarget }}"
            wire:loading.attr="disabled" @endif>
@endif

{{-- SPINNER (left) --}}
@if ($hasSpinner && !$iconRight)
    <span wire:loading wire:target="{{ $spinnerTarget }}" class="loading loading-spinner w-5 h-5"></span>
@endif

{{-- LEADING ICON --}}
@if (filled($icon))
    <span class="block"
        @if ($hasSpinner) wire:loading.class="hidden" wire:target="{{ $spinnerTarget }}" @endif>
        <x-ui.icon :name="$icon" class="w-5 h-5" />
    </span>
@endif

{{-- LABEL / SLOT --}}
@if (filled($label))
    <span @class(['hidden lg:block' => $responsive])>{{ $label }}</span>
@else
    {{ $slot }}
@endif

{{-- TRAILING ICON --}}
@if (filled($iconRight))
    <span class="block"
        @if ($hasSpinner) wire:loading.class="hidden" wire:target="{{ $spinnerTarget }}" @endif>
        <x-ui.icon :name="$iconRight" class="w-5 h-5" />
    </span>
@endif

{{-- SPINNER (right) --}}
@if ($hasSpinner && $iconRight)
    <span wire:loading wire:target="{{ $spinnerTarget }}" class="loading loading-spinner w-5 h-5"></span>
@endif

@if (filled($link))
    </a>
@else
    </button>
@endif
