@props([
    'title' => null,
    // Extra Vite entries on top of the shared app bundle (e.g. filepond, avatar).
    'vite' => [],
])

{{--
    Shared <head> for every layout: theme bootstrap (runs before paint so there is
    no light/dark flash), meta, fonts, and the Vite bundle. See DESIGN.md.
--}}
<script>
    (function() {
        var root = document.documentElement;
        var apply = function(dark) {
            root.setAttribute('data-theme', dark ? 'emeraldDark' : 'emerald');
            root.classList.toggle('dark', dark);
            root.style.colorScheme = dark ? 'dark' : 'light';
            document.querySelectorAll('meta[name="theme-color"]').forEach(function(meta) {
                meta.setAttribute('content', dark ? '#0a121e' : '#f3f6fa');
            });
        };
        var stored = function() {
            try {
                return localStorage.getItem('theme') === 'dark';
            } catch (e) {
                return false;
            }
        };

        apply(stored());

        window.__themeToggleChange = function(input) {
            var dark = input.checked;
            try {
                localStorage.setItem('theme', dark ? 'dark' : 'light');
            } catch (e) {}
            apply(dark);
            // Keep every theme toggle on the page in sync (top bar, public header).
            document.querySelectorAll('input.theme-controller').forEach(function(el) {
                if (el !== input) el.checked = dark;
            });
            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: {
                    dark: dark
                }
            }));
        };

        // wire:navigate swaps the document; re-apply the stored theme and toggle state.
        document.addEventListener('livewire:navigated', function() {
            var dark = stored();
            apply(dark);
            document.querySelectorAll('input.theme-controller').forEach(function(el) {
                el.checked = dark;
            });
        });
    })();
</script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#f3f6fa">

<title>{{ filled($title) ? $title . ' · ' . config('app.name') : config('app.name') }}</title>

<link rel="icon" type="image/webp" href="{{ asset('images/optimized/osa-logo.webp') }}">

<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700|ibm-plex-mono:500&display=swap"
    rel="stylesheet" />

@vite(array_merge(['resources/css/app.css', 'resources/css/fontawesome.css', 'resources/js/app.js'], $vite))

{{ $slot }}
