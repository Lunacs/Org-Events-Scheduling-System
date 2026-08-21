@props([
    'label' => null,
    'hint' => null,
    'rows' => 3,
    'id' => null,
])

@php
    // Resolve the bound Livewire field so the inline error can be driven from it.
    $field = $attributes->whereStartsWith('wire:model')->first();

    // Ensure a stable id so the label can be associated via for/id.
    $textareaId = $id ?? 'ui-textarea-' . \Illuminate\Support\Str::random(8);

    $hasError = $field && $errors->has($field);
@endphp

{{--
    x-ui.textarea — replaces `x-mary-textarea`.

    DaisyUI `textarea` with an associated label. Forwards wire:model (+ modifiers),
    placeholder, required, and rows verbatim. Renders an inline @error message
    adjacent to the control and an optional hint.
--}}
<div>
    @if ($label)
        <label for="{{ $textareaId }}" class="block text-sm font-medium mb-1">
            {{ $label }}
            @if ($attributes->get('required'))
                <span class="text-error">*</span>
            @endif
        </label>
    @endif

    <textarea id="{{ $textareaId }}" rows="{{ $rows }}"
        {{ $attributes->whereDoesntStartWith('id')->class(['textarea textarea-bordered w-full', 'textarea-error' => $hasError]) }}></textarea>

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
