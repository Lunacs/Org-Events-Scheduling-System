<?php

use App\Livewire\Superadmin\Roles\Permissions;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Livewire;

// Feature: dynamic-rbac, Property 8: Permission Toggle State Reflects Actual Role-Permission Assignments
// **Validates: Requirements 3.1**
it('loads exactly the assigned permission IDs into the role permission toggle state', function (): void {
    $role = Roles::query()->create([
        'role_name' => 'rbac-property-8-role-'.Str::uuid(),
    ]);
    $permissions = collect(range(1, 12))->map(
        fn (int $permissionIndex): Permission => Permission::query()->create([
            'name' => 'rbac.property_8.'.Str::uuid().'.permission_'.$permissionIndex,
        ]),
    );
    $assignedPermissionIds = $permissions
        ->filter(static fn (): bool => random_int(0, 1) === 1)
        ->pluck('permission_id')
        ->all();

    $role->permissions()->sync($assignedPermissionIds);

    $component = new Permissions;
    $component->mount($role->role_id);

    $pivotPermissionIds = DB::table('role_permission')
        ->where('role_id', $role->role_id)
        ->orderBy('permission_id')
        ->pluck('permission_id')
        ->map(static fn (mixed $permissionId): int => (int) $permissionId)
        ->all();
    $mountedPermissionIds = array_map(
        static fn (mixed $permissionId): int => (int) $permissionId,
        $component->selectedPermissionIds,
    );

    sort($mountedPermissionIds);

    expect($mountedPermissionIds)->toBe($pivotPermissionIds);
})->repeat(100)->group('rbac', 'property');

// Feature: dynamic-rbac, Property 9: Permission Toggle Enable/Disable Round Trip
// **Validates: Requirements 3.2, 3.3, 3.4**
it('updates a role users gate authorization when a permission is enabled and then disabled', function (): void {
    $superadminRole = Roles::query()->firstOrCreate([
        'role_name' => 'superadmin',
    ]);
    $administrator = User::factory()->create([
        'role_id' => $superadminRole->role_id,
    ]);
    $role = Roles::query()->create([
        'role_name' => 'rbac-property-nine-role-'.Str::uuid(),
    ]);
    $permission = Permission::query()->create([
        'name' => 'rbac.property_nine.toggle_'.Str::lower(Str::random(16)),
    ]);
    $roleUser = User::factory()->create([
        'role_id' => $role->role_id,
    ]);
    $cacheKey = "rbac_role_permissions_{$role->role_id}";

    (new AppServiceProvider($this->app))->boot();
    Cache::forget($cacheKey);
    $this->actingAs($administrator);

    $component = Livewire::withoutLazyLoading()
        ->test(Permissions::class, ['roleId' => $role->role_id])
        ->call('togglePermission', $permission->permission_id)
        ->assertSet('selectedPermissionIds', [$permission->permission_id])
        ->call('savePermissions');

    expect($role->fresh()->permissions()->pluck('permissions.permission_id')->all())
        ->toBe([$permission->permission_id])
        ->and(Gate::forUser($roleUser)->check($permission->name))->toBeTrue();

    $component
        ->call('togglePermission', $permission->permission_id)
        ->assertSet('selectedPermissionIds', [])
        ->call('savePermissions');

    expect($role->fresh()->permissions()->pluck('permissions.permission_id')->all())
        ->toBe([])
        ->and(Gate::forUser($roleUser)->check($permission->name))->toBeFalse();
})->repeat(100)->group('rbac', 'property');
