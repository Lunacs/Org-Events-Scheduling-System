<!DOCTYPE html>
<html data-theme="emerald" class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-layouts.partials.head :title="$title ?? null" :vite="['resources/js/avatar.js']" />
    @stack('head')
</head>

{{-- Public information pages (About, FAQ, Data privacy): simple header, readable content column, full footer. --}}
<body class="min-h-full bg-base-100 font-sans text-base-content antialiased">
    <a href="#main"
        class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-70 focus:rounded-field focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-primary-content">
        Skip to main content
    </a>

    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-20 border-b border-base-300 bg-base-100">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" wire:navigate class="flex items-center gap-3">
                    <img src="{{ asset('images/optimized/plv-logo.webp') }}" alt="" class="size-9" width="80"
                        height="80">
                    <span class="leading-tight">
                        <span class="block text-[0.9375rem] font-semibold">Event Scheduling</span>
                        <span class="hidden text-xs text-base-content/70 sm:block">PLV Office of Student Affairs</span>
                    </span>
                </a>

                <nav aria-label="Main" class="flex items-center gap-1 sm:gap-2">
                    @php
                        $publicLinks = [
                            ['label' => 'FAQ', 'route' => 'faq'],
                            ['label' => 'About OSA', 'route' => 'about-us'],
                            ['label' => 'Data privacy', 'route' => 'data-privacy'],
                        ];
                    @endphp
                    <ul class="hidden items-center gap-1 md:flex">
                        @foreach ($publicLinks as $link)
                            @php $active = request()->routeIs($link['route']); @endphp
                            <li>
                                <a href="{{ route($link['route']) }}" wire:navigate
                                    @if ($active) aria-current="page" @endif
                                    @class([
                                        'rounded-field px-3 py-2 text-sm font-medium transition-colors',
                                        'text-base-content' => $active,
                                        'text-base-content/70 hover:bg-base-200 hover:text-base-content' => ! $active,
                                    ])>{{ $link['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>

                    <label class="swap swap-rotate btn btn-ghost btn-sm btn-square" title="Switch light/dark theme">
                        <input id="theme-toggle" type="checkbox" value="emeraldDark" class="theme-controller sr-only"
                            aria-label="Dark theme" onchange="window.__themeToggleChange(this)" />
                        <x-ui.icon name="o-moon" class="swap-off size-5" aria-hidden="true" />
                        <x-ui.icon name="o-sun" class="swap-on size-5" aria-hidden="true" />
                    </label>

                    @auth
                        <a href="{{ route(auth()->user()->getDashboardRoute()) }}" wire:navigate class="btn btn-primary btn-sm">
                            Go to dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-sm">Sign in</a>
                    @endauth
                </nav>
            </div>

            {{-- Mobile: the same links as a scrollable row (no hidden menu for three links) --}}
            <nav aria-label="Main" class="border-t border-base-300 md:hidden">
                <ul class="mx-auto flex max-w-6xl gap-1 overflow-x-auto px-2 py-1.5">
                    @foreach ($publicLinks as $link)
                        @php $active = request()->routeIs($link['route']); @endphp
                        <li class="shrink-0">
                            <a href="{{ route($link['route']) }}" wire:navigate
                                @if ($active) aria-current="page" @endif
                                @class([
                                    'block rounded-field px-3 py-2 text-sm font-medium',
                                    'bg-base-200 text-base-content' => $active,
                                    'text-base-content/70' => ! $active,
                                ])>{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </header>

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        <x-footer variant="osa" />
        <x-ui.toast />
    </div>

    @stack('scripts')
</body>

</html>
