<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Event Scheduling System</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/osa-logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/osa-logo.jpg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-sans">
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div
            class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-1 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:justify-center lg:col-start-2">
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Event Scheduling System</h1>
                    </div>
                </header>

                <main class="mt-6">
                    <div class="text-center mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Welcome to the Event
                            Scheduling System</h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400">Please select your portal to continue</p>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-4 lg:gap-8">
                        <!-- OSA Portal -->
                        <a href="{{ route('admin.login') }}"
                            class="flex flex-col items-center gap-6 overflow-hidden rounded-lg bg-white p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/5 transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
                            <div class="flex size-16 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                <svg class="size-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>

                            <div class="text-center">
                                <h3 class="text-xl font-semibold text-black dark:text-white">OSA Portal</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Office of Student Affairs<br>
                                    Administrative access for event management
                                </p>
                            </div>

                            <svg class="size-6 shrink-0 stroke-blue-600" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                            </svg>
                        </a>

                        <!-- Student Organization Portal -->
                        <a href="{{ route('student-org.login') }}"
                            class="flex flex-col items-center gap-6 overflow-hidden rounded-lg bg-white p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/5 transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
                            <div class="flex size-16 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                                <svg class="size-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>

                            <div class="text-center">
                                <h3 class="text-xl font-semibold text-black dark:text-white">Student Organizations</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Student Organization Portal<br>
                                    Submit and manage event requests
                                </p>
                            </div>

                            <svg class="size-6 shrink-0 stroke-green-600" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                            </svg>
                        </a>

                        <!-- GSO Portal -->
                        <a href="{{ route('gso.login') }}"
                            class="flex flex-col items-center gap-6 overflow-hidden rounded-lg bg-white p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/5 transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
                            <div
                                class="flex size-16 shrink-0 items-center justify-center rounded-full bg-purple-500/10">
                                <svg class="size-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>

                            <div class="text-center">
                                <h3 class="text-xl font-semibold text-black dark:text-white">GSO Portal</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Graduate School Office<br>
                                    Venue & resource approvals
                                </p>
                            </div>

                            <svg class="size-6 shrink-0 stroke-purple-600" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                            </svg>
                        </a>

                        <!-- SuperAdmin Portal -->
                        <a href="{{ route('superadmin.login') }}"
                            class="flex flex-col items-center gap-6 overflow-hidden rounded-lg bg-white p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/5 transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
                            <div class="flex size-16 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                                <svg class="size-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>

                            <div class="text-center">
                                <h3 class="text-xl font-semibold text-black dark:text-white">Super Admin</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    System Administrator<br>
                                    Full system access and user management
                                </p>
                            </div>

                            <svg class="size-6 shrink-0 stroke-red-600" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                            </svg>
                        </a>
                    </div>

                    <!-- Account Creation Notice -->
                    <div class="mt-12 text-center">
                        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-6 max-w-2xl mx-auto">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Account Creation
                                Policy</h3>
                            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <p><strong>SuperAdmin:</strong> Creates OSA and GSO staff accounts</p>
                                <p><strong>OSA:</strong> Creates Student Organization accounts</p>
                                <p><strong>No Public Registration:</strong> All accounts are created by administrators
                                </p>
                            </div>
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    Event Scheduling System v1.0
                </footer>
            </div>
        </div>
    </div>
</body>

</html>
