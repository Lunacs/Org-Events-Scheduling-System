<?php

use App\Livewire\Superadmin\Roles\Permissions;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\Transaction_Logs;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Livewire;

class TestableRolePermissions extends Permissions
{
    public function render(): string
    {
        return '<div></div>';
    }
}

// Task 9.1: role permission initialization
// **Validates: Requirements 3.1, 9.1**
it('declares the role permission state and page metadata', function (): void {
    $component = new ReflectionClass(Permissions::class);

    foreach (['roleId', 'selectedPermissionIds', 'isSuperadminRole'] as $property) {
        expect($component->getProperty($property)->isPublic())->toBeTrue();
    }

    expect($component->getAttributes(Layout::class)[0]->getArguments())
        ->toBe(['components.layouts.superadmin'])
        ->and($component->getAttributes(Title::class)[0]->getArguments())
        ->toBe(['Superadmin - Roles & Permissions'])
        ->and($component->getMethod('role')->getAttributes(Computed::class)[0]->getArguments())
        ->toBe([])
        ->and($component->getMethod('allPermissions')->getAttributes(Computed::class)[0]->getArguments())
        ->toBe(['persist' => true, 'seconds' => 60]);
});

// Task 9.1: role permission initialization
// **Validates: Requirements 3.1, 9.1**
it('initializes role permission state from the current mappings', function (): void {
    $role = Roles::query()->create(['role_name' => 'event-coordinators']);
    $firstAssignedPermission = Permission::query()->create(['name' => 'calendar.component']);
    $secondAssignedPermission = Permission::query()->create(['name' => 'tickets.component']);
    Permission::query()->create(['name' => 'users.component']);

    $role->permissions()->sync([
        $secondAssignedPermission->permission_id,
        $firstAssignedPermission->permission_id,
    ]);

    $component = new Permissions;
    $component->mount($role->role_id);

    $selectedPermissionIds = $component->selectedPermissionIds;
    $expectedPermissionIds = [
        $firstAssignedPermission->permission_id,
        $secondAssignedPermission->permission_id,
    ];
    sort($selectedPermissionIds);
    sort($expectedPermissionIds);

    expect($component->roleId)->toBe($role->role_id)
        ->and($component->isSuperadminRole)->toBeFalse()
        ->and($selectedPermissionIds)->toBe($expectedPermissionIds)
        ->and($component->role())->toBeInstanceOf(Roles::class)
        ->and($component->role()->is($role))->toBeTrue();
});

// Task 9.1: role permission initialization
// **Validates: Requirements 3.1, 9.1**
it('marks the superadmin role and returns all permissions in name order', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);

    foreach (['zebra.component', 'alpha.component', 'middle.component'] as $name) {
        Permission::query()->firstOrCreate(['name' => $name]);
    }

    $component = new Permissions;
    $component->mount($superadminRole->role_id);
    $permissions = $component->allPermissions();
    $permissionNames = $permissions->pluck('name')->all();
    $sortedPermissionNames = $permissionNames;
    sort($sortedPermissionNames, SORT_STRING);

    expect($permissions)->toBeInstanceOf(Collection::class)
        ->and($component->isSuperadminRole)->toBeTrue()
        ->and($permissionNames)->toBe($sortedPermissionNames)
        ->and($permissionNames)->toContain('alpha.component', 'middle.component', 'zebra.component');
});

// Task 9.2: local permission selection toggling
// **Validates: Requirements 3.1, 3.2, 3.3**
it('toggles a permission selection in memory without changing the role mapping', function (): void {
    $role = Roles::query()->create(['role_name' => 'event-coordinators']);
    $permission = Permission::query()->create(['name' => 'calendar.toggle']);
    $role->permissions()->sync([$permission->permission_id]);

    $component = new Permissions;
    $component->mount($role->role_id);

    expect(method_exists($component, 'togglePermission'))->toBeTrue();

    $component->togglePermission($permission->permission_id);

    expect($component->selectedPermissionIds)->toBe([])
        ->and($role->permissions()->pluck('permissions.permission_id')->all())
        ->toBe([$permission->permission_id]);

    $component->togglePermission($permission->permission_id);

    expect($component->selectedPermissionIds)->toBe([$permission->permission_id])
        ->and($role->permissions()->pluck('permissions.permission_id')->all())
        ->toBe([$permission->permission_id]);
});

