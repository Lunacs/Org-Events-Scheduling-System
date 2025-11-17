@props([
    'title',
    'description',
    'icon',
    'link',
    'color' => 'primary',
    'badge' => null,
    'badgeClass' => 'badge-warning',
])

@php
    $gradientClasses = [
        'primary' => 'from-primary/5 to-primary/10 hover:from-primary/10 hover:to-primary/20 border-primary',
        'secondary' =>
            'from-secondary/5 to-secondary/10 hover:from-secondary/10 hover:to-secondary/20 border-secondary',
        'accent' => 'from-accent/5 to-accent/10 hover:from-accent/10 hover:to-accent/20 border-accent',
        'info' => 'from-info/5 to-info/10 hover:from-info/10 hover:to-info/20 border-info',
        'success' => 'from-success/5 to-success/10 hover:from-success/10 hover:to-success/20 border-success',
        'warning' => 'from-warning/5 to-warning/10 hover:from-warning/10 hover:to-warning/20 border-warning',
        'error' => 'from-error/5 to-error/10 hover:from-error/10 hover:to-error/20 border-error',
    ];

    $iconColorClass = "text-{$color} group-hover:text-white";
    $bgColorClass = "bg-{$color}/10 group-hover:bg-{$color}";
    $gradientClass = $gradientClasses[$color] ?? $gradientClasses['primary'];
@endphp

<a href="{{ $link }}" wire:navigate
    class="group p-6 bg-gradient-to-br {{ $gradientClass }} rounded-xl border-2 border-transparent hover:border-{{ $color }} transition-all duration-200 cursor-pointer">
    <div class="text-center">
        <div
            class="inline-flex items-center justify-center w-16 h-16 rounded-full {{ $bgColorClass }} group-hover:scale-110 transition-all mb-3">
            <x-mary-icon :name="$icon" class="w-8 h-8 {{ $iconColorClass }}" />
        </div>
        <h3 class="font-semibold text-gray-900 mb-1">{{ $title }}</h3>
        <p class="text-xs text-gray-500">{{ $description }}</p>

        @if ($badge)
            <div class="badge {{ $badgeClass }} badge-sm mt-2">{{ $badge }}</div>
        @endif
    </div>
</a>
