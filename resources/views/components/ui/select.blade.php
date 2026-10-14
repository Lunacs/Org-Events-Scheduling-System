@props([
    'label' => null,
    'icon' => null,
    'iconRight' => null,
    'hint' => null,
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'placeholder' => null,
    'placeholderValue' => null,
    'id' => null,
])

@php
    // Resolve the bound Livewire field so the inline error can be driven from it.
    $field = $attributes->whereStartsWith('wire:model')->first();

    // Stable id so the label stays associated across Livewire re-renders.
    $selectId =
        $id ??
        ($field ? 'ui-select-' . \Illuminate\Support\Str::slug($field) : 'ui-select-' . \Illuminate\Support\Str::random(8));

    $hasError = $field && $errors->has($field);
    $describedBy = collect([$hasError ? $selectId . '-error' : null, $hint ? $selectId . '-hint' : null])
        ->filter()
        ->implode(' ');
@endphp

<div>
    @if ($label)
        <label for="{{ $selectId }}" class="mb-1.5 block text-[0.8125rem] font-medium text-base-content">
            {{ $label }}
            @if ($attributes->get('required'))
                <span class="text-error" aria-hidden="true">*</span>
                <span class="sr-only">(required)</span>
            @endif
        </label>
    @endif

    <label
        {{ $attributes->whereStartsWith('class')->class([
                'select flex w-full items-center gap-2',
                'select-error' => $hasError,
            ]) }}>
        {{-- LEADING ICON --}}
        @if ($icon)
            <x-ui.icon :name="$icon" class="pointer-events-none -ms-1 size-4 shrink-0 text-base-content/60" aria-hidden="true" />
        @endif

        {{-- NATIVE SELECT: forwards wire:model (+ modifiers), required verbatim --}}
        <select id="{{ $selectId }}" @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->whereDoesntStartWith('class') }}>
            @if ($placeholder)
                <option value="{{ $placeholderValue }}">{{ $placeholder }}</option>
            @endif

            @foreach ($options as $option)
                @php
                    // Support both a flat scalar list (['A', 'B']) and a list of arrays/objects.
                    $isScalar = !is_array($option) && !is_object($option);
                    $value = $isScalar ? $option : data_get($option, $optionValue);
                    $text = $isScalar ? $option : data_get($option, $optionLabel);
                    $disabled = !$isScalar && data_get($option, 'disabled');
                @endphp
                <option value="{{ $value }}" @if ($disabled) disabled @endif>{{ $text }}</option>
            @endforeach
        </select>

        {{-- TRAILING ICON --}}
        @if ($iconRight)
            <x-ui.icon :name="$iconRight" class="pointer-events-none size-4 shrink-0 text-base-content/60" aria-hidden="true" />
        @endif
    </label>

    {{-- INLINE VALIDATION ERROR --}}
    @if ($field)
        @error($field)
            <x-ui.input-error :messages="$message" id="{{ $selectId }}-error" class="mt-1.5" />
        @enderror
    @endif

    {{-- HINT --}}
    @if ($hint)
        <p id="{{ $selectId }}-hint" class="mt-1.5 text-xs text-base-content/70">{{ $hint }}</p>
    @endif
</div>
