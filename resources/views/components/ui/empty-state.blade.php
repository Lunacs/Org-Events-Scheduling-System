@props([
    'icon' => 'o-inbox',
    'title',
    'description' => null,
    'actionLabel' => null,
    'actionLink' => null,
    'iconColor' => 'text-base-content/30',
    'tone' => 'default',
])

@php
    $toneMap = [
        'default' => 'bg-base-200 text-base-content',
        'primary' => 'bg-primary/15 text-primary',
        'success' => 'bg-success/15 text-success',
        'warning' => 'bg-warning/15 text-warning',
        'error' => 'bg-error/15 text-error',
        'info' => 'bg-info/15 text-info',
        'secondary' => 'bg-secondary/15 text-secondary',
    ];

    $toneClass = $toneMap[$tone] ?? $toneMap['default'];
@endphp

<div class="text-center py-12">
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 {{ $toneClass }}">
        <x-mary-icon :name="$icon" class="w-10 h-10 {{ $iconColor }}" />
    </div>
    <h3 class="text-lg font-semibold text-base-content mb-2">{{ $title }}</h3>

    @if ($description)
        <p class="text-sm text-base-content/70 mb-4">{{ $description }}</p>
    @endif

    @if ($actionLabel && $actionLink)
        <x-mary-button :label="$actionLabel" :link="$actionLink" class="btn-primary btn-sm" wire:navigate />
    @endif
</div>
