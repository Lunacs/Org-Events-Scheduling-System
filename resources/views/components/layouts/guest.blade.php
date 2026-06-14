<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
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

<body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-950 lg:overflow-hidden">
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
                        alt="PLV Background" loading="eager" fetchpriority="high" decoding="async"
                        width="1280" height="960">
                </picture>
            </div>
            <div class="absolute inset-0 bg-gradient-to-tr from-secondary/40 via-transparent to-black/20"></div>
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
            <div class="mb-4 sm:mb-6 transform hover:scale-105 transition-transform duration-300 shrink-0">
                <a href="/" wire:navigate class="block">
                    <div class="relative">
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-secondary to-secondary-focus rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200">
                        </div>
                        <img src="{{ asset('images/optimized/osa-logo.webp') }}"
                            class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl shadow-2xl border-2 border-white dark:border-gray-800"
                            alt="OSA Logo" loading="eager" fetchpriority="high" decoding="async"
                            width="200" height="200">
                    </div>
                </a>
            </div>

            <!-- Login Form Container -->
            <div class="w-full max-w-lg shrink-0">
                <div
                    class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl shadow-gray-200/50 dark:shadow-none px-5 py-6 sm:p-8 border border-gray-100 dark:border-gray-800">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center shrink-0">
                <p class="text-[10px] text-gray-400 dark:text-gray-600 font-medium uppercase tracking-widest">
                    &copy; {{ date('Y') }} Office of Student Affairs • PLV
                </p>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
