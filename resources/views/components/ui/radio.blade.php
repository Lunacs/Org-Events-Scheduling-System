@props([
    'label' => null,
    'hint' => null,
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'optionHint' => 'hint',
    'options' => [],
    'inline' => false,
    'id' => null,
])

{{--
    x-ui.radio — replaces `x-mary-radio`.

    A DaisyUI `radio` group generated from `options` (honoring optionValue/optionLabel).
    Renders an optional legend label, an optional per-option hint, and an inline
    validation error. Forwards wire:model verbatim onto each native radio input; the
    input `name` is derived from the bound Livewire field so the group behaves as one.
--}}
@php
    // Resolve the bound Livewire field so the inline error + group name can be driven from it.
    $field = $attributes->whereStartsWith('wire:model')->first();
@endphp

<div>
    <fieldset class="py-0">
        {{-- LABEL --}}
        @if ($label)
            <legend class="text-sm font-medium mb-2">
                {{ $label }}
                @if ($attributes->get('required'))
                    <span class="text-error">*</span>
                @endif
            </legend>
        @endif

        <div @class(['gap-4 grid', 'sm:flex sm:gap-6' => $inline])>
            @foreach ($options as $option)
                <label
                    class="flex items-center gap-3 cursor-pointer {{ data_get($option, $optionHint) ? '!items-start' : '' }}">
                    {{-- RADIO: forwards wire:model + class verbatim --}}
                    <input type="radio" name="{{ $field }}" value="{{ data_get($option, $optionValue) }}"
                        @if (data_get($option, 'disabled')) disabled @endif {{ $attributes->class(['radio']) }} />

                    <div>
                        <div class="text-sm font-medium">{{ data_get($option, $optionLabel) }}</div>
                        @if (data_get($option, $optionHint))
                            <div class="text-xs opacity-60 mt-1">{{ data_get($option, $optionHint) }}</div>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>

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
    </fieldset>
</div>
