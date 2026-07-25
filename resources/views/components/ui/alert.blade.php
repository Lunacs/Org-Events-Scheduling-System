@props([
    'title' => null,
    'icon' => null,
    'description' => null,
    'shadow' => false,
    'dismissible' => false,
    'actions' => null,
])

{{--
    x-ui.alert — replaces `x-mary-alert`.

    DaisyUI `alert` component with role="alert". Renders an optional leading icon
    (through the x-ui.icon shim), a title + description, or the default slot when no
    title is provided. An optional `actions` slot holds buttons/links, and the alert
    can be made dismissible. Pass-through class (alert-info, alert-success, etc.) is
    forwarded verbatim to the root element.
--}}
<div role="alert" {{ $attributes->class(['alert', 'shadow-md' => $shadow]) }}
    @if ($dismissible) x-data="{ show: true }" x-show="show" @endif>
    @if (filled($icon))
        <x-ui.icon :name="$icon" class="self-center" />
    @endif

    @if (filled($title))
        <div>
            <div @class(['font-bold' => filled($description)])>{{ $title }}</div>
            @if (filled($description))
                <div class="text-xs">{{ $description }}</div>
            @endif
        </div>
    @else
        <span>{{ $slot }}</span>
    @endif

    @if (filled($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif

    @if ($dismissible)
        <button type="button" class="btn btn-xs btn-circle btn-ghost self-start" @click="show = false"
            aria-label="Dismiss">
            <x-ui.icon name="o-x-mark" class="h-4 w-4" />
        </button>
    @endif
</div>
