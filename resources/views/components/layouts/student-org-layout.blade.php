{{-- Student organization portal layout — thin wrapper around the shared app shell (x-layouts.portal). --}}
<x-layouts.portal portal="student-org" label="Student Organization Portal" :title="$title ?? null"
    :vite="['resources/js/avatar.js', 'resources/js/filepond.js']">
    {{ $slot }}
</x-layouts.portal>
