@props([
    // MaryUI `x-mary-dropdown` shorthand: `right` aligns the menu to the end.
    'right' => false,
    // Breeze-compatible alignment: left | right | top | bottom.
    'align' => 'right',
    // Breeze-compatible width token (e.g. '48') or an explicit Tailwind width class.
    'width' => '48',
    // Extra classes applied to the menu panel (Breeze compatibility).
    'contentClasses' => '',
    // MaryUI `label` shorthand: renders a default button trigger when no
    // `trigger` slot is supplied.
    'label' => null,
])

@php
    // MaryUI's boolean `right` prop is shorthand for right alignment; otherwise
// fall back to the Breeze `align` prop.
$effectiveAlign = $right ? 'right' : $align;

$alignmentClasses = match ($effectiveAlign) {
    'left' => 'origin-top-left start-0',
    'top' => 'origin-bottom bottom-full mb-2',
    'bottom' => 'origin-top',
    default => 'origin-top-right end-0', // right
};

// Map the Breeze width token to a Tailwind class; pass through anything else.
$widthClass = match ($width) {
    '48' => 'w-48',
    'auto', '', null => '',
        default => $width,
    };
@endphp

<div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false"
    @keydown.escape.window="open = false">
    {{-- Trigger: preserves any Livewire bindings placed inside the trigger slot verbatim.
         Consumers pass a `trigger` slot; MaryUI-style consumers pass a `label` prop. --}}
    <div @click="open = ! open" @keydown.enter.prevent="open = ! open" @keydown.space.prevent="open = ! open" role="button"
        tabindex="0" aria-haspopup="true" :aria-expanded="open">
        @isset($trigger)
            {{ $trigger }}
        @else
            <span class="btn btn-ghost btn-sm">{{ $label }}</span>
        @endisset
    </div>

    {{-- Menu panel: DaisyUI menu styling with reduced-motion-aware transitions --}}
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75 motion-reduce:transition-none"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        {{ $attributes->merge(['class' => "menu bg-base-100 rounded-box shadow absolute z-50 mt-2 {$widthClass} {$alignmentClasses} {$contentClasses}"]) }}>
        {{-- Breeze consumers pass a `content` slot; MaryUI/new consumers use the default slot.
             Livewire bindings inside either slot are preserved verbatim. --}}
        {{ $content ?? $slot }}
    </div>
</div>
