@props([
    'title' => null,
    'subtitle' => null,
    // [['label' => 'Tickets', 'link' => route(...)], ['label' => 'TKT-2026-0001']]
    'breadcrumbs' => [],
])

{{--
    x-ui.page-heading — the page header for every signed-in page (DESIGN.md "Page Header").

    Left-aligned <h1> + one-line description, optional breadcrumbs above, and an
    `actions` slot on the right that holds the page's single primary action. Use the
    `meta` slot for status badges or counts that belong next to the title.
    No icon tiles, gradients, or banners.
--}}
<header {{ $attributes->class(['mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if (count($breadcrumbs) > 0)
            <nav aria-label="Breadcrumb" class="breadcrumbs mb-1 p-0 text-sm text-base-content/70">
                <ul>
                    @foreach ($breadcrumbs as $crumb)
                        <li>
                            @if (isset($crumb['link']))
                                <a href="{{ $crumb['link'] }}" wire:navigate
                                    class="hover:text-base-content">{{ $crumb['label'] }}</a>
                            @else
                                <span aria-current="page">{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
            <h1 class="text-2xl font-semibold leading-tight tracking-[-0.015em] text-base-content sm:text-[1.75rem]">
                {{ $title ?? $heading ?? '' }}
            </h1>
            @isset($meta)
                <div class="flex flex-wrap items-center gap-2">{{ $meta }}</div>
            @endisset
        </div>

        @if (filled($subtitle))
            <p class="mt-1.5 max-w-[70ch] text-[0.9375rem] text-base-content/70">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div {{ $actions->attributes->class(['flex flex-wrap items-center gap-2 sm:shrink-0 sm:justify-end']) }}>
            {{ $actions }}
        </div>
    @endisset
</header>
