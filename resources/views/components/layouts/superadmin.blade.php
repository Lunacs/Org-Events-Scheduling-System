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

    <!-- Resource Hints for Performance -->
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <!-- Fonts with optimized loading -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,900|poppins:400,500,600,900&display=swap"
        rel="stylesheet" />

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased scroll-smooth">
    <div class="min-h-screen">

        {{-- MAIN --}}
        <x-mary-main full-width>
            @persist('superadmin-sidebar')
                {{-- SIDEBAR --}}
                <x-slot:sidebar collapsible withNav drawer="main-drawer" class="bg-base-100 lg:bg-inherit rounded-r-xl">

                    {{-- BRAND --}}
                    <div class="ml-3 mr-5 pt-5 flex items-center justify-between">
                        <div class="flex items-center">
                            <img src="{{ asset('images/plv-logo.png') }}" alt="PLV Logo" class="h-10 w-10" loading="eager"
                                fetchpriority="high">
                            <h2 class="p-3 text-xl font-bold ml-2">SuperAdmin</h2>
                        </div>

                    </div>

                    {{-- MENU --}}
                    <x-mary-menu separator activate-by-route active-bg-color="bg-accent" class="font-heading">
                        <x-mary-menu-item title="Dashboard" icon="o-squares-2x2" link="{{ route('superadmin.dashboard') }}"
                            class="tooltip tooltip-right" data-tip="Dashboard" wire:navigate.hover />

                        <x-mary-menu-separator />

                        <x-mary-menu-item title="User Management" icon="o-users" link="{{ route('superadmin.users') }}"
                            class="tooltip tooltip-right" data-tip="User Management" wire:navigate.hover />
                        <x-mary-menu-item title="Roles & Permissions" icon="o-key" link="{{ route('superadmin.roles') }}"
                            wire:navigate.hover />

                        <x-mary-menu-separator />

                        <x-mary-menu-item title="Event Calendar" icon="o-calendar" link="{{ route('superadmin.calendar') }}"
                            wire:navigate.hover />
                        {{-- <x-mary-menu-item title="Ticket Management" icon="o-ticket"
                            link="{{ route('superadmin.tickets') }}" wire:navigate.hover />
                        <x-mary-menu-item title="Archive Management" icon="o-archive-box"
                            link="{{ route('superadmin.archive') }}" wire:navigate.hover /> --}}

                        <x-mary-menu-separator />

                        <x-mary-menu-item title="System Notifications" icon="o-bell"
                            link="{{ route('superadmin.notifications') }}" wire:navigate.hover />

                        <x-mary-menu-separator />

                        <x-mary-menu-item title="Reports & Analytics" icon="o-chart-bar"
                            link="{{ route('superadmin.reports') }}" wire:navigate.hover />
                        <x-mary-menu-item title="Transaction Logs" icon="o-clipboard-document-list"
                            link="{{ route('superadmin.logs') }}" wire:navigate.hover />

                        <x-mary-menu-separator />

                        {{-- Content Management --}}
                        <x-mary-menu-item title="FAQ Management" icon="o-question-mark-circle"
                            link="{{ route('superadmin.faqs') }}" wire:navigate.hover />

                        {{-- <x-mary-menu-sub title="System Settings" icon="o-cog-6-tooth" :active="false">
                            <x-mary-menu-item title="Student Organizations" icon="o-building-office-2"
                                link="{{ route('superadmin.system-settings', ['activeTab' => 'organizations']) }}"
                                :active="request('activeTab') == 'organizations' ||
                                    (request()->routeIs('superadmin.system-settings') && !request('activeTab'))" wire:navigate.hover />
                            <x-mary-menu-item title="Courses" icon="o-academic-cap"
                                link="{{ route('superadmin.system-settings', ['activeTab' => 'courses']) }}"
                                :active="request('activeTab') == 'courses'" wire:navigate.hover />
                            <x-mary-menu-item title="Event Types" icon="o-calendar-days"
                                link="{{ route('superadmin.system-settings', ['activeTab' => 'event-types']) }}"
                                :active="request('activeTab') == 'event-types'" wire:navigate.hover />
                            <x-mary-menu-item title="Content Section" icon="o-document-text"
                                link="{{ route('superadmin.system-settings', ['activeTab' => 'content']) }}"
                                :active="request('activeTab') == 'content'" wire:navigate.hover />
                        </x-mary-menu-sub> --}}

                        <x-mary-menu-item title="System Settings" icon="o-cog-6-tooth"
                            link="{{ route('superadmin.system-settings') }}" wire:navigate.hover />

                        <x-mary-menu-separator />
                    </x-mary-menu>

                </x-slot:sidebar>
            @endpersist

            @persist('superadmin-footer')
                <x-slot:footer>
                    <x-footer variant="superadmin" />
                </x-slot:footer>
            @endpersist

            {{-- Content --}}
            <x-slot:content>
                <div class="sticky top-0 z-15">
                    {{-- Top Navigation Bar - Persisted for SPA-like experience --}}
                    @persist('superadmin-navigation')
                        <livewire:layout.navigation />
                    @endpersist

                    {{-- Announcements Banner --}}
                    <x-announcement-banner />
                </div>

                {{-- Page Content --}}
                {{ $slot }}
                <x-rich-text::styles theme="richtextlaravel" />
            </x-slot:content>
        </x-mary-main>

        <x-mary-toast />

    </div>

    {{-- Scripts Stack --}}
    @stack('scripts')

    {{-- Performance Monitoring Scripts --}}
    <script src="{{ asset('js/network-aware.js') }}" defer></script>
    <script src="{{ asset('js/lazy-livewire.js') }}" defer></script>
</body>

</html>
