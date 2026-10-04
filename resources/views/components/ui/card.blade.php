@props([
    'title' => null,
    'subtitle' => null,
    // Kept for backwards compatibility; panels are flat at rest (DESIGN.md "Flat-at-Rest").
    'shadow' => false,
    'separator' => false,
    'bodyClass' => '',
    // Heading level for the title (h2 under a page h1; h3 when nested in a section).
    'headingLevel' => 2,
    // Slots
    'menu' => null,
    'actions' => null,
    'figure' => null,
])

{{--
    x-ui.card — the standard panel (DESIGN.md "Panels / Cards").

    Paper-white surface, 1px hairline border, 12px radius, 16–20px padding, no shadow.
    Optional figure, a header (title/subtitle + `menu` slot for panel actions), the
    default slot as the body, and an optional `actions` footer. Pass-through classes
    (e.g. lg:col-span-2) go to the root element.
--}}
@php
    $headingTag = 'h' . max(2, min(4, (int) $headingLevel));
@endphp

<section {{ $attributes->class(['card bg-base-100 rounded-box border border-base-300 p-4 sm:p-5']) }}>
    @if (filled($figure))
        <figure {{ $figure->attributes->class(['-m-4 mb-4 sm:-m-5 sm:mb-5']) }}>
            {{ $figure }}
        </figure>
    @endif

    @if (filled($title) || filled($subtitle) || filled($menu))
        <div class="pb-4">
            <div class="flex w-full items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    @if (filled($title))
                        <{{ $headingTag }} class="text-base font-semibold leading-snug text-base-content">{{ $title }}</{{ $headingTag }}>
                    @endif
                    @if (filled($subtitle))
                        <p class="mt-0.5 text-sm text-base-content/70">{{ $subtitle }}</p>
                    @endif
                </div>

                @if (filled($menu))
                    <div {{ $menu->attributes->class(['-me-2 -mt-1 flex shrink-0 items-center gap-2']) }}>
                        {{ $menu }}
                    </div>
                @endif
            </div>

            @if ($separator)
                <hr class="mt-4 border-base-300" />
            @endif
        </div>
    @endif

    <div @class(['grow', $bodyClass => filled($bodyClass)])>
        {{ $slot }}
    </div>

    @if (filled($actions))
        @if ($separator)
            <hr class="mt-5 border-base-300" />
        @endif

        <div {{ $actions->attributes->class(['flex w-full flex-wrap items-center justify-end gap-3 pt-5']) }}>
            {{ $actions }}
        </div>
    @endif
</section>
