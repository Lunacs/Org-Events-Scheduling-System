@props([
    'label',
    'description',
    'icon',
    'link',
    'color' => 'primary',
    'badge' => null,
])

{{--
    x-ui.quick-action — dashboard shortcut tile.

    Solid icon chip that inverts on hover, no gradient wash. Tailwind can't resolve
    `bg-{$color}` classes generated from a PHP variable at build time, so per-color
    variants are written out as a literal map instead of interpolated strings.
--}}
@php
    $colorMap = [
        'primary' => ['chip' => 'bg-primary/10 text-primary', 'chipHover' => 'group-hover:bg-primary group-hover:text-primary-content', 'border' => 'hover:border-primary'],
        'secondary' => ['chip' => 'bg-secondary/10 text-secondary', 'chipHover' => 'group-hover:bg-secondary group-hover:text-secondary-content', 'border' => 'hover:border-secondary'],
        'accent' => ['chip' => 'bg-accent/10 text-accent', 'chipHover' => 'group-hover:bg-accent group-hover:text-accent-content', 'border' => 'hover:border-accent'],
        'info' => ['chip' => 'bg-info/10 text-info', 'chipHover' => 'group-hover:bg-info group-hover:text-info-content', 'border' => 'hover:border-info'],
        'success' => ['chip' => 'bg-success/10 text-success', 'chipHover' => 'group-hover:bg-success group-hover:text-success-content', 'border' => 'hover:border-success'],
        'warning' => ['chip' => 'bg-warning/10 text-warning', 'chipHover' => 'group-hover:bg-warning group-hover:text-warning-content', 'border' => 'hover:border-warning'],
    ];
    $c = $colorMap[$color] ?? $colorMap['primary'];
@endphp

<a href="{{ $link }}" wire:navigate
    class="group flex flex-col gap-3 p-5 bg-base-100 border border-base-300 rounded-xl {{ $c['border'] }} transition-colors">
    <div class="w-12 h-12 rounded-xl {{ $c['chip'] }} {{ $c['chipHover'] }} flex items-center justify-center transition-colors">
        <x-ui.icon :name="$icon" class="w-6 h-6" />
    </div>
    <div>
        <h3 class="font-semibold text-base-content">{{ $label }}</h3>
        <p class="text-xs text-base-content/60 mt-0.5">{{ $description }}</p>
    </div>
    @if ($badge)
        <span class="badge badge-sm badge-{{ $color }} self-start">{{ $badge }}</span>
    @endif
</a>
