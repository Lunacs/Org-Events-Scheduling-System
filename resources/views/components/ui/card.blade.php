@props([
    'title' => null,
    'subtitle' => null,
    'shadow' => false,
    'separator' => false,
    'bodyClass' => '',
    // Slots
    'menu' => null,
    'actions' => null,
    'figure' => null,
])

{{--
    x-ui.card — replaces `x-mary-card`.

    DaisyUI `card bg-base-100` container. Renders an optional figure, a header
    (title/subtitle with an optional `menu` slot), the default slot as the card body,
    and an optional `actions` slot. `shadow` toggles the drop shadow and `separator`
    draws divider lines around the header/actions. Any pass-through class (e.g.
    lg:col-span-2) is forwarded verbatim to the root element.
--}}
<div {{ $attributes->class(['card bg-base-100 p-5', 'shadow-xs' => $shadow]) }}>
    @if (filled($figure))
        <figure {{ $figure->attributes->class(['mb-5 -m-5']) }}>
            {{ $figure }}
        </figure>
    @endif

    @if (filled($title) || filled($subtitle))
        <div class="pb-5">
            <div class="flex gap-3 justify-between items-center w-full">
                <div class="grow">
                    @if (filled($title))
                        <div class="card-title text-xl font-bold">{{ $title }}</div>
                    @endif
                    @if (filled($subtitle))
                        <div class="text-base-content/50 text-sm mt-1">{{ $subtitle }}</div>
                    @endif
                </div>

                @if (filled($menu))
                    <div {{ $menu->attributes->class(['flex items-center gap-2']) }}>
                        {{ $menu }}
                    </div>
                @endif
            </div>

            @if ($separator)
                <hr class="mt-3 border-t-(length:--border) border-base-content/10" />
            @endif
        </div>
    @endif

    <div @class(['grow', $bodyClass => filled($bodyClass)])>
        {{ $slot }}
    </div>

    @if (filled($actions))
        @if ($separator)
            <hr class="mt-5 border-t-(length:--border) border-base-content/10" />
        @endif

        <div {{ $actions->attributes->class(['flex w-full items-end justify-end gap-3 pt-5']) }}>
            {{ $actions }}
        </div>
    @endif
</div>
