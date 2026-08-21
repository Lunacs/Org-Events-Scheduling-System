@props([
    'right' => false,
    'title' => null,
    'subtitle' => null,
    'separator' => false,
    'withCloseButton' => false,
    'closeOnEscape' => false,
    'withoutBackdropClose' => false,
    'id' => null,
])

{{--
    x-ui.drawer — replaces `x-mary-drawer`.

    A DaisyUI `drawer` slide-over driven by an Alpine `open` state entangled with the
    bound Livewire model (wire:model). Reproduces MaryUI's drawer: optional right
    placement, title/subtitle header (via x-ui.card), separator, close button,
    close-on-escape, and backdrop click-to-close. Any Livewire bindings inside the
    default slot are left untouched. Pass-through classes (e.g. width) apply to the
    inner card; @-event listeners (e.g. @close) apply to the root.
--}}
@php
    $model = $attributes->wire('model')->value();
    $drawerId = $id ?? ($model ?: 'ui-drawer-' . \Illuminate\Support\Str::random(6));
@endphp

<div x-data="{
    open: @if ($model) @entangle($attributes->wire('model')) @else false @endif,
    close() {
        this.open = false;
        if (this.$refs.checkbox) { this.$refs.checkbox.checked = false }
    }
}" x-init="$watch('open', value => { if (!value) { $dispatch('close') } else { $dispatch('open') } })" @if ($closeOnEscape)
    @keydown.window.escape="close()"
    @endif
    @class(['drawer absolute z-50', 'drawer-end' => $right])
    {{ $attributes->whereStartsWith('@') }}>
    {{-- Toggle visibility --}}
    <input id="{{ $drawerId }}" x-model="open" x-ref="checkbox" type="checkbox" class="drawer-toggle" />

    <div class="drawer-side z-50">
        {{-- Overlay / click outside to close --}}
        @if ($withoutBackdropClose)
            <div class="drawer-overlay pointer-events-none"></div>
        @else
            <label for="{{ $drawerId }}" class="drawer-overlay"></label>
        @endif

        {{-- Content --}}
        <x-ui.card :title="$title" :subtitle="$subtitle" :separator="$separator" wire:key="drawer-card"
            {{ $attributes->except('wire:model')->whereDoesntStartWith('@')->class(['min-h-screen rounded-none px-8']) }}>
            @if ($withCloseButton)
                <x-slot:menu>
                    <x-ui.button icon="o-x-mark" class="btn-ghost btn-sm btn-circle" @click="close()" />
                </x-slot:menu>
            @endif

            {{ $slot }}
        </x-ui.card>
    </div>
</div>
