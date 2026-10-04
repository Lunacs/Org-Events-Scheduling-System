{{-- SuperAdmin portal layout — thin wrapper around the shared app shell (x-layouts.portal). --}}
@php
    $menuItems = [
        [
            'title' => 'Dashboard',
            'icon' => 'fa-solid fa-table-columns',
            'link' => route('superadmin.dashboard'),
            'pattern' => 'superadmin/dashboard*',
        ],
        [
            'title' => 'User Management',
            'icon' => 'fa-solid fa-users',
            'link' => route('superadmin.users'),
            'pattern' => 'superadmin/users*',
        ],
        [
            'title' => 'Roles & Permissions',
            'icon' => 'fa-solid fa-key',
            'link' => route('superadmin.roles'),
            'pattern' => 'superadmin/roles*',
        ],
        [
            'title' => 'Event Calendar',
            'icon' => 'fa-solid fa-calendar-days',
            'link' => route('superadmin.calendar'),
            'pattern' => 'superadmin/calendar*',
        ],
        [
            'title' => 'Ticket Management',
            'icon' => 'fa-solid fa-ticket',
            'link' => route('superadmin.tickets'),
            'pattern' => 'superadmin/tickets*',
        ],
        [
            'title' => 'Archive Management',
            'icon' => 'fa-solid fa-box-archive',
            'link' => route('superadmin.archive'),
            'pattern' => 'superadmin/archive*',
        ],
        [
            'title' => 'System Notifications',
            'icon' => 'fa-solid fa-bell',
            'link' => route('superadmin.notifications'),
            'pattern' => 'superadmin/notifications*',
        ],
        [
            'title' => 'Reports & Analytics',
            'icon' => 'fa-solid fa-chart-bar',
            'link' => route('superadmin.reports'),
            'pattern' => 'superadmin/reports*',
        ],
        [
            'title' => 'Transaction Logs',
            'icon' => 'fa-solid fa-clipboard-list',
            'link' => route('superadmin.logs'),
            'pattern' => 'superadmin/logs*',
        ],
        [
            'title' => 'FAQ Management',
            'icon' => 'fa-solid fa-circle-question',
            'link' => route('superadmin.faqs'),
            'pattern' => 'superadmin/faqs*',
        ],
        [
            'title' => 'System Settings',
            'icon' => 'fa-solid fa-gear',
            'link' => route('superadmin.system-settings'),
            'pattern' => 'superadmin/system-settings*',
        ],
    ];
@endphp

<x-layouts.portal portal="superadmin" label="SuperAdmin Portal" :items="$menuItems" :title="$title ?? null"
    :vite="['resources/js/avatar.js']">
    {{ $slot }}
    <x-rich-text::styles theme="richtextlaravel" />
</x-layouts.portal>
