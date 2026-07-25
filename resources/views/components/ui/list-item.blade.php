@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'link' => null,
    'noSeparator' => false,
    'noHover' => false,
    'value' => null,
    'actions' => null,
])

{{--
    x-ui.list-item — replaces `x-mary-list-item`.

    A flex row reproducing MaryUI's list-item layout: an optional leading icon
    (through the x-ui.icon shim), a title with optional subtitle, an optional trailing
    `value` slot and an `actions` slot. When `link` is set the row becomes navigable via
    wire:navigate. `noHover` disables the hover background and `noSeparator` hides the
    bottom divider. Pass-through class is forwarded verbatim to the row element.
--}}
<div>
    <div
        {{ $attributes->class([
            'flex justify-start items-center gap-4 px-3 py-3',
            'hover:bg-base-200' => !$noHover,
            'cursor-pointer' => filled($link),
        ]) }}>
        {{-- LEADING ICON --}}
        @if (filled($icon))
            <div class="shrink-0">
                @if (filled($link))
                    <a href="{{ $link }}" wire:navigate>
                        <x-ui.icon :name="$icon" class="w-5 h-5 text-base-content/70" />
                    </a>
                @else
                    <x-ui.icon :name="$icon" class="w-5 h-5 text-base-content/70" />
                @endif
            </div>
        @endif

        {{-- CONTENT --}}
        <div class="flex-1 overflow-hidden whitespace-nowrap text-ellipsis truncate">
            @if (filled($link))
                <a href="{{ $link }}" wire:navigate>
            @endif

            <div>
                @if (filled($title))
                    <div class="font-semibold truncate">{{ $title }}</div>
                @endif

                @if (filled($subtitle))
                    <div class="text-base-content/50 text-sm truncate">{{ $subtitle }}</div>
                @endif
            </div>

            @if (filled($link))
                </a>
            @endif
        </div>

        {{-- VALUE --}}
        @if (filled($value))
            <div class="shrink-0 text-sm text-base-content/70">
                {{ $value }}
            </div>
        @endif

        {{-- ACTIONS --}}
        @if (filled($actions))
            <div {{ $actions->attributes->class(['flex items-center gap-3']) }}>
                {{ $actions }}
            </div>
        @endif
    </div>

    @unless ($noSeparator)
        <hr class="border-t-(length:--border) border-base-content/10" />
    @endunless
</div>
