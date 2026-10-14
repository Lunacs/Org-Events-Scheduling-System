{{-- Legacy Breeze API; renders the design-system secondary button. --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline border-base-300 bg-base-100']) }}>
    {{ $slot }}
</button>
