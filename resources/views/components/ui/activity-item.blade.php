@props([
    'icon' => 'o-information-circle',
    'iconColor' => 'text-info',
    'title',
    'description' => null,
    'timestamp' => null,
])

<div class="flex gap-3">
    <div class="flex flex-col items-center">
        <div class="w-8 h-8 rounded-full bg-{{ $iconColor }}/10 flex items-center justify-center">
            <x-ui.icon :name="$icon" class="w-4 h-4 {{ $iconColor }}" />
        </div>
        <div class="w-px h-full bg-base-300 mt-2"></div>
    </div>
    <div class="flex-1 pb-4">
        <p class="text-sm font-medium">{{ $title }}</p>
        @if ($description)
            <p class="text-xs text-gray-500">{{ $description }}</p>
        @endif
        @if ($timestamp)
            <p class="text-xs text-gray-400 mt-1">{{ $timestamp }}</p>
        @endif
    </div>
</div>
