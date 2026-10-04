@props(['status'])

@if ($status)
    <div role="status" {{ $attributes->merge(['class' => 'flex items-start gap-2 rounded-field border border-success/30 bg-success/[0.08] px-3 py-2.5 text-sm text-success']) }}>
        <x-ui.icon name="s-check-circle" class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
        <span>{{ $status }}</span>
    </div>
@endif
