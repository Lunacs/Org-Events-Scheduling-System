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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/css/fontawesome.css', 'resources/js/app.js', 'resources/js/avatar.js'])
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>

    @stack('head')
</head>

<body class="font-sans antialiased scroll-smooth bg-base-100">
    <div class="min-h-screen flex flex-col">

        {{-- Public Navigation Header --}}
        <header class="sticky top-0 z-50 bg-base-100 border-b border-base-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    {{-- Brand --}}
                    <a href="/" wire:navigate class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                        <img src="{{ asset('images/optimized/plv-logo.webp') }}" alt="PLV Logo" class="h-10 w-10"
                            loading="eager" width="80" height="80">
                        <h2 class="text-lg font-semibold font-heading">Event Scheduling</h2>
                    </a>

                    {{-- Navigation Links --}}
                    <nav class="flex items-center gap-3">
                        {{-- Theme Toggle --}}
                        <label class="swap swap-rotate btn btn-ghost btn-sm btn-circle" aria-label="Toggle theme">
                            <input id="theme-toggle" type="checkbox" value="emeraldDark"
                                class="theme-controller opacity-0" onchange="window.__themeToggleChange(this)" />
                            <x-ui.icon name="o-sun" class="swap-off" />
                            <x-ui.icon name="o-moon" class="swap-on" />
                        </label>

                        @auth
                            @php $user = auth()->user(); @endphp
                            {{-- User Profile Dropdown --}}
                            <x-ui.dropdown right width="auto" class="p-2 border border-base-content/10 min-w-max shadow">

                                {{-- Trigger Button --}}
                                <x-slot:trigger>
                                    <div class="btn btn-ghost btn-sm gap-2 hover:bg-base-200 transition-colors">
                                        <x-ui.avatar :user="$user" size="sm" nav="true" />
                                        <span
                                            class="hidden md:inline-block max-w-[150px] truncate">{{ $user->name }}</span>
                                        <svg class="w-4 h-4 hidden md:block" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </x-slot:trigger>

                                {{-- Dropdown Content --}}
                                <div class="w-72 bg-base-100 rounded-box border border-base-300">
                                    {{-- User Info Header --}}
                                    <div class="px-4 py-3 bg-base-200 rounded-t-box border-b border-base-300">
                                        <div class="flex items-center gap-3">
                                            <x-ui.avatar :user="$user" size="lg" />
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-bold text-base text-base-content truncate">
                                                    {{ $user->name }}
                                                </h4>
                                                <p class="text-xs text-base-content/70 truncate">{{ $user->email }}</p>
                                                @if ($user->role)
                                                    <div class="mt-1">
                                                        <span
                                                            class="badge badge-primary badge-xs text-neutral-content dark:text-base-200">
                                                            {{ $user->role_display }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Menu Items --}}
                                    <div class="p-2">
                                        {{-- Go to Dashboard --}}
                                        <a href="{{ route($user->getDashboardRoute()) }}" wire:navigate
                                            class="py-3 px-4 hover:bg-base-200 transition-colors flex items-center gap-3 rounded-lg">
                                            <i class="fa-solid fa-gauge-high w-5"></i>
                                            <span class="font-medium">Dashboard</span>
                                        </a>
                                        {{-- Profile --}}
                                        <a href="{{ route('profile') }}" wire:navigate
                                            class="py-3 px-4 hover:bg-base-200 transition-colors flex items-center gap-3 rounded-lg">
                                            <i class="fa-solid fa-user-circle w-5"></i>
                                            <span class="font-medium">Profile</span>
                                        </a>
                                    </div>

                                    <div class="divider my-0"></div>

                                    {{-- Logout --}}
                                    <div class="p-2">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit"
                                                class="w-full py-3 px-4 text-error hover:bg-error/10 hover:cursor-pointer transition-colors flex items-center gap-3 rounded-lg">
                                                <i class="fa-solid fa-right-from-bracket w-5"></i>
                                                <span class="font-medium">Log Out</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </x-ui.dropdown>
                        @else
                            {{-- Login Button for Guests --}}
                            <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-sm gap-2">
                                <i class="fas fa-sign-in-alt"></i>
                                Login
                            </a>
                        @endauth
                    </nav>
                </div>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <x-footer variant="osa" />

        {{-- Toast --}}
        <x-ui.toast />
    </div>

    {{-- Scripts Stack --}}
    @stack('scripts')
</body>

</html>
