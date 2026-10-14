{{-- Legacy Breeze API; renders the design-system primary button. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary']) }}>
    {{ $slot }}
</button>
