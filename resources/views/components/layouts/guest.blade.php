<!DOCTYPE html>
<html data-theme="emerald" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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
            document.documentElement.setAttribute('data-theme', dark ? 'emeraldDark' : 'emerald');
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

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="{{ asset('images/optimized/osa-logo.webp') }}">

    <!-- Preload hero image for LCP -->
    <link rel="preload" as="image" type="image/webp"
        imagesrcset="{{ asset('images/optimized/hero-480w.webp') }} 480w, {{ asset('images/optimized/hero-768w.webp') }} 768w, {{ asset('images/optimized/hero-1024w.webp') }} 1024w, {{ asset('images/optimized/hero-1280w.webp') }} 1280w"
        imagesizes="(max-width: 1023px) 0px, 55vw">

    <!-- Fonts — single consolidated request, non-render-blocking -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts — minimal: only core CSS + JS (no avatar, calendar, charts) -->
    @vite(['resources/css/app.css', 'resources/css/fontawesome.css', 'resources/js/app.js'])

</head>

<body class="font-sans text-base-content antialiased bg-base-200 lg:overflow-hidden">
    <div class="flex min-h-screen lg:h-screen w-full p-3 sm:p-4 lg:p-6 gap-4">
        <!-- Left side - Image (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-[55%] relative overflow-hidden rounded-3xl shadow-2xl h-full">
            <div class="absolute inset-0">
                <picture>
                    <source type="image/webp"
                        srcset="{{ asset('images/optimized/hero-480w.webp') }} 480w, {{ asset('images/optimized/hero-768w.webp') }} 768w, {{ asset('images/optimized/hero-1024w.webp') }} 1024w, {{ asset('images/optimized/hero-1280w.webp') }} 1280w"
                        sizes="55vw">
                    <img src="{{ asset('images/suhay husay.png') }}"
                        class="w-full h-full object-cover scale-105 hover:scale-100 transition-transform duration-10000 ease-linear"
                        alt="PLV Background" loading="eager" fetchpriority="high" decoding="async" width="1280"
                        height="960">
                </picture>
            </div>
            <div class="absolute inset-0 bg-gradient-to-tr from-primary/70 via-primary/10 to-black/30"></div>
            <div class="absolute top-10 left-10 h-1 w-16 rounded-full bg-accent"></div>
            <div class="absolute bottom-10 left-10 right-10 text-white z-10">
                <h2 class="text-3xl xl:text-4xl font-black mb-3 drop-shadow-lg">Organization Events Scheduling System
                </h2>
                <p class="text-base xl:text-lg font-medium opacity-90 drop-shadow-md max-w-lg">Streamlining event
                    management and
                    approvals for a more vibrant campus life.</p>
            </div>
        </div>

        <!-- Right side - Login Form -->
        <div
            class="flex flex-col justify-start items-center w-full lg:w-[45%] px-2 pt-10 sm:pt-0 sm:p-4 sm:justify-center lg:p-6 lg:h-full lg:overflow-y-auto">
            <!-- Logo -->
            <div class="mb-4 sm:mb-6 shrink-0">
                <a href="/" wire:navigate class="block">
                    <div class="relative">
                        <div
                            class="absolute -inset-1 bg-accent rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200">
                        </div>
                        <img src="{{ asset('images/optimized/osa-logo.webp') }}"
                            class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl shadow-2xl border-2 border-white"
                            alt="OSA Logo" loading="eager" fetchpriority="high" decoding="async" width="200"
                            height="200">
                    </div>
                </a>
            </div>

            <!-- Login Form Container -->
            <div class="w-full max-w-lg shrink-0">
                <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 px-5 py-6 sm:p-8 border border-gray-100">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center shrink-0">
                <p class="text-[10px] text-base-content/40 font-medium uppercase tracking-widest">
                    &copy; {{ date('Y') }} Office of Student Affairs • PLV
                </p>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
