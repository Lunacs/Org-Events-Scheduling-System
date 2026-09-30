<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Labels and groups for every baseline permission.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const LABELS = [
        'dashboard.view' => ['View dashboard', 'General'],
        'notifications.view' => ['View notifications', 'General'],
        'calendar.view' => ['View event calendar', 'Calendar'],
        'tickets.view' => ['View ticket information', 'Tickets'],
        'tickets.create' => ['Submit and resubmit tickets', 'Tickets'],
        'tickets.reschedule' => ['Request event reschedules', 'Tickets'],
        'tickets.comment' => ['Comment on tickets', 'Tickets'],
        'tickets.approve' => ['Approve tickets', 'Ticket workflow'],
        'tickets.reject' => ['Reject or request ticket revisions', 'Ticket workflow'],
        'tickets.forward' => ['Forward tickets to GSO', 'Ticket workflow'],
        'reports.view' => ['View reports', 'Reports'],
        'reports.export' => ['Export reports', 'Reports'],
        'archive.view' => ['View archived events', 'Reports'],
        'users.manage' => ['Manage users', 'Administration'],
        'roles.manage' => ['Manage roles and permissions', 'Administration'],
        'system.settings' => ['Manage system settings', 'Administration'],
    ];

    /**
     * Grants that reproduce each built-in portal's existing behaviour once
     * pages and actions are enforced by permission. Existing grants and
     * earlier manual revocations are left untouched.
     *
     * @var array<string, list<string>>
     */
    private const NEW_GRANTS = [
        'superadmin' => ['dashboard.view', 'notifications.view', 'tickets.reschedule', 'tickets.comment', 'reports.export', 'archive.view'],
        'osa' => ['dashboard.view', 'notifications.view', 'tickets.comment', 'reports.export', 'archive.view'],
        'gso' => ['dashboard.view', 'notifications.view', 'tickets.comment', 'reports.export', 'tickets.reject'],
        'student-org' => ['dashboard.view', 'notifications.view', 'tickets.reschedule', 'tickets.comment', 'reports.export'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        foreach (self::LABELS as $name => [$label, $group]) {
            $exists = DB::table('permissions')->where('name', $name)->exists();

            if ($exists) {
                DB::table('permissions')->where('name', $name)->update(['label' => $label, 'group' => $group]);
            } else {
                DB::table('permissions')->insert([
                    'name' => $name,
                    'label' => $label,
                    'group' => $group,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach (self::NEW_GRANTS as $roleName => $permissionNames) {
            $roleId = DB::table('roles')->where('role_name', $roleName)->value('role_id');

            if ($roleId === null) {
                continue;
            }

            $permissionIds = DB::table('permissions')->whereIn('name', $permissionNames)->pluck('permission_id');

            foreach ($permissionIds as $permissionId) {
                DB::table('role_permission')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }

            Cache::forget("rbac_role_permissions_{$roleId}");
        }

        Cache::forget('rbac_permission_names');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('name', ['dashboard.view', 'notifications.view', 'tickets.reschedule', 'tickets.comment', 'reports.export', 'archive.view'])
            ->delete();

        Cache::forget('rbac_permission_names');
    }
};
