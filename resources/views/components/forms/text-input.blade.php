@props(['disabled' => false, 'label' => null, 'value' => null])

@if ($label)
    <div>
        <x-forms.input-label :value="$label" />
        <input @disabled($disabled) {{ $attributes->merge(['class' => 'input w-full']) }}>
    </div>
@else
    <input @disabled($disabled) {{ $attributes->merge(['class' => 'input w-full']) }}>
@endif
