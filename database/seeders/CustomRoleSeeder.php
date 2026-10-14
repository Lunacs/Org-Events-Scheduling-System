<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Roles;
use App\Models\User;
use App\Services\TransactionLogService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * A sample custom role on the read-only Viewer portal, created the way a
 * superadmin would create it from the Roles and Permissions pages.
 */
class CustomRoleSeeder extends Seeder
{
    public const ROLE_NAME = 'security-guard';

    public const USER_EMAIL = 'guard@plv.edu.ph';

    /**
     * @var list<string>
     */
    public const PERMISSIONS = ['calendar.view', 'notifications.view'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::query()->where('email', 'superadmin@plv.edu.ph')->first();

        Carbon::setTestNow(now()->subDays(7)->setTime(10, 30));
        if ($superadmin) {
            Auth::setUser($superadmin);
        }

        try {
            $role = Roles::query()->firstOrCreate(
                ['role_name' => self::ROLE_NAME],
                ['is_protected' => false, 'base_portal' => Roles::VIEWER_PORTAL],
            );

            // Only a newly created role is mapped, so a re-run never undoes changes made in the UI.
            if ($role->wasRecentlyCreated) {
                TransactionLogService::logRbacOperation('RBAC_ROLE_CREATED', [
                    'record_id' => $role->role_id,
                    'before' => null,
                    'after' => ['role_name' => $role->role_name, 'base_portal' => $role->base_portal],
                    'timestamp' => now()->utc()->toISOString(),
                ]);

                $this->grantPermissions($role);
            }

            $user = User::withTrashed()->firstOrNew(['email' => self::USER_EMAIL]);
            $user->forceFill([
                'name' => 'Campus Security Guard',
                'password' => Hash::make('password'),
                'role_id' => $role->role_id,
                'org_id' => null,
                'office_id' => null,
                'email_verified_at' => now(),
            ])->save();
        } finally {
            Carbon::setTestNow();
            Auth::forgetUser();
        }

        $this->command?->info('Custom role: '.self::ROLE_NAME.' (Viewer portal) / '.self::USER_EMAIL.' / password');
    }

    /**
     * Map the role's permissions the way the superadmin permission editor does.
     */
    private function grantPermissions(Roles $role): void
    {
        $permissionIds = Permission::query()
            ->whereIn('name', self::PERMISSIONS)
            ->orderBy('permission_id')
            ->pluck('permission_id')
            ->map(static fn (mixed $permissionId): int => (int) $permissionId)
            ->all();

        $role->permissions()->sync($permissionIds);

        TransactionLogService::logRbacOperation('RBAC_PERMISSIONS_MAPPED', [
            'record_id' => $role->role_id,
            'before' => ['permission_ids' => []],
            'after' => ['permission_ids' => $permissionIds],
            'timestamp' => now()->utc()->toISOString(),
        ]);

        TransactionLogService::invalidateRolePermissionCache($role->role_id);
    }
}
