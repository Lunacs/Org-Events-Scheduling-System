<!DOCTYPE html>
<html data-theme="emerald" class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,900|poppins:400,500,600,900" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100">

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
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 dark:bg-gray-900 lg:bg-inherit rounded-r-xl">

            {{-- BRAND --}}
            <div class="ml-3 mr-5 pt-5 flex items-center justify-between">
                <div class="flex items-center">
                    <img src="{{ asset('images/plv-logo.png') }}" alt="My Logo" class="h-10 w-10">
                    <h2 class="p-3 text-xl font-bold ml-2">Event Scheduling</h2>
                </div>

            </div>

            {{-- MENU --}}
            <x-mary-menu separator activate-by-route active-bg-color="bg-accent" class="font-heading">

                {{--                --}}{{-- User --}}
                {{--                @if($user = auth()->user())--}}
                {{--                    <x-mary-menu-separator/>--}}

                {{--                    <x-mary-list-item :item="$user" value="name" sub-value="email" no-separator no-hover--}}
                {{--                                 class="-mx-2 !-my-2 rounded">--}}
                {{--                        <x-slot:actions>--}}
                {{--                            <x-mary-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff"--}}
                {{--                                      no-wire-navigate link="/logout"/>--}}
                {{--                        </x-slot:actions>--}}
                {{--                    </x-mary-list-item>--}}

                {{--                    <x-mary-menu-separator/>--}}
                {{--                @endif--}}

                {{-- MENU --}}
                <x-mary-menu-item  title="Dashboard" icon="o-squares-2x2" link="/dashboard" wire:navigate/>
                <x-mary-menu-item title="Event Requests" icon="o-calendar-days" link="/event-req" wire:navigate/>
                <x-mary-menu-item title="Calendar" icon="o-calendar" link="/calendar" wire:navigate/>
                <x-mary-menu-item title="Archives" icon="o-archive-box" link="/archive" wire:navigate/>
                <x-mary-menu-item title="Student Organizations" icon="o-building-office" link="/organizations" wire:navigate/>
                <x-mary-menu-item title="Reports" icon="o-chart-bar" link="/reports" wire:navigate/>
                <x-mary-menu-item title="Users/Accounts" icon="o-users" link="/accounts" wire:navigate/>


                <x-mary-menu-sub title="Settings" icon="o-cog-6-tooth">
                    <x-mary-menu-item title="Wifi" icon="o-wifi" link="####"/>
                    <x-mary-menu-item title="Archives" icon="o-archive-box" link="####"/>
                </x-mary-menu-sub>
            </x-mary-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-mary-main>

    {{-- Toast --}}
    <x-mary-toast/>
</div>
</body>
</html>
