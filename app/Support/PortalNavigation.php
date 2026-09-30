<?php

namespace App\Support;

use App\Models\User;

/**
 * The pages of each portal and the permission each one requires.
 *
 * Drives both the portal sidebars and where a user lands after signing in.
 */
class PortalNavigation
{
    /**
     * Route shown to users whose role has no permitted pages.
     */
    public const NO_ACCESS_ROUTE = 'no-access';

    /**
     * @var array<string, list<array{title: string, icon: string, route: string, pattern: string, permission: string}>>
     */
    public const MENUS = [
        'osa' => [
            ['title' => 'Dashboard', 'icon' => 'fa-solid fa-table-columns', 'route' => 'admin.dashboard', 'pattern' => 'admin/dashboard*', 'permission' => 'dashboard.view'],
            ['title' => 'Ticket Management', 'icon' => 'fa-solid fa-ticket', 'route' => 'admin.ticket', 'pattern' => 'admin/tickets*', 'permission' => 'tickets.view'],
            ['title' => 'Ticket Review & Approvals', 'icon' => 'fa-solid fa-clipboard-check', 'route' => 'osa.ticket-review.index', 'pattern' => 'admin/ticket-review*', 'permission' => 'tickets.view'],
            ['title' => 'Event Calendar', 'icon' => 'fa-solid fa-calendar-days', 'route' => 'admin.calendar', 'pattern' => 'admin/calendar*', 'permission' => 'calendar.view'],
            ['title' => 'Notifications', 'icon' => 'fa-solid fa-bell', 'route' => 'admin.notifications', 'pattern' => 'admin/notifications*', 'permission' => 'notifications.view'],
            ['title' => 'Reports', 'icon' => 'fa-solid fa-chart-bar', 'route' => 'admin.reports', 'pattern' => 'admin/reports*', 'permission' => 'reports.view'],
            ['title' => 'Archives', 'icon' => 'fa-solid fa-box-archive', 'route' => 'admin.archive', 'pattern' => 'admin/archive*', 'permission' => 'archive.view'],
        ],
        'gso' => [
            ['title' => 'Dashboard', 'icon' => 'fa-solid fa-table-columns', 'route' => 'gso.dashboard', 'pattern' => 'gso/dashboard*', 'permission' => 'dashboard.view'],
            ['title' => 'Ticket Review', 'icon' => 'fa-solid fa-calendar-days', 'route' => 'gso.ticket-review', 'pattern' => 'gso/ticket-review*', 'permission' => 'tickets.view'],
            ['title' => 'Event Calendar', 'icon' => 'fa-solid fa-calendar', 'route' => 'gso.calendar', 'pattern' => 'gso/calendar*', 'permission' => 'calendar.view'],
            ['title' => 'Notification', 'icon' => 'fa-solid fa-bell', 'route' => 'gso.notifications', 'pattern' => 'gso/notifications*', 'permission' => 'notifications.view'],
            ['title' => 'Reports', 'icon' => 'fa-solid fa-chart-bar', 'route' => 'gso.reports', 'pattern' => 'gso/reports*', 'permission' => 'reports.view'],
        ],
        'student-org' => [
            ['title' => 'Dashboard', 'icon' => 'fa-solid fa-table-columns', 'route' => 'student-org.dashboard', 'pattern' => 'student-org/dashboard*', 'permission' => 'dashboard.view'],
            ['title' => 'My Tickets', 'icon' => 'fa-solid fa-ticket', 'route' => 'student-org.my-tickets', 'pattern' => 'student-org/my-tickets*', 'permission' => 'tickets.view'],
            ['title' => 'Submit Ticket', 'icon' => 'fa-solid fa-file-circle-plus', 'route' => 'student-org.submit-ticket', 'pattern' => 'student-org/submit-ticket*', 'permission' => 'tickets.create'],
            ['title' => 'Event Calendar', 'icon' => 'fa-solid fa-calendar-days', 'route' => 'student-org.calendar', 'pattern' => 'student-org/calendar*', 'permission' => 'calendar.view'],
            ['title' => 'Reschedule Request', 'icon' => 'fa-solid fa-arrow-rotate-left', 'route' => 'student-org.reschedule', 'pattern' => 'student-org/reschedule*', 'permission' => 'tickets.reschedule'],
            ['title' => 'Notifications', 'icon' => 'fa-solid fa-bell', 'route' => 'student-org.notifications', 'pattern' => 'student-org/notifications*', 'permission' => 'notifications.view'],
            ['title' => 'History', 'icon' => 'fa-solid fa-box-archive', 'route' => 'student-org.history', 'pattern' => 'student-org/history*', 'permission' => 'tickets.view'],
        ],
        'viewer' => [
            ['title' => 'Event Calendar', 'icon' => 'fa-solid fa-calendar-days', 'route' => 'portal.calendar', 'pattern' => 'portal/calendar*', 'permission' => 'calendar.view'],
            ['title' => 'Notifications', 'icon' => 'fa-solid fa-bell', 'route' => 'portal.notifications', 'pattern' => 'portal/notifications*', 'permission' => 'notifications.view'],
        ],
    ];

    /**
     * Sidebar items the user may open in their portal, with resolved links.
     *
     * @return list<array{title: string, icon: string, route: string, pattern: string, permission: string, link: string}>
     */
    public static function itemsFor(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        $items = array_filter(
            self::MENUS[$user->portal()] ?? [],
            static fn (array $item): bool => $user->can($item['permission']),
        );

        return array_values(array_map(
            static fn (array $item): array => [...$item, 'link' => route($item['route'])],
            $items,
        ));
    }

    /**
     * The route name the user lands on after signing in.
     */
    public static function landingRouteFor(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return 'superadmin.dashboard';
        }

        return self::itemsFor($user)[0]['route'] ?? self::NO_ACCESS_ROUTE;
    }
}