// Task 9.3: saving role permissions
// **Validates: Requirements 3.4, 3.5, 5.5, 7.5**
it('syncs role permissions, clears only the affected cache entry, records the mapping, and notifies', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $role = Roles::query()->create(['role_name' => 'event-coordinators']);
    $unaffectedRole = Roles::query()->create(['role_name' => 'program-board']);
    $removedPermission = Permission::query()->create(['name' => 'calendar.remove']);
    $retainedPermission = Permission::query()->create(['name' => 'tickets.retain']);
    $addedPermission = Permission::query()->create(['name' => 'reports.add']);

    $role->permissions()->sync([
        $removedPermission->permission_id,
        $retainedPermission->permission_id,
    ]);

    $roleCacheKey = "rbac_role_permissions_{$role->role_id}";
    $unaffectedCacheKey = "rbac_role_permissions_{$unaffectedRole->role_id}";
    Cache::put($roleCacheKey, ['calendar.remove', 'tickets.retain'], now()->addMinute());
    Cache::put($unaffectedCacheKey, ['users.manage'], now()->addMinute());

    $this->actingAs($actor);

    Livewire::test(TestableRolePermissions::class, ['roleId' => $role->role_id])
        ->set('selectedPermissionIds', [
            $retainedPermission->permission_id,
            $addedPermission->permission_id,
        ])
        ->call('savePermissions')
        ->assertDispatched(
            'toast-show',
            type: 'success',
            message: 'Role permissions updated successfully.',
            position: 'toast-top',
        );

    $transactionLog = Transaction_Logs::query()
        ->where('user_id', $actor->user_id)
        ->where('action', 'RBAC_PERMISSIONS_MAPPED')
        ->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

    expect($role->fresh()->permissions()
        ->orderBy('permissions.permission_id')
        ->pluck('permissions.permission_id')
        ->all())
        ->toBe([
            $retainedPermission->permission_id,
            $addedPermission->permission_id,
        ])
        ->and(Cache::has($roleCacheKey))->toBeFalse()
        ->and(Cache::get($unaffectedCacheKey))->toBe(['users.manage'])
        ->and($payload['record_id'])->toBe($role->role_id)
        ->and($payload['before'])->toBe([
            'permission_ids' => [
                $removedPermission->permission_id,
                $retainedPermission->permission_id,
            ],
        ])
        ->and($payload['after'])->toBe([
            'permission_ids' => [
                $retainedPermission->permission_id,
                $addedPermission->permission_id,
            ],
        ])
        ->and($payload['timestamp'])->toEndWith('Z');
});

// Task 9.3: superadmin safeguards
// **Validates: Requirements 3.7, 3.8**
it('rejects superadmin permission changes without altering assignments, cache, or audit history', function (array $selectedPermissionIds, string $message): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $assignedPermission = Permission::query()->create(['name' => 'system.assigned']);
    $proposedPermission = Permission::query()->create(['name' => 'system.proposed']);
    $superadminRole->permissions()->sync([$assignedPermission->permission_id]);
    $cacheKey = "rbac_role_permissions_{$superadminRole->role_id}";
    Cache::put($cacheKey, ['system.assigned'], now()->addMinute());
    $this->actingAs($actor);

    Livewire::test(TestableRolePermissions::class, ['roleId' => $superadminRole->role_id])
        ->set('selectedPermissionIds', $selectedPermissionIds === ['proposed']
            ? [$proposedPermission->permission_id]
            : $selectedPermissionIds)
        ->call('savePermissions')
        ->assertDispatched(
            'toast-show',
            type: 'error',
            message: $message,
            position: 'toast-top',
        );

    expect($superadminRole->fresh()->permissions()->pluck('permissions.permission_id')->all())
        ->toBe([$assignedPermission->permission_id])
        ->and(Cache::get($cacheKey))->toBe(['system.assigned'])
        ->and(Transaction_Logs::query()->where('action', 'RBAC_PERMISSIONS_MAPPED')->count())->toBe(0);
})->with([
    'when the selection is emptied' => [[], 'The superadmin role must retain at least one permission.'],
    'when the selection remains non-empty' => [['proposed'], 'The superadmin role permissions cannot be modified.'],
]);

