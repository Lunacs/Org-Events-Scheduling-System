@props([
    'title' => null,
    'subtitle' => null,
    'separator' => false,
    'boxClass' => '',
    'withCloseButton' => false,
    'persistent' => false,
    'closeOnEscape' => true,
    // Slot
    'actions' => null,
])

{{--
    x-ui.modal-dialog — replaces MaryUI's `x-mary-modal` (wire:model-bound DaisyUI modal).

    The existing x-ui.modal is an event-driven (open-modal/close-modal) Breeze-style
    modal, which is incompatible with MaryUI's boolean wire:model API. This component
    reproduces MaryUI's contract: a boolean Livewire property is entangled into Alpine
    `open`, the DaisyUI `.modal`/`.modal-box` markup renders the title/subtitle/body/
    actions, and closing (backdrop click, escape, close button) sets the property false.

    Props: title, subtitle, separator, boxClass, withCloseButton, persistent (disables
    backdrop-click close). Slots: default (body), actions (footer buttons).
--}}
<div x-data="{ open: @entangle($attributes->wire('model')) }" x-cloak x-show="open" x-transition.opacity.duration.200ms class="modal"
    :class="{ 'modal-open': open }" @keydown.escape.window="open = false" role="dialog" aria-modal="true">
    <div class="modal-box {{ $boxClass }}">
        @if ($withCloseButton)
            <button type="button" class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3" @click="open = false"
                aria-label="Close">✕</button>
        @endif

        @if (filled($title))
            <h3 class="text-lg font-bold text-base-content">{{ $title }}</h3>
        @endif

        @if (filled($subtitle))
            <p class="text-sm text-base-content/60 mt-1">{{ $subtitle }}</p>
        @endif

        @if ($separator)
            <hr class="my-4 border-base-content/10" />
        @endif

        <div class="py-2">
            {{ $slot }}
        </div>

        @if (filled($actions))
            <div class="modal-action">
                {{ $actions }}
            </div>
        @endif
    </div>

    <div class="modal-backdrop" @if (!$persistent) @click="open = false" @endif></div>
</div>
