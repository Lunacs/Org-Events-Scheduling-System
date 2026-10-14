<!DOCTYPE html>
<html data-theme="emerald" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-layouts.partials.head :title="$title ?? null">
        {{-- Preload the campus photo (desktop only) for LCP --}}
        <link rel="preload" as="image" type="image/webp"
            imagesrcset="{{ asset('images/optimized/hero-480w.webp') }} 480w, {{ asset('images/optimized/hero-768w.webp') }} 768w, {{ asset('images/optimized/hero-1024w.webp') }} 1024w, {{ asset('images/optimized/hero-1280w.webp') }} 1280w"
            imagesizes="(max-width: 1023px) 0px, 50vw">
    </x-layouts.partials.head>
</head>

{{--
    Guest shell for sign-in, password reset, and verification (DESIGN.md "The Well-Run Office").
    Campus photo with the institution's name on large screens; one calm form panel.
--}}
<body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
    <a href="#main"
        class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-70 focus:rounded-field focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-primary-content">
        Skip to form
    </a>

    <div class="flex min-h-screen w-full">
        {{-- Campus photo (large screens) --}}
        <div class="relative hidden w-1/2 overflow-hidden bg-primary lg:block">
            <picture>
                <source type="image/webp"
                    srcset="{{ asset('images/optimized/hero-480w.webp') }} 480w, {{ asset('images/optimized/hero-768w.webp') }} 768w, {{ asset('images/optimized/hero-1024w.webp') }} 1024w, {{ asset('images/optimized/hero-1280w.webp') }} 1280w"
                    sizes="50vw">
                <img src="{{ asset('images/suhay husay.png') }}" class="absolute inset-0 size-full object-cover"
                    alt="Pamantasan ng Lungsod ng Valenzuela main building" loading="eager" fetchpriority="high"
                    decoding="async" width="1280" height="960">
            </picture>
            {{-- Scrim so the caption stays readable on any part of the photo --}}
            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-linear-to-t from-primary/95 via-primary/60 to-transparent"
                aria-hidden="true"></div>
            <div class="absolute inset-x-10 bottom-10 text-primary-content xl:inset-x-12 xl:bottom-12">
                <div class="mb-5 flex items-center gap-3">
                    <img src="{{ asset('images/optimized/plv-logo.webp') }}" alt="" class="size-11" width="80"
                        height="80">
                    <img src="{{ asset('images/optimized/osa-logo.webp') }}" alt="" class="size-11 rounded-full"
                        width="200" height="200">
                </div>
                <p class="text-sm font-medium text-primary-content/80">Pamantasan ng Lungsod ng Valenzuela &middot; Office of
                    Student Affairs</p>
                <p class="mt-2 max-w-md text-3xl font-semibold leading-tight tracking-[-0.015em] xl:text-4xl">
                    Organization Events Scheduling System
                </p>
                <p class="mt-3 max-w-md text-[0.9375rem] text-primary-content/85">
                    Submit event requests, follow every approval step, and see the campus calendar in one place.
                </p>
            </div>
        </div>

        {{-- Form column --}}
        <div class="flex w-full flex-col lg:w-1/2">
            <main id="main" class="flex flex-1 flex-col items-center justify-center px-4 py-10 sm:px-8">
                <a href="{{ url('/') }}" wire:navigate class="mb-8 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('images/optimized/plv-logo.webp') }}" alt="" class="size-10" width="80"
                        height="80">
                    <span class="text-left leading-tight">
                        <span class="block text-sm font-semibold">Event Scheduling</span>
                        <span class="block text-xs text-base-content/70">PLV Office of Student Affairs</span>
                    </span>
                </a>

                <div class="w-full max-w-md rounded-box border border-base-300 bg-base-100 px-5 py-7 sm:px-8 sm:py-9">
                    {{ $slot }}
                </div>
            </main>

            <footer class="px-4 pb-6 text-center text-xs text-base-content/70">
                &copy; {{ date('Y') }} Office of Student Affairs &middot; Pamantasan ng Lungsod ng Valenzuela
                <span class="mx-1" aria-hidden="true">&middot;</span>
                <a href="{{ route('data-privacy') }}" class="hover:text-base-content hover:underline">Data privacy</a>
            </footer>
        </div>
    </div>

    <x-ui.toast />
    @stack('scripts')
</body>

</html>
