@props([
    'icon' => 'o-inbox',
    'title',
    'description' => null,
    'actionLabel' => null,
    'actionLink' => null,
    // Legacy props: empty states are neutral by design (DESIGN.md "Empty States").
    'iconColor' => null,
    'tone' => 'default',
    'compact' => false,
    // Make the action the page's primary action (only when this empty state IS the page).
    'primary' => false,
])

{{--
    x-ui.empty-state — teaches what will appear here and offers the next action.
    Neutral icon (no colored circles), one-line title, one sentence, optional action.
--}}
<div {{ $attributes->class(['flex flex-col items-center text-center', 'py-10' => ! $compact, 'py-6' => $compact]) }}>
    <div class="mb-3 flex size-10 items-center justify-center rounded-full bg-base-200 text-base-content/60">
        <x-ui.icon :name="$icon" class="size-5" aria-hidden="true" />
    </div>
    <h3 class="text-sm font-semibold text-base-content">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-base-content/70">{{ $description }}</p>
    @endif

    @if ($actionLabel && $actionLink)
        <x-ui.button :label="$actionLabel" :link="$actionLink" :class="$primary ? 'btn-sm btn-primary mt-4' : 'btn-sm mt-4'" wire:navigate />
    @endif

    {{ $slot }}
</div>
