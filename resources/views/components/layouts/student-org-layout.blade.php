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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased scroll-smooth">
    <div class="min-h-screen">

        {{-- Announcements Banner --}}
        <x-announcement-banner />
        {{-- MAIN --}}
        <x-mary-main full-width>
            {{-- SIDEBAR --}}
            @persist('student-drawer')
                <x-slot:sidebar collapsible withNav drawer="main-drawer"
                    class="bg-base-100 lg:bg-inherit rounded-r-xl border-r border-base-300/60">

                    {{-- BRAND --}}
                    <div class="ml-3 mr-5 pt-5 pb-2 flex items-center justify-between border-b border-base-300/60">
                        <div class="flex items-center">
                            <img src="{{ asset('images/plv-logo.png') }}" alt="PLV Logo" class="h-10 w-10" loading="eager"
                                fetchpriority="high">
                            <div class="pl-3">
                                <h2 class="text-lg font-semibold font-heading">Event Scheduling</h2>
                                <p class="text-xs text-base-content/60">Student Organization Portal</p>
                            </div>
                        </div>
                    </div>

                    {{-- MENU --}}
                    <x-mary-menu separator activate-by-route
                        active-bg-color="bg-neutral text-base-content dark:text-neutral-content"
                        class="font-heading mt-2 [&_a]:rounded-lg [&_a]:transition-colors [&_a]:focus-visible:outline-none [&_a]:focus-visible:ring-2 [&_a]:focus-visible:ring-primary/50">
                        @foreach ([['title' => 'Dashboard', 'icon' => 's-squares-2x2', 'link' => '/student-org/dashboard'], ['title' => 'My Tickets', 'icon' => 's-ticket', 'link' => '/student-org/my-tickets'], ['title' => 'Submit Ticket', 'icon' => 's-document-plus', 'link' => '/student-org/submit-ticket'], ['title' => 'Event Calendar', 'icon' => 's-calendar', 'link' => '/student-org/calendar'], ['title' => 'Reschedule Request', 'icon' => 's-arrow-path', 'link' => '/student-org/reschedule'], ['title' => 'Notifications', 'icon' => 's-bell', 'link' => '/student-org/notifications'], ['title' => 'History', 'icon' => 's-archive-box', 'link' => '/student-org/history']] as $item)
                            <x-mary-menu-item :title="$item['title']" :icon="$item['icon']" :link="$item['link']" wire:navigate />
                        @endforeach
                    </x-mary-menu>
                </x-slot:sidebar>
            @endpersist

            @persist('student-footer')
                <x-slot:footer>
                    <x-footer variant="student-org" />
                </x-slot:footer>
            @endpersist


            {{-- The `$slot` goes here --}}
            <x-slot:content>
                <div class="sticky top-0 z-15 bg-base-100">
                    {{-- Top Navigation Bar - Persisted for SPA-like experience --}}
                    @persist('student-navigation')
                        <livewire:layout.navigation />
                    @endpersist

                    {{-- Announcements Banner --}}
                    {{-- <x-announcement-banner /> --}}
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

    {{-- Performance Monitoring Scripts --}}
    <script src="{{ asset('js/network-aware.js') }}" defer></script>
    <script src="{{ asset('js/lazy-livewire.js') }}" defer></script>
</body>

</html>
