{{-- Legacy Breeze API; renders the design-system destructive button. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-error']) }}>
    {{ $slot }}
</button>
