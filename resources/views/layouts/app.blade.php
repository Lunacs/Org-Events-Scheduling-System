<!DOCTYPE html>
<html data-theme="emerald" class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,900|poppins:400,500,600,900" rel="stylesheet" />

    <!-- Font Awesome -->
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
                <div class="ml-5 pt-5">App</div>
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
            <x-slot:sidebar drawer="main-drawer" class="bg-base-100  lg:bg-inherit rounded-r-xl mx-10">

                {{-- BRAND --}}
                <div class="ml-3 mr-5 pt-5 flex items-center justify-between">
                    <div class="flex items-center">
                        <img src="{{ asset('images/plv-logo.png') }}" alt="My Logo" class="h-10 w-10">
                        <h2 class="p-3 text-lg font-semibold font-heading ml-2">Event Scheduling</h2>
                    </div>

                </div>

                {{-- MENU --}}
                <x-mary-menu separator activate-by-route active-bg-color="bg-neutral" class="font-heading">
                    {{-- MENU --}}
                    <x-mary-menu-item title="Dashboard" icon="s-squares-2x2" link="/admin/dashboard" wire:navigate />
                    <x-mary-menu-item title="Event Requests" icon="s-calendar-days" link="/admin/event-req"
                        wire:navigate />
                    <x-mary-menu-item title="Calendar" icon="s-calendar" link="/admin/calendar" wire:navigate />
                    <x-mary-menu-item title="Archives" icon="s-archive-box" link="/admin/archive" wire:navigate />
                    <x-mary-menu-item title="Student Organizations" icon="s-building-office"
                        link="/admin/student-organizations" wire:navigate />
                    <x-mary-menu-item title="Reports" icon="s-chart-bar" link="/admin/reports" wire:navigate />
                    <x-mary-menu-item title="Users/Accounts" icon="s-users" link="/admin/accounts" wire:navigate />

                    <x-mary-menu-sub title="Settings" icon="s-cog-6-tooth">
                        <x-mary-menu-item title="Profile" icon="o-user-circle" link="/admin/profile" wire:navigate />
                        <x-mary-menu-item title="Preferences" icon="o-cog-6-tooth" link="/profile" wire:navigate />
                    </x-mary-menu-sub>
                </x-mary-menu>
            </x-slot:sidebar>
            <x-slot:footer>
                <div class="bg-accent text-center py-2">
                    <p class="text-sm">© 2025 PLV Event Scheduling System - OSA Admin</p>
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

    <h2 class="bg-accent">niga im home</h2>

</body>

</html>
