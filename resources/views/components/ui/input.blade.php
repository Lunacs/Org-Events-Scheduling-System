@props([
    'label' => null,
    'icon' => null,
    'iconRight' => null,
    'hint' => null,
    'type' => 'text',
    'id' => null,
])

@php
    // Resolve the bound Livewire field so the inline error can be driven from it.
    $field = $attributes->whereStartsWith('wire:model')->first();

    // Stable id (from the wire:model field when available) so label/for and
    // aria-describedby survive Livewire re-renders without stealing focus.
    $inputId =
        $id ??
        ($field ? 'ui-input-' . \Illuminate\Support\Str::slug($field) : 'ui-input-' . \Illuminate\Support\Str::random(8));

    $hasError = $field && $errors->has($field);
    $describedBy = collect([$hasError ? $inputId . '-error' : null, $hint ? $inputId . '-hint' : null])
        ->filter()
        ->implode(' ');
@endphp

<div>
    @if ($label)
        <label for="{{ $inputId }}" class="mb-1.5 block text-[0.8125rem] font-medium text-base-content">
            {{ $label }}
            @if ($attributes->get('required'))
                <span class="text-error" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            @endif
        </label>
    @endif

    <label
        {{ $attributes->whereStartsWith('class')->class([
                'input flex w-full items-center gap-2',
                'input-error' => $hasError,
            ]) }}>
        {{-- LEADING ICON --}}
        @if ($icon)
            <x-ui.icon :name="$icon" class="pointer-events-none size-4 shrink-0 text-base-content/60" aria-hidden="true" />
        @endif

        {{-- NATIVE INPUT: forwards wire:model (+ modifiers), value, readonly, required, ::type, placeholder verbatim --}}
        <input id="{{ $inputId }}" @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->whereDoesntStartWith('class')->merge(['type' => $type, 'class' => 'grow min-w-0']) }} />

        {{-- TRAILING ICON --}}
        @if ($iconRight)
            <x-ui.icon :name="$iconRight" class="pointer-events-none size-4 shrink-0 text-base-content/60" aria-hidden="true" />
        @endif
    </label>

    {{-- INLINE VALIDATION ERROR --}}
    @if ($field)
        @error($field)
            <x-ui.input-error :messages="$message" id="{{ $inputId }}-error" class="mt-1.5" />
        @enderror
    @endif

    {{-- HINT --}}
    @if ($hint)
        <p id="{{ $inputId }}-hint" class="mt-1.5 text-xs text-base-content/70">{{ $hint }}</p>
    @endif
</div>
