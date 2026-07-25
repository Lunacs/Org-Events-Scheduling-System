<!DOCTYPE html>
<html data-theme="emerald" class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <script>
        (function() {
            var dark = localStorage.getItem('theme') === 'dark';
            if (dark) {
                document.documentElement.setAttribute('data-theme', 'emeraldDark');
                document.documentElement.classList.add('dark');
            }
        })();
        window.__themeToggleChange = function(input) {
            var dark = input.checked;
            localStorage.setItem('theme', dark ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', dark);
            document.querySelectorAll('input.theme-controller').forEach(function(el) {
                if (el !== input) el.checked = dark;
            });
        };
        document.addEventListener('livewire:navigated', function() {
            var dark = localStorage.getItem('theme') === 'dark';
            if (dark) {
                document.documentElement.setAttribute('data-theme', 'emeraldDark');
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'emerald');
                document.documentElement.classList.remove('dark');
            }
            document.querySelectorAll('input.theme-controller').forEach(function(el) {
                el.checked = dark;
            });
        });
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="{{ asset('images/optimized/osa-logo.webp') }}">

    <!-- Fonts — single consolidated request -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600|poppins:400,500,600&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/css/fontawesome.css', 'resources/js/app.js', 'resources/js/avatar.js', 'resources/js/filepond.js'])

    @stack('head')
</head>

<body class="font-sans antialiased scroll-smooth">
    <div class="min-h-screen">

        {{-- Announcements Banner --}}
        <x-announcement-banner />

        {{-- MAIN: DaisyUI Drawer Sidebar --}}
        <div class="drawer lg:drawer-open" x-data="{ sidebarExpanded: $persist(true).as('student-sidebar-expanded') }">
            <input id="main-drawer" type="checkbox" class="drawer-toggle" wire:ignore :checked="sidebarExpanded"
                @change="sidebarExpanded = $event.target.checked" />
            <script>
                if (localStorage.getItem('student-sidebar-expanded') === 'true') {
                    document.getElementById('main-drawer').checked = true;
                }
            </script>

            {{-- DRAWER CONTENT (Navbar + Page) --}}
            <div class="drawer-content flex flex-col">
                {{-- Top Navigation Bar --}}
                <div class="sticky top-0 z-15 bg-base-100">
                    @persist('student-navigation')
                        <livewire:layout.navigation />
                    @endpersist
                </div>

                {{-- Page Content --}}
                <div class="flex-1 px-4">
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                @persist('student-footer')
                    <x-footer variant="student-org" />
                @endpersist
            </div>

            {{-- DRAWER SIDEBAR --}}
            <div class="drawer-side is-drawer-close:overflow-visible z-20">
                <label for="main-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
                <div
                    class="flex min-h-full flex-col items-start bg-base-200 border-r border-base-300/60 is-drawer-close:w-16 is-drawer-open:w-64 transition-[width] duration-200">

                    {{-- BRAND --}}
                    <div class="w-full px-3 pt-5 pb-2 border-b border-base-300/60">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/optimized/plv-logo.webp') }}" alt="PLV Logo"
                                class="h-10 w-10 shrink-0" loading="eager" width="80" height="80">
                            <div class="is-drawer-close:hidden">
                                <h2 class="text-lg font-semibold font-heading leading-tight">Event Scheduling</h2>
                                <p class="text-xs text-base-content/60">Student Organization Portal</p>
                            </div>
                        </div>
                    </div>


                    {{-- MENU --}}
                    @php
                        $menuItems = [
                            [
                                'title' => 'Dashboard',
                                'icon' => 'fa-solid fa-table-columns',
                                'link' => '/student-org/dashboard',
                                'pattern' => 'student-org/dashboard*',
                            ],
                            [
                                'title' => 'My Tickets',
                                'icon' => 'fa-solid fa-ticket',
                                'link' => '/student-org/my-tickets',
                                'pattern' => 'student-org/my-tickets*',
                            ],
                            [
                                'title' => 'Submit Ticket',
                                'icon' => 'fa-solid fa-file-circle-plus',
                                'link' => '/student-org/submit-ticket',
                                'pattern' => 'student-org/submit-ticket*',
                            ],
                            [
                                'title' => 'Event Calendar',
                                'icon' => 'fa-solid fa-calendar-days',
                                'link' => '/student-org/calendar',
                                'pattern' => 'student-org/calendar*',
                            ],
                            [
                                'title' => 'Reschedule Request',
                                'icon' => 'fa-solid fa-arrow-rotate-left',
                                'link' => '/student-org/reschedule',
                                'pattern' => 'student-org/reschedule*',
                            ],
                            [
                                'title' => 'Notifications',
                                'icon' => 'fa-solid fa-bell',
                                'link' => '/student-org/notifications',
                                'pattern' => 'student-org/notifications*',
                            ],
                            [
                                'title' => 'History',
                                'icon' => 'fa-solid fa-box-archive',
                                'link' => '/student-org/history',
                                'pattern' => 'student-org/history*',
                            ],
                        ];
                    @endphp

                    <ul class="menu w-full grow font-heading mt-2 gap-2">
                        @foreach ($menuItems as $item)
                            <li>
                                <a href="{{ $item['link'] }}" wire:navigate.hover
                                    class="is-drawer-close:tooltip is-drawer-close:tooltip-right is-drawer-close:flex is-drawer-close:items-center is-drawer-close:justify-center rounded-lg transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 px-4 py-1.5 {{ request()->is($item['pattern']) ? 'bg-neutral text-neutral-content' : '' }}"
                                    data-tip="{{ $item['title'] }}">
                                    <i class="{{ $item['icon'] }} w-4 text-center shrink-0"></i>
                                    <span class="is-drawer-close:hidden">{{ $item['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- SIDEBAR TOGGLE (visible on lg screens) --}}
                    <div class="w-full px-2 py-4 hidden lg:block border-t border-base-300/60">
                        <label for="main-drawer" aria-label="toggle sidebar"
                            class="btn btn-ghost btn-sm w-full is-drawer-close:justify-center justify-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linejoin="round"
                                stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor"
                                class="size-4 shrink-0">
                                <path
                                    d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z">
                                </path>
                                <path d="M9 4v16"></path>
                                <path d="M14 10l2 2l-2 2"></path>
                            </svg>
                            <span class="is-drawer-close:hidden text-xs">Collapse</span>
                        </label>
                    </div>

                </div>
            </div>
        </div>

        {{-- Toast --}}
        <x-ui.toast />

    </div>

    {{-- Scripts Stack --}}
    @stack('scripts')


</body>

</html>
