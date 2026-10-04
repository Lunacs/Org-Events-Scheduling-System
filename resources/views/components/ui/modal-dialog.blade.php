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
    x-ui.modal-dialog — wire:model-bound DaisyUI modal (replaces MaryUI's x-mary-modal).

    A boolean Livewire property is entangled into Alpine `open`. Focus is trapped inside
    while open and returned to the trigger on close; Escape and backdrop click close it
    unless `persistent`. Titled dialogs are labelled for assistive tech.

    Props: title, subtitle, separator, boxClass, withCloseButton, persistent,
    closeOnEscape. Slots: default (body), actions (footer buttons).
--}}
@php
    $dialogId = 'dialog-' . \Illuminate\Support\Str::random(6);
@endphp

<div x-data="{ open: @entangle($attributes->wire('model')) }" x-cloak x-show="open"
    x-transition.opacity.duration.150ms class="modal overscroll-contain" :class="{ 'modal-open': open }"
    @if ($closeOnEscape && ! $persistent) @keydown.escape.window="open = false" @endif role="dialog" aria-modal="true"
    @if (filled($title)) aria-labelledby="{{ $dialogId }}-title" @endif>
    <div class="modal-box rounded-box border border-base-300 p-5 shadow-overlay sm:p-6 {{ $boxClass }}"
        x-trap.noscroll="open">
        @if ($withCloseButton)
            <button type="button" class="btn btn-ghost btn-sm btn-square absolute end-3 top-3" @click="open = false"
                aria-label="Close dialog">
                <x-ui.icon name="o-x-mark" class="size-5" aria-hidden="true" />
            </button>
        @endif

        @if (filled($title))
            <h2 id="{{ $dialogId }}-title" class="pe-8 text-lg font-semibold text-base-content">{{ $title }}</h2>
        @endif

        @if (filled($subtitle))
            <p class="mt-1 text-sm text-base-content/70">{{ $subtitle }}</p>
        @endif

        @if ($separator)
            <hr class="my-4 border-base-300" />
        @endif

        <div class="py-2">
            {{ $slot }}
        </div>

        @if (filled($actions))
            <div class="modal-action mt-5 flex-wrap gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>

    <div class="modal-backdrop bg-neutral/40" @if (! $persistent) @click="open = false" @endif></div>
</div>
