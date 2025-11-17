@props([
    'icon' => 'o-inbox',
    'title',
    'description' => null,
    'actionLabel' => null,
    'actionLink' => null,
    'iconColor' => 'text-gray-300',
])

<div class="text-center py-12">
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-base-200 mb-4">
        <x-mary-icon :name="$icon" class="w-10 h-10 {{ $iconColor }}" />
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $title }}</h3>

    @if ($description)
        <p class="text-sm text-gray-500 mb-4">{{ $description }}</p>
    @endif

    @if ($actionLabel && $actionLink)
        <x-mary-button :label="$actionLabel" :link="$actionLink" class="btn-primary btn-sm" wire:navigate />
    @endif
</div>
