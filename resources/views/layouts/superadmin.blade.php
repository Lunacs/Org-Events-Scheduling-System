<!DOCTYPE html>
<html data-theme="emerald" class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/osa-logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/osa-logo.jpg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,900|poppins:400,500,600,900" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">

        {{-- NAVBAR mobile only --}}
        <x-mary-nav sticky class="lg:hidden">
            <x-slot:brand>
                <div class="ml-5 pt-5">SuperAdmin</div>
            </x-slot:brand>
            <x-slot:actions>
                <label for="superadmin-drawer" class="lg:hidden mr-3">
                    <i class="fas fa-burger cursor-pointer"></i>
                </label>
            </x-slot:actions>
        </x-mary-nav>

        {{-- MAIN --}}
        <x-mary-main full-width>
            {{-- SIDEBAR --}}
            <x-slot:sidebar drawer="superadmin-drawer" collapsible
                class="bg-base-100 dark:bg-gray-900 lg:bg-inherit rounded-r-xl">

                {{-- BRAND --}}
                <div class="ml-3 mr-5 pt-5 flex items-center justify-between">
                    <div class="flex items-center">
                        <img src="{{ asset('images/plv-logo.png') }}" alt="My Logo" class="h-10 w-10">
                        <h2 class="p-3 text-xl font-bold ml-2">SuperAdmin</h2>
                    </div>

                </div>

                {{-- MENU --}}
                <x-mary-menu separator activate-by-route active-bg-color="bg-accent" class="font-heading">
                    <x-mary-menu-item title="Dashboard" icon="o-squares-2x2" link="/superadmin/dashboard"
                        wire:navigate />
                    <x-mary-menu-item title="User Management" icon="o-users" link="/superadmin/users" wire:navigate />
                    <x-mary-menu-item title="Roles & Permissions" icon="o-key" link="/superadmin/roles"
                        wire:navigate />
                    <x-mary-menu-item title="System Settings" icon="o-cog-6-tooth" link="/superadmin/system-settings"
                        wire:navigate />
                    <x-mary-menu-item title="Transaction Logs" icon="o-clipboard-document-list" link="/superadmin/logs"
                        wire:navigate />
                    <x-mary-menu-item title="Archive Management" icon="o-archive-box" link="/superadmin/archive"
                        wire:navigate />
                    <x-mary-menu-item title="Reports & Analytics" icon="o-chart-bar" link="/superadmin/reports"
                        wire:navigate />

                    <x-mary-menu-separator />
                    <x-mary-menu-sub title="Account" icon="o-user-circle">
                        <x-mary-menu-item title="Profile" icon="o-user" link="/profile" wire:navigate />
                        <x-mary-menu-item title="Go to OSA" icon="o-arrow-uturn-left" link="/admin" wire:navigate />
                    </x-mary-menu-sub>
                </x-mary-menu>
            </x-slot:sidebar>

            {{-- Content --}}
            <x-slot:content>
                {{ $slot }}
            </x-slot:content>
        </x-mary-main>

        <x-mary-toast />
    </div>
</body>

</html>
