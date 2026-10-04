@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-1 text-sm text-error']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-1.5">
                <x-ui.icon name="s-exclamation-circle" class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
