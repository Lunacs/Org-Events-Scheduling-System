@props([
    'label' => null,
    'hint' => null,
    'rows' => 3,
    'id' => null,
])

@php
    // Resolve the bound Livewire field so the inline error can be driven from it.
    $field = $attributes->whereStartsWith('wire:model')->first();

    // Stable id so the label stays associated across Livewire re-renders.
    $textareaId =
        $id ??
        ($field ? 'ui-textarea-' . \Illuminate\Support\Str::slug($field) : 'ui-textarea-' . \Illuminate\Support\Str::random(8));

    $hasError = $field && $errors->has($field);
    $describedBy = collect([$hasError ? $textareaId . '-error' : null, $hint ? $textareaId . '-hint' : null])
        ->filter()
        ->implode(' ');
@endphp

{{--
    x-ui.textarea — replaces `x-mary-textarea`.

    DaisyUI `textarea` with an associated label. Forwards wire:model (+ modifiers),
    placeholder, required, and rows verbatim. Renders an inline @error message
    adjacent to the control and an optional hint, both wired via aria-describedby.
--}}
<div>
    @if ($label)
        <label for="{{ $textareaId }}" class="mb-1.5 block text-[0.8125rem] font-medium text-base-content">
            {{ $label }}
            @if ($attributes->get('required'))
                <span class="text-error" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            @endif
        </label>
    @endif

    <textarea id="{{ $textareaId }}" rows="{{ $rows }}" @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->whereDoesntStartWith('id')->class(['textarea w-full text-sm', 'textarea-error' => $hasError]) }}></textarea>

    {{-- INLINE VALIDATION ERROR --}}
    @if ($field)
        @error($field)
            <x-ui.input-error :messages="$message" id="{{ $textareaId }}-error" class="mt-1.5" />
        @enderror
    @endif

    {{-- HINT --}}
    @if ($hint)
        <p id="{{ $textareaId }}-hint" class="mt-1.5 text-xs text-base-content/70">{{ $hint }}</p>
    @endif
</div>
