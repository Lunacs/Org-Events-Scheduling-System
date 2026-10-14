<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Roles;
use App\Services\TransactionLogService;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Roles::BUILT_IN_ROLE_NAMES as $roleName) {
            $role = Roles::firstOrCreate(
                ['role_name' => $roleName],
                ['is_protected' => true, 'base_portal' => $roleName],
            );

            if (! $role->is_protected || $role->base_portal !== $roleName) {
                $role->update(['is_protected' => true, 'base_portal' => $roleName]);
            }
        }

        foreach (PermissionCatalog::PERMISSIONS as $permissionName => $definition) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                ['label' => $definition['label'], 'group' => $definition['group']],
            );

            if ($permission->label === null || $permission->group === null) {
                $permission->update([
                    'label' => $permission->label ?? $definition['label'],
                    'group' => $permission->group ?? $definition['group'],
                ]);
            }
        }

        foreach (Roles::BUILT_IN_ROLE_NAMES as $roleName) {
            $role = Roles::query()->where('role_name', $roleName)->firstOrFail();
            $permissionIds = Permission::query()
                ->whereIn('name', PermissionCatalog::defaultsFor($roleName))
                ->pluck('permission_id');

            foreach ($permissionIds as $permissionId) {
                DB::table('role_permission')->updateOrInsert([
                    'role_id' => $role->role_id,
                    'permission_id' => $permissionId,
                ]);
            }

            TransactionLogService::invalidateRolePermissionCache($role->role_id);
        }
    }
}
