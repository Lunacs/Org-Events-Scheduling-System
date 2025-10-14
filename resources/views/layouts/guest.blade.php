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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="flex min-h-screen w-full bg-white p-20">
        <!-- Left side - Image -->
        <div class="hidden lg:flex lg:w-[55%] relative">
            <div class="absolute inset-0">
                <img src="{{ asset('images/suhay husay.png') }}"
                    class="w-full h-full object-cover opacity-80 rounded-xl" alt="PLV Background">
            </div>
            <div class="absolute inset-0 bg-gradient-to-r from-black/20 to-transparent"></div>
        </div>

        <!-- Right side - Login Form -->
        <div
            class="flex flex-col justify-center items-center w-full lg:w-[40%] bg-white rounded-xl shadow-xl dark:bg-gray-800 p-6">
            <!-- Logo -->
            <div class="mb-8">
                <a href="/" wire:navigate>
                    <img src="{{ asset('images/osa-logo.jpg') }}" class="w-20 h-20 rounded-lg shadow-md" alt="OSA Logo">
                </a>
            </div>

            <!-- Login Form Container -->
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>
