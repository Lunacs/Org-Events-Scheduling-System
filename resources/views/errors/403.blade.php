<!DOCTYPE html>
<html data-theme="emerald" class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Access Denied - {{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/osa-logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/osa-logo.jpg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,900|poppins:400,500,600,900" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased bg-base-200">
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 py-4">
        <div class="max-w-2xl w-full">
            <!-- Error Card -->
            <div class="bg-base-100 rounded-box shadow-xl overflow-hidden">
                <!-- Header Section with Icon -->
                <div class="bg-error px-6 py-8 sm:px-10 sm:py-12 text-center">
                    <div
                        class="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 bg-base-100 rounded-full mb-4">
                        <i class="fas fa-shield-halved text-error text-4xl sm:text-5xl"></i>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-heading font-bold text-error-content mb-2">
                        Access Denied
                    </h1>
                    <p class="text-lg sm:text-xl text-error-content opacity-90">
                        Error 403 - Forbidden
                    </p>
                </div>

                <!-- Content Section -->
                <div class="px-6 py-8 sm:px-10 sm:py-10">
                    <div class="text-center mb-8">
                        <p class="text-base-content text-lg mb-4">
                            You don't have permission to access this resource.
                        </p>
                        <p class="text-base-content/70 text-base">
                            {{ $exception->getMessage() ?: 'This action is unauthorized. Please contact your administrator if you believe this is an error.' }}
                        </p>
                    </div>

                    <!-- Info Alert -->
                    <div class="alert alert-warning mb-6">
                        <i class="fas fa-info-circle text-xl"></i>
                        <div>
                            <h3 class="font-semibold">Why am I seeing this?</h3>
                            <div class="text-sm opacity-90">
                                You may be trying to access a restricted area or perform an action you're not authorized
                                for.
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ url()->previous() }}"
                            class="btn btn-outline hover:outline-primary bg-primary text-white">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Go Back
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline hover:outline-primary">
                            <i class="fas fa-home mr-2"></i>
                            Go to Login
                        </a>
                    </div>

                    <!-- Additional Help -->
                    <div class="mt-8 pt-6 border-t border-base-300 text-center">
                        <p class="text-sm text-base-content/60">
                            Need help? Contact your system administrator or
                            <a href="mailto:support@example.com" class="text-primary hover:underline font-medium">
                                support team
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="mt-6 text-center">
                <div class="inline-flex items-center gap-2 text-sm text-base-content/50">
                    <i class="fas fa-lock"></i>
                    <span>This access attempt has been logged for security purposes</span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
