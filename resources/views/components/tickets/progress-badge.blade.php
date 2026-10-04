@props(['status', 'size' => 'md'])

{{--
    x-tickets.progress-badge — the one status badge (DESIGN.md "Status Badges").
    Label + icon + color from App\Support\TicketStatus, so status never relies on color alone.
--}}
@php
    $meta = \App\Support\TicketStatus::for($status);
@endphp

<span {{ $attributes->class([
    'badge gap-1 whitespace-nowrap font-medium',
    \App\Support\TicketStatus::badgeClass($status),
    'badge-sm' => $size === 'sm',
    'badge-md' => $size === 'md',
    'badge-lg' => $size === 'lg',
]) }}>
    <x-ui.icon :name="$meta['icon']" @class(['shrink-0', 'size-3.5' => $size !== 'lg', 'size-4' => $size === 'lg']) aria-hidden="true" />
    {{ $meta['label'] }}
</span>