// Task 9.3: persistence failure handling
// **Validates: Requirements 3.6**
it('preserves the current mapping when sync fails and notifies the user', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $role = Roles::query()->create(['role_name' => 'event-coordinators']);
    $assignedPermission = Permission::query()->create(['name' => 'calendar.assigned']);
    $missingPermission = Permission::query()->create(['name' => 'calendar.missing']);
    $missingPermissionId = $missingPermission->permission_id;
    $missingPermission->delete();
    $role->permissions()->sync([$assignedPermission->permission_id]);
    $cacheKey = "rbac_role_permissions_{$role->role_id}";
    Cache::put($cacheKey, ['calendar.assigned'], now()->addMinute());
    $this->actingAs($actor);

    Livewire::test(TestableRolePermissions::class, ['roleId' => $role->role_id])
        ->set('selectedPermissionIds', [$missingPermissionId])
        ->call('savePermissions')
        ->assertDispatched(
            'toast-show',
            type: 'error',
            message: 'Failed to update role permissions. Please try again.',
            position: 'toast-top',
        );

    expect($role->fresh()->permissions()->pluck('permissions.permission_id')->all())
        ->toBe([$assignedPermission->permission_id])
        ->and(Cache::get($cacheKey))->toBe(['calendar.assigned'])
        ->and(Transaction_Logs::query()->where('action', 'RBAC_PERMISSIONS_MAPPED')->count())->toBe(0);
});

// Task 9.4: role permission view
// **Validates: Requirements 3.1, 3.5, 3.6, 3.8, 9.2**
it('renders a capped permission toggle list with a save action for a role', function (): void {
    $role = Roles::query()->create(['role_name' => 'Event Coordinators']);

    $permissions = collect(range(1, 201))->map(static fn (int $number): Permission => Permission::query()->create([
        'name' => 'a0.view'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
    ]));
    $selectedPermission = $permissions->first();
    $lastVisiblePermission = $permissions->get(199);
    $hiddenPermission = $permissions->get(200);

    $role->permissions()->sync([$selectedPermission->permission_id]);

    Livewire::test(Permissions::class, ['roleId' => $role->role_id])
        ->assertSee('Permissions for Event Coordinators')
        ->assertSee($selectedPermission->name)
        ->assertSee($lastVisiblePermission->name)
        ->assertDontSee($hiddenPermission->name)
        ->assertSeeHtml('wire:click="togglePermission('.$selectedPermission->permission_id.')"')
        ->assertSeeHtml('wire:submit="savePermissions"')
        ->assertSeeHtml('wire:target="savePermissions"');
});

// Task 9.4: superadmin read-only permission view
// **Validates: Requirements 3.8, 9.2**
it('renders selected and unselected superadmin permission toggles as disabled', function (): void {
    $superadminRole = Roles::query()->firstOrCreate(['role_name' => 'superadmin']);
    $assignedPermission = Permission::query()->create(['name' => 'access.readonly']);
    $unassignedPermission = Permission::query()->create(['name' => 'access.unassigned']);

    $superadminRole->permissions()->sync([$assignedPermission->permission_id]);

    $component = Livewire::test(Permissions::class, ['roleId' => $superadminRole->role_id])
        ->assertSee('Permissions for superadmin')
        ->assertSee($assignedPermission->name)
        ->assertSee($unassignedPermission->name);

    $renderedHtml = $component->html();

    foreach ([$assignedPermission, $unassignedPermission] as $permission) {
        $disabledTogglePattern = sprintf(
            '/<input\b(?=[^>]*\bid="permission-toggle-%d")(?=[^>]*\sdisabled(?=\s|>))(?=[^>]*\baria-disabled="true")[^>]*>/',
            $permission->permission_id,
        );

        expect(preg_match($disabledTogglePattern, $renderedHtml))->toBe(1);
    }

    $selectedTogglePattern = sprintf(
        '/<input\b(?=[^>]*\bid="permission-toggle-%d")(?=[^>]*\bchecked\b)[^>]*>/',
        $assignedPermission->permission_id,
    );

    expect(preg_match($selectedTogglePattern, $renderedHtml))->toBe(1);
});

