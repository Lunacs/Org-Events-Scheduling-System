<!DOCTYPE html>
<html data-theme="emerald" class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Student Organization</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,900|poppins:400,500,600,900" rel="stylesheet" />

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen">

        {{-- NAVBAR mobile only --}}
        <x-mary-nav sticky class="lg:hidden">
            <x-slot:brand>
                <div class="ml-5 pt-5">Student Organization Portal</div>
            </x-slot:brand>
            <x-slot:actions>
                <label for="main-drawer" class="lg:hidden mr-3">
                    <i class="fas fa-burger cursor-pointer"></i>
                </label>
            </x-slot:actions>
        </x-mary-nav>

        {{-- MAIN --}}
        <x-mary-main full-width>
            {{-- SIDEBAR --}}
            <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100  lg:bg-inherit rounded-r-xl mx-10">

                {{-- BRAND --}}
                <div class="ml-3 mr-5 pt-5 flex items-center justify-between">
                    <div class="flex items-center">
                        <img src="{{ asset('images/plv-logo.png') }}" alt="PLV Logo" class="h-10 w-10">
                        <h2 class="p-3 text-lg font-semibold font-heading ml-2">Student Org Portal</h2>
                    </div>
                </div>

                {{-- USER INFO --}}
                @if ($user = auth()->user())
                    <x-mary-menu-separator />
                    <x-mary-list-item :item="$user" value="name" sub-value="email" no-separator no-hover
                        class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <x-mary-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="Logout"
                                no-wire-navigate link="/logout" />
                        </x-slot:actions>
                    </x-mary-list-item>
                    <x-mary-menu-separator />
                @endif

                {{-- MENU --}}
                <x-mary-menu separator activate-by-route active-bg-color="bg-neutral" class="font-heading">
                    <x-mary-menu-item title="Dashboard" icon="s-squares-2x2" link="/student-org/dashboard"
                        wire:navigate />
                    <x-mary-menu-item title="Submit Ticket" icon="s-document-plus" link="/student-org/submit-ticket"
                        wire:navigate />
                    <x-mary-menu-item title="My Tickets" icon="s-ticket" link="/student-org/my-tickets" wire:navigate />
                    <x-mary-menu-item title="Event Calendar" icon="s-calendar" link="/student-org/calendar"
                        wire:navigate />
                    <x-mary-menu-item title="Reschedule Request" icon="s-arrow-path" link="/student-org/reschedule"
                        wire:navigate />
                    <x-mary-menu-item title="Notifications" icon="s-bell" link="/student-org/notifications"
                        wire:navigate />
                    <x-mary-menu-item title="History" icon="s-archive-box" link="/student-org/history" wire:navigate />

                    <x-mary-menu-sub title="Account" icon="s-user">
                        <x-mary-menu-item title="Profile" icon="o-user-circle" link="/profile" wire:navigate />
                        <x-mary-menu-item title="Settings" icon="o-cog-6-tooth" link="/student-org/settings"
                            wire:navigate />
                    </x-mary-menu-sub>
                </x-mary-menu>
            </x-slot:sidebar>

            <x-slot:footer>
                <div class="bg-accent text-center py-2">
                    <p class="text-sm">© 2025 PLV Event Scheduling System - Student Organization Portal</p>
                </div>
            </x-slot:footer>

            {{-- The `$slot` goes here --}}
            <x-slot:content>
                {{ $slot }}
            </x-slot:content>
        </x-mary-main>

        {{-- Toast --}}
        <x-mary-toast />
    </div>
</body>

</html>
