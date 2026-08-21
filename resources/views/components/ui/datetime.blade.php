@props([
    'label' => null,
    'icon' => null,
    'iconRight' => null,
    'hint' => null,
    'type' => 'date',
    'id' => null,
])

{{--
    x-ui.datetime — replaces `x-mary-datetime`.

    A native date/time <input> (type defaults to `date`) inside a DaisyUI `.input`
    wrapper, with an associated label, optional leading/trailing icons, inline
    validation error and hint. Forwards wire:model (with modifiers), type, min/max,
    readonly, required and Alpine directives verbatim.
--}}
@php
    // Resolve the bound Livewire field so the inline error can be driven from it.
    $field = $attributes->whereStartsWith('wire:model')->first();

    // Ensure a stable id so the label can be associated via for/id.
    $inputId = $id ?? 'ui-datetime-' . \Illuminate\Support\Str::random(8);

    $hasError = $field && $errors->has($field);
@endphp

<div>
    @if ($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium mb-1">
            {{ $label }}
            @if ($attributes->get('required'))
                <span class="text-error">*</span>
            @endif
        </label>
    @endif

    <label
        {{ $attributes->whereStartsWith('class')->class([
                'input flex items-center gap-2 w-full',
                'border-dashed' => $attributes->get('readonly'),
                'input-error' => $hasError,
            ]) }}>
        {{-- LEADING ICON --}}
        @if ($icon)
            <x-ui.icon :name="$icon" class="pointer-events-none w-4 h-4 opacity-60" />
        @endif

        {{-- NATIVE INPUT: forwards wire:model (+ modifiers), type, min/max, readonly, required verbatim --}}
        <input id="{{ $inputId }}"
            {{ $attributes->whereDoesntStartWith('class')->merge(['type' => $type, 'class' => 'grow']) }} />

        {{-- TRAILING ICON --}}
        @if ($iconRight)
            <x-ui.icon :name="$iconRight" class="pointer-events-none w-4 h-4 opacity-60" />
        @endif
    </label>

    {{-- INLINE VALIDATION ERROR --}}
    @if ($field)
        @error($field)
            <x-ui.input-error :messages="$message" class="mt-1" />
        @enderror
    @endif

    {{-- HINT --}}
    @if ($hint)
        <p class="text-xs opacity-60 mt-1">{{ $hint }}</p>
    @endif
</div>
