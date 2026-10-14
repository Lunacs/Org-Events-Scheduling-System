<?php

namespace App\Support;

/**
 * The baseline RBAC permission catalog: labels, grouping, the portals each
 * permission has an effect in, and the built-in roles granted it by default.
 */
class PermissionCatalog
{
    /**
     * @var array<string, array{label: string, group: string, portals: list<string>, defaults: list<string>}>
     */
    public const PERMISSIONS = [
        'dashboard.view' => [
            'label' => 'View dashboard',
            'group' => 'General',
            'portals' => ['osa', 'gso', 'student-org'],
            'defaults' => ['osa', 'gso', 'student-org'],
        ],
        'notifications.view' => [
            'label' => 'View notifications',
            'group' => 'General',
            'portals' => ['viewer', 'osa', 'gso', 'student-org'],
            'defaults' => ['osa', 'gso', 'student-org'],
        ],
        'calendar.view' => [
            'label' => 'View event calendar',
            'group' => 'Calendar',
            'portals' => ['viewer', 'osa', 'gso', 'student-org'],
            'defaults' => ['osa', 'gso', 'student-org'],
        ],
        'tickets.view' => [
            'label' => 'View ticket information',
            'group' => 'Tickets',
            'portals' => ['osa', 'gso', 'student-org'],
            'defaults' => ['osa', 'gso', 'student-org'],
        ],
        'tickets.create' => [
            'label' => 'Submit and resubmit tickets',
            'group' => 'Tickets',
            'portals' => ['student-org'],
            'defaults' => ['student-org'],
        ],
        'tickets.reschedule' => [
            'label' => 'Request event reschedules',
            'group' => 'Tickets',
            'portals' => ['student-org'],
            'defaults' => ['student-org'],
        ],
        'tickets.comment' => [
            'label' => 'Comment on tickets',
            'group' => 'Tickets',
            'portals' => ['osa', 'gso', 'student-org'],
            'defaults' => ['osa', 'gso', 'student-org'],
        ],
        'tickets.approve' => [
            'label' => 'Approve tickets',
            'group' => 'Ticket workflow',
            'portals' => ['osa', 'gso'],
            'defaults' => ['osa', 'gso'],
        ],
        'tickets.reject' => [
            'label' => 'Reject or request ticket revisions',
            'group' => 'Ticket workflow',
            'portals' => ['osa', 'gso'],
            'defaults' => ['osa', 'gso'],
        ],
        'tickets.forward' => [
            'label' => 'Forward tickets to GSO',
            'group' => 'Ticket workflow',
            'portals' => ['osa'],
            'defaults' => ['osa'],
        ],
        'reports.view' => [
            'label' => 'View reports',
            'group' => 'Reports',
            'portals' => ['osa', 'gso'],
            'defaults' => ['osa', 'gso'],
        ],
        'reports.export' => [
            'label' => 'Export reports',
            'group' => 'Reports',
            'portals' => ['osa', 'gso', 'student-org'],
            'defaults' => ['osa', 'gso', 'student-org'],
        ],
        'archive.view' => [
            'label' => 'View archived events',
            'group' => 'Reports',
            'portals' => ['osa'],
            'defaults' => ['osa'],
        ],
        'users.manage' => [
            'label' => 'Manage users',
            'group' => 'Administration',
            'portals' => [],
            'defaults' => ['osa'],
        ],
        'roles.manage' => [
            'label' => 'Manage roles and permissions',
            'group' => 'Administration',
            'portals' => [],
            'defaults' => [],
        ],
        'system.settings' => [
            'label' => 'Manage system settings',
            'group' => 'Administration',
            'portals' => [],
            'defaults' => [],
        ],
    ];

    /**
     * Permission names granted to a built-in role by default.
     *
     * The superadmin role is granted every catalog permission.
     *
     * @return list<string>
     */
    public static function defaultsFor(string $roleName): array
    {
        if ($roleName === 'superadmin') {
            return array_keys(self::PERMISSIONS);
        }

        return array_keys(array_filter(
            self::PERMISSIONS,
            static fn (array $definition): bool => in_array($roleName, $definition['defaults'], true),
        ));
    }

    /**
     * Determine whether a permission has any effect inside a portal.
     *
     * Permissions outside the catalog are assumed to apply everywhere.
     */
    public static function appliesToPortal(string $permissionName, string $portal): bool
    {
        if ($portal === 'superadmin' || ! isset(self::PERMISSIONS[$permissionName])) {
            return true;
        }

        return in_array($portal, self::PERMISSIONS[$permissionName]['portals'], true);
    }
}
