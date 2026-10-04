@props(['value'])

<label {{ $attributes->merge(['class' => 'mb-1.5 block text-[0.8125rem] font-medium text-base-content']) }}>
    {{ $value ?? $slot }}
</label>
