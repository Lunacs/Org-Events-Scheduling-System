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
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>
</head>

<body class="font-sans antialiased scroll-smooth">
    <div class="min-h-screen">

        {{-- MAIN --}}
        <x-mary-main full-width>
            {{-- SIDEBAR - Persisted for SPA-like experience --}}
            @persist('osa-sidebar')
                <x-slot:sidebar drawer="main-drawer" class="bg-base-100 lg:bg-inherit rounded-r-xl lg:pl-10">

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
                        <x-mary-menu-item title="Dashboard" icon="s-squares-2x2" link="/admin/dashboard"
                            wire:navigate.prefetch />
                        <x-mary-menu-item title="Ticket Management" icon="s-ticket" link="/admin/tickets"
                            wire:navigate.prefetch />
                        <x-mary-menu-item title="Ticket Review & Approvals" icon="s-clipboard-document-check"
                            link="/admin/ticket-review" wire:navigate.prefetch />
                        <x-mary-menu-item title="Event Calendar" icon="s-calendar-days" link="/admin/calendar"
                            wire:navigate.prefetch />
                        <x-mary-menu-item title="Notifications" icon="s-bell" link="/admin/notifications" wire:navigate.prefetch />
                        <x-mary-menu-item title="Reports" icon="s-chart-bar" link="/admin/reports" wire:navigate.prefetch />
                        <x-mary-menu-item title="Archives" icon="s-archive-box" link="/admin/archive"
                            wire:navigate.prefetch />
                    </x-mary-menu>
                </x-slot:sidebar>
            @endpersist

            @persist('osa-footer')
                <x-slot:footer>
                    <div class="bg-accent text-center py-2">
                        <p class="text-sm">© {{ date('Y') }} PLV Event Scheduling System - OSA Admin</p>
                    </div>
                </x-slot:footer>
            @endpersist

            {{-- The `$slot` goes here --}}
            <x-slot:content>
                <div class="sticky top-0 z-15">
                    {{-- Top Navigation Bar - Persisted for SPA-like experience --}}
                    @persist('osa-navigation')
                        <livewire:layout.navigation />
                    @endpersist
                </div>

                {{-- Page Content --}}
                {{ $slot }}
            </x-slot:content>
        </x-mary-main>

        {{-- Toast --}}
        <x-mary-toast />
    </div>

    {{-- Scripts Stack --}}
    @stack('scripts')
</body>

</html>
