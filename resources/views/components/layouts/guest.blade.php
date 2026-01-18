<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

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

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-950 overflow-hidden">
    <div class="flex h-screen w-full p-3 sm:p-4 lg:p-6 gap-4">
        <!-- Left side - Image (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-[55%] relative overflow-hidden rounded-3xl shadow-2xl h-full">
            <div class="absolute inset-0">
                <img src="{{ asset('images/suhay husay.png') }}"
                    class="w-full h-full object-cover scale-105 hover:scale-100 transition-transform duration-10000 ease-linear"
                    alt="PLV Background" loading="eager" fetchpriority="high">
            </div>
            <div class="absolute inset-0 bg-gradient-to-tr from-secondary/40 via-transparent to-black/20"></div>
            <div class="absolute bottom-10 left-10 right-10 text-white z-10">
                <h2 class="text-3xl xl:text-4xl font-black mb-3 drop-shadow-lg">Org Events Scheduling System</h2>
                <p class="text-base xl:text-lg font-medium opacity-90 drop-shadow-md max-w-lg">Streamlining event
                    management and
                    approvals for a more vibrant campus life.</p>
            </div>
        </div>

        <!-- Right side - Login Form -->
        <div class="flex flex-col justify-center items-center w-full lg:w-[45%] p-4 lg:p-6 h-full">
            <!-- Logo -->
            <div class="mb-6 transform hover:scale-105 transition-transform duration-300 shrink-0">
                <a href="/" wire:navigate class="block">
                    <div class="relative">
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-secondary to-secondary-focus rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200">
                        </div>
                        <img src="{{ asset('images/osa-logo.jpg') }}"
                            class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl shadow-2xl border-2 border-white dark:border-gray-800"
                            alt="OSA Logo" loading="eager" fetchpriority="high">
                    </div>
                </a>
            </div>

            <!-- Login Form Container -->
            <div class="w-full max-w-lg shrink-0">
                <div
                    class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl shadow-gray-200/50 dark:shadow-none p-6 sm:p-8 border border-gray-100 dark:border-gray-800">
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
