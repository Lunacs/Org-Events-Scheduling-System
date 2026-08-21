@props([
    'label' => null,
    'hint' => null,
    'right' => false,
    'id' => null,
])

{{--
    x-ui.checkbox — replaces `x-mary-checkbox`.

    DaisyUI `checkbox` with an associated label (prop or `label` slot), optional hint,
    and inline validation error. Forwards wire:model verbatim onto the native input.
--}}
@php
    // Resolve the bound Livewire field so the inline error can be driven from it.
    $field = $attributes->whereStartsWith('wire:model')->first();

    // Ensure a stable id so the label can be associated via for/id.
    $checkboxId = $id ?? 'ui-checkbox-' . \Illuminate\Support\Str::random(8);
@endphp

<div>
    <label @class([
        'flex gap-3 items-center cursor-pointer',
        'justify-between' => $right,
        'items-start' => $hint,
    ])>
        {{-- CHECKBOX: forwards wire:model, required verbatim --}}
        <input id="{{ $checkboxId }}" type="checkbox"
            {{ $attributes->whereDoesntStartWith('id')->class(['checkbox', 'order-2' => $right]) }} />

        {{-- LABEL --}}
        <div @class(['order-1' => $right])>
            @if (filled($label))
                <div class="text-sm font-medium">
                    {{ $label }}
                    @if ($attributes->get('required'))
                        <span class="text-error">*</span>
                    @endif
                </div>
            @endif

            {{-- HINT --}}
            @if ($hint)
                <div class="text-xs opacity-60 mt-1">{{ $hint }}</div>
            @endif
        </div>
    </label>

    {{-- INLINE VALIDATION ERROR --}}
    @if ($field)
        @error($field)
            <x-ui.input-error :messages="$message" class="mt-1" />
        @enderror
    @endif
</div>
