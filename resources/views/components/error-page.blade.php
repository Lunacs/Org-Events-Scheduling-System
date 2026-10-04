@props([
    'code',
    'title',
    'message',
    'hint' => null,
    'icon' => 'o-exclamation-circle',
    // Show a "Reload page" action (for expired or failed requests).
    'reload' => false,
])

{{--
    x-error-page — every HTTP error screen (401, 403, 404, 419, 429, 500, 503).
    Says what happened in plain words, what the user can do next, and offers the
    most useful way out: their dashboard when signed in, otherwise sign in.
--}}
@php
    $user = auth()->user();
    $homeUrl = $user ? route($user->getDashboardRoute()) : route('login');
    $homeLabel = $user ? 'Go to your dashboard' : 'Go to sign in';
    $previous = url()->previous();
    $canGoBack = $previous && $previous !== url()->current();
@endphp
<!DOCTYPE html>
<html data-theme="emerald" class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-layouts.partials.head :title="$title" />
</head>

<body class="min-h-full bg-base-200 font-sans text-base-content antialiased">
    <main class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <a href="{{ url('/') }}" class="mb-8 flex items-center gap-3">
            <img src="{{ asset('images/optimized/plv-logo.webp') }}" alt="" class="size-10" width="80" height="80">
            <span class="leading-tight">
                <span class="block text-sm font-semibold">Event Scheduling</span>
                <span class="block text-xs text-base-content/70">PLV Office of Student Affairs</span>
            </span>
        </a>

        <div class="w-full max-w-lg rounded-box border border-base-300 bg-base-100 p-6 sm:p-8">
            <div class="flex items-center gap-3">
                <x-ui.icon :name="$icon" class="size-6 shrink-0 text-base-content/60" aria-hidden="true" />
                <p class="font-mono text-sm text-base-content/70">Error {{ $code }}</p>
            </div>
            <h1 class="mt-3 text-2xl font-semibold tracking-[-0.015em] text-base-content">{{ $title }}</h1>
            <p class="mt-2 text-[0.9375rem] leading-relaxed text-base-content/80">{{ $message }}</p>
            @if ($hint)
                <p class="mt-3 text-sm text-base-content/70">{{ $hint }}</p>
            @endif

            {{ $slot }}

            <div class="mt-6 flex flex-wrap gap-3">
                @if ($reload)
                    <button type="button" onclick="window.location.reload()" class="btn btn-primary">Reload page</button>
                    <a href="{{ $homeUrl }}" class="btn border-base-300 bg-base-100">{{ $homeLabel }}</a>
                @else
                    <a href="{{ $homeUrl }}" class="btn btn-primary">{{ $homeLabel }}</a>
                @endif
                @if ($canGoBack)
                    <a href="{{ $previous }}" class="btn btn-ghost">
                        <x-ui.icon name="o-arrow-left" class="size-4" aria-hidden="true" />
                        Go back
                    </a>
                @endif
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-base-content/70">
            Need help? Email the Office of Student Affairs at
            <a href="mailto:plv.osa.official@gmail.com" class="font-medium text-base-content hover:underline">plv.osa.official@gmail.com</a>.
        </p>
    </main>
</body>

</html>
