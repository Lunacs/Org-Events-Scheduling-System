@props([
    'label' => null,
    'hint' => null,
    'id' => null,
])

@php
    // Resolve the bound Livewire field so the inline error can be driven from it.
    $field = $attributes->whereStartsWith('wire:model')->first();

    // Ensure a stable id so the label can be associated via for/id.
    $fileId = $id ?? 'ui-file-' . \Illuminate\Support\Str::random(8);

    $hasError = $field && $errors->has($field);
@endphp

{{--
    x-ui.file — replaces `x-mary-file`.

    DaisyUI `file-input` with an associated label. Forwards wire:model, accept, and
    other attributes verbatim. Renders an inline @error message and an optional hint.
    (Image preview markup is provided by the call site, matching prior behavior.)
--}}
<div>
    @if ($label)
        <label for="{{ $fileId }}" class="block text-sm font-medium mb-1">
            {{ $label }}
            @if ($attributes->get('required'))
                <span class="text-error">*</span>
            @endif
        </label>
    @endif

    <input id="{{ $fileId }}" type="file"
        {{ $attributes->whereDoesntStartWith('id')->class(['file-input file-input-bordered w-full', 'file-input-error' => $hasError]) }} />

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
