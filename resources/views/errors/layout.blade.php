<!DOCTYPE html>
<html data-theme="emerald" class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/osa-logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/osa-logo.jpg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,900|poppins:400,500,600,900" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Vite (Tailwind/Alpine/Livewire styles) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased bg-base-200">
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 py-6">
        <div class="w-full max-w-2xl">
            <div class="bg-base-100 rounded-box shadow-xl overflow-hidden">
                <div class="px-6 py-8 sm:px-10 sm:py-12">
                    <div class="flex items-center justify-center gap-4 mb-6">
                        <div class="text-3xl font-bold text-primary">
                            @yield('code')
                        </div>
                        <div class="h-6 w-px bg-base-300"></div>
                        <div class="text-lg sm:text-xl text-base-content/90 uppercase tracking-wider">
                            @yield('message')
                        </div>
                    </div>
                    @yield('content')
                    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ url()->previous() }}"
                            class="btn btn-outline hover:outline-primary bg-primary text-white">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Go Back
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-outline">
                            <i class="fas fa-home mr-2"></i>
                            Go to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
