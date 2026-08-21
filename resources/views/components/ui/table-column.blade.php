@props(['heading' => false])

{{--
    x-ui.table-column — cell helper for <x-ui.table> custom mode.

    Renders a <td> by default, or a <th> when the `heading` prop is set. All
    pass-through attributes (class, colspan, wire:*, x-on:*, etc.) are forwarded
    verbatim onto the rendered element.
--}}

@if ($heading)
    <th {{ $attributes }}>{{ $slot }}</th>
@else
    <td {{ $attributes }}>{{ $slot }}</td>
@endif
