{{-- GSO portal layout — thin wrapper around the shared app shell (x-layouts.portal). --}}
<x-layouts.portal portal="gso" label="GSO Portal" :title="$title ?? null" :vite="['resources/js/avatar.js']">
    {{ $slot }}
</x-layouts.portal>
