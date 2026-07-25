@props([
    'value' => null,
    'icon' => null,
    'iconRight' => null,
])

{{--
    x-ui.badge — replaces `x-mary-badge`.

    DaisyUI `badge` component. Renders the `value` prop (or the default slot when no
    value is given). Optional leading/trailing icons render through the x-ui.icon shim.
    Any pass-through class (e.g. badge-success, badge-warning) is forwarded verbatim to
    the root element.
--}}
<div {{ $attributes->class(['badge']) }}>
    @if (filled($icon))
        <x-ui.icon :name="$icon" class="h-4 w-4" />
    @endif

    {{ $value ?? $slot }}

    @if (filled($iconRight))
        <x-ui.icon :name="$iconRight" class="h-4 w-4" />
    @endif
</div>
