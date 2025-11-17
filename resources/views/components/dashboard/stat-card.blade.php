@props([
    'title',
    'value',
    'icon',
    'description' => null,
    'trend' => null,
    'trendDirection' => null,
    'color' => 'primary',
    'action' => null,
    'actionLabel' => null,
    'actionLink' => null,
    'badge' => null,
    'badgeClass' => 'badge-warning',
])

@php
    $colorClasses = [
        'primary' => 'border-l-primary',
        'success' => 'border-l-success',
        'warning' => 'border-l-warning',
        'error' => 'border-l-error',
        'info' => 'border-l-info',
        'secondary' => 'border-l-secondary',
        'accent' => 'border-l-accent',
    ];

    $iconColorClasses = [
        'primary' => 'text-primary',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'error' => 'text-error',
        'info' => 'text-info',
        'secondary' => 'text-secondary',
        'accent' => 'text-accent',
    ];

    $borderClass = $colorClasses[$color] ?? 'border-l-primary';
    $iconColorClass = $iconColorClasses[$color] ?? 'text-primary';
@endphp

<x-mary-card class="hover:shadow-lg transition-all duration-200 border-l-4 {{ $borderClass }}">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
                <x-mary-icon :name="$icon" class="w-5 h-5 {{ $iconColorClass }}" />
                <p class="text-sm font-medium text-gray-600">{{ $title }}</p>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>

            @if ($description)
                <p class="text-xs text-gray-500 mt-1">{{ $description }}</p>
            @endif

            @if ($trend)
                <div class="flex items-center gap-1 mt-2">
                    @if ($trendDirection === 'up')
                        <x-mary-icon name="o-arrow-trending-up" class="w-3 h-3 text-success" />
                        <span class="text-xs text-success">{{ $trend }}</span>
                    @elseif ($trendDirection === 'down')
                        <x-mary-icon name="o-arrow-trending-down" class="w-3 h-3 text-error" />
                        <span class="text-xs text-error">{{ $trend }}</span>
                    @else
                        <span class="text-xs text-gray-500">{{ $trend }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="avatar placeholder">
            <div
                class="bg-{{ $color }}/10 text-{{ $color }} rounded-full w-12 h-12 flex items-center justify-center">
                @if ($badge)
                    <span class="text-xl font-bold">{{ $badge }}</span>
                @else
                    <x-mary-icon :name="$icon" class="w-6 h-6" />
                @endif
            </div>
        </div>
    </div>

    @if ($actionLink && $actionLabel)
        <div class="mt-3 pt-3 border-t">
            <x-mary-button :label="$actionLabel" icon-right="o-arrow-right"
                class="btn-{{ $color }} btn-sm btn-block" :link="$actionLink" wire:navigate />
        </div>
    @endif
</x-mary-card>
