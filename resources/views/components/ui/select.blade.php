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

    // Ensure a stable id so the label can be associated via for/id.
    $selectId = $id ?? 'ui-select-' . \Illuminate\Support\Str::random(8);

    $hasError = $field && $errors->has($field);
@endphp

<div>
    @if ($label)
        <label for="{{ $selectId }}" class="block text-sm font-medium mb-1">
            {{ $label }}
            @if ($attributes->get('required'))
                <span class="text-error">*</span>
            @endif
        </label>
    @endif

    <label
        {{ $attributes->whereStartsWith('class')->class([
                'select flex items-center gap-2 w-full',
                'border-dashed' => $attributes->get('readonly'),
                'select-error' => $hasError,
            ]) }}>
        {{-- LEADING ICON --}}
        @if ($icon)
            <x-ui.icon :name="$icon" class="pointer-events-none w-4 h-4 -ms-1 opacity-60" />
        @endif

        {{-- NATIVE SELECT: forwards wire:model (+ modifiers), required verbatim --}}
        <select id="{{ $selectId }}" {{ $attributes->whereDoesntStartWith('class') }}>
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
                <option value="{{ $value }}" @if ($disabled) disabled @endif>{{ $text }}
                </option>
            @endforeach
        </select>

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