it('forbids saving role permissions after the mounted superadmin actor loses that role', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $osaRole = Roles::query()->where('role_name', 'osa')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedRole = Roles::query()->create(['role_name' => 'stale-mapping-role']);
    $assignedPermission = Permission::query()->create(['name' => 'mapping.stale_assigned']);
    $proposedPermission = Permission::query()->create(['name' => 'mapping.stale_proposed']);

    $managedRole->permissions()->sync([$assignedPermission->permission_id]);

    $component = Livewire::actingAs($actor)->test(TestableRolePermissions::class, [
        'roleId' => $managedRole->role_id,
    ]);

    $actor->update(['role_id' => $osaRole->role_id]);
    Auth::setUser($actor->fresh());

    $component
        ->set('selectedPermissionIds', [$proposedPermission->permission_id])
        ->call('savePermissions')
        ->assertForbidden();

    expect($managedRole->fresh()->permissions()->pluck('permissions.permission_id')->all())
        ->toBe([$assignedPermission->permission_id]);
});

it('rejects forged public state that targets the protected superadmin role', function (bool $mountSuperadminRole): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $ordinaryRole = Roles::query()->create(['role_name' => 'forged-state-ordinary-role']);
    $assignedPermission = Permission::query()->create(['name' => 'mapping.forged_assigned']);
    $proposedPermission = Permission::query()->create(['name' => 'mapping.forged_proposed']);
    $cacheKey = "rbac_role_permissions_{$superadminRole->role_id}";

    $superadminRole->permissions()->sync([$assignedPermission->permission_id]);
    Cache::put($cacheKey, [$assignedPermission->name], now()->addMinute());

    $component = Livewire::actingAs($actor)->test(TestableRolePermissions::class, [
        'roleId' => $mountSuperadminRole ? $superadminRole->role_id : $ordinaryRole->role_id,
    ]);

    $component
        ->set('roleId', $superadminRole->role_id)
        ->set('isSuperadminRole', false)
        ->set('selectedPermissionIds', [$proposedPermission->permission_id])
        ->call('savePermissions')
        ->assertDispatched(
            'toast-show',
            type: 'error',
            message: 'The superadmin role permissions cannot be modified.',
            position: 'toast-top',
        );

    expect($superadminRole->fresh()->permissions()->pluck('permissions.permission_id')->all())
        ->toBe([$assignedPermission->permission_id])
        ->and(Cache::get($cacheKey))->toBe([$assignedPermission->name])
        ->and(Transaction_Logs::query()->where('action', 'RBAC_PERMISSIONS_MAPPED')->count())->toBe(0);
})->with([
    'after mounting the superadmin role' => true,
    'after replacing an ordinary mounted role ID' => false,
]);

it('keeps a role permission cache warm when an outer transaction rolls back', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedRole = Roles::query()->create(['role_name' => 'mapping-cache-rollback-role']);
    $assignedPermission = Permission::query()->create(['name' => 'mapping.rollback_assigned']);
    $proposedPermission = Permission::query()->create(['name' => 'mapping.rollback_proposed']);
    $cacheKey = "rbac_role_permissions_{$managedRole->role_id}";

    $managedRole->permissions()->sync([$assignedPermission->permission_id]);
    Cache::put($cacheKey, [$assignedPermission->name], now()->addMinute());

    DB::beginTransaction();

    try {
        Livewire::actingAs($actor)
            ->test(TestableRolePermissions::class, ['roleId' => $managedRole->role_id])
            ->set('selectedPermissionIds', [$proposedPermission->permission_id])
            ->call('savePermissions');
    } finally {
        DB::rollBack();
    }

    expect($managedRole->fresh()->permissions()->pluck('permissions.permission_id')->all())
        ->toBe([$assignedPermission->permission_id])
        ->and(Cache::get($cacheKey))->toBe([$assignedPermission->name]);
});
