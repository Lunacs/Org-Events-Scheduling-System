@props([
    // osa | gso | student-org | superadmin | viewer — keys the persisted nav + footer.
    'portal' => 'osa',
    // Human label shown under the brand and used for the nav landmark.
    'label' => 'Portal',
    // Sidebar items; defaults to the permission-filtered PortalNavigation menu.
    'items' => null,
    // Extra Vite entries for this portal (avatar, filepond, ...).
    'vite' => [],
    'title' => null,
])

@php
    $menuItems = $items ?? \App\Support\PortalNavigation::itemsFor(auth()->user());
@endphp

{{--
    x-layouts.portal — the one app shell for every signed-in portal (DESIGN.md "App Shell").

    Sidebar + sticky top bar + <main> content column. One checkbox drives the DaisyUI
    drawer: on desktop it toggles expanded/collapsed (remembered in localStorage), on
    mobile it opens the off-canvas menu, which always starts closed on page load.
--}}
<!DOCTYPE html>
<html data-theme="emerald" class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-layouts.partials.head :title="$title" :vite="$vite">
        {{ $head ?? '' }}
    </x-layouts.partials.head>
    @stack('head')
</head>

<body class="min-h-full bg-base-200 font-sans text-base-content antialiased">
    <a href="#main"
        class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-70 focus:rounded-field focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-primary-content">
        Skip to main content
    </a>

    <x-announcement-banner />

    <div class="drawer lg:drawer-open">
        <input id="main-drawer" type="checkbox" class="drawer-toggle" aria-hidden="true" tabindex="-1" />
        <script>
            (function() {
                var toggle = document.getElementById('main-drawer');
                var desktop = window.matchMedia('(min-width: 1024px)');
                var saved = function() {
                    try {
                        return localStorage.getItem('sidebar-expanded') !== 'false';
                    } catch (e) {
                        return true;
                    }
                };
                // Desktop restores expanded/collapsed; mobile never starts with the menu open.
                var sync = function() {
                    toggle.checked = desktop.matches ? saved() : false;
                };
                sync();
                desktop.addEventListener('change', sync);
                toggle.addEventListener('change', function() {
                    if (desktop.matches) {
                        try {
                            localStorage.setItem('sidebar-expanded', toggle.checked ? 'true' : 'false');
                        } catch (e) {}
                    }
                });
                window.__toggleSidebar = function() {
                    toggle.checked = !toggle.checked;
                    toggle.dispatchEvent(new Event('change'));
                };
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && !desktop.matches && toggle.checked) {
                        toggle.checked = false;
                    }
                });
            })();
        </script>

        {{-- Content column: top bar, page, footer --}}
        <div class="drawer-content flex min-h-screen flex-col">
            <div class="sticky top-0 z-20">
                @persist($portal . '-navigation')
                    <livewire:layout.navigation />
                @endpersist
            </div>

            <main id="main" tabindex="-1" class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                {{ $slot }}
            </main>

            <x-footer :variant="$portal" compact />
        </div>

        {{-- Sidebar --}}
        <div class="drawer-side z-30 is-drawer-close:overflow-visible">
            <label for="main-drawer" aria-label="Close navigation" class="drawer-overlay"></label>
            <aside id="portal-sidebar"
                class="flex min-h-full w-72 flex-col border-r border-base-300 bg-base-100 transition-[width] duration-200 ease-out lg:w-64 lg:is-drawer-close:w-16">

                {{-- Brand --}}
                <div class="flex h-16 shrink-0 items-center gap-3 border-b border-base-300 px-3">
                    <img src="{{ asset('images/optimized/plv-logo.webp') }}" alt=""
                        class="size-9 shrink-0" width="80" height="80">
                    <div class="min-w-0 is-drawer-close:hidden">
                        <p class="truncate text-[0.9375rem] font-semibold leading-tight">Event Scheduling</p>
                        <p class="truncate text-xs text-base-content/70">{{ $label }}</p>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav aria-label="{{ $label }}" class="grow overflow-y-auto">
                    <ul class="menu w-full gap-1 px-2 py-3">
                        @foreach ($menuItems as $item)
                            @php $active = request()->is($item['pattern']); @endphp
                            <li>
                                <a href="{{ $item['link'] }}" wire:navigate.hover
                                    @if ($active) aria-current="page" @endif
                                    data-tip="{{ $item['title'] }}"
                                    @class([
                                        'is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:justify-center',
                                        'gap-3 rounded-field px-3 py-2.5 text-sm font-medium transition-colors',
                                        'bg-primary text-primary-content hover:bg-primary focus-visible:bg-primary active:bg-primary!' => $active,
                                        'text-base-content/80 hover:bg-base-200 hover:text-base-content' => ! $active,
                                    ])>
                                    <i class="{{ $item['icon'] }} w-4 shrink-0 text-center" aria-hidden="true"></i>
                                    <span class="truncate is-drawer-close:sr-only">{{ $item['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                {{-- Collapse toggle (desktop) --}}
                <div class="hidden border-t border-base-300 p-2 lg:block">
                    <button type="button" onclick="window.__toggleSidebar()" aria-controls="portal-sidebar"
                        class="btn btn-ghost btn-sm w-full justify-start gap-2 font-medium text-base-content/70 is-drawer-close:justify-center"
                        aria-label="Collapse or expand the sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linejoin="round"
                            stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor"
                            class="size-4 shrink-0 is-drawer-close:rotate-180" aria-hidden="true">
                            <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                            <path d="M9 4v16" />
                            <path d="M15 10l-2 2l2 2" />
                        </svg>
                        <span class="text-xs is-drawer-close:hidden">Collapse</span>
                    </button>
                </div>
            </aside>
        </div>
    </div>

    <x-ui.toast />

    @stack('scripts')
</body>

</html>
