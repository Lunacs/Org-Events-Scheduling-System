<?php

use App\Livewire\Superadmin\Permissions\Index as PermissionsIndex;
use App\Livewire\Superadmin\Roles\Index;
use App\Livewire\Superadmin\Roles\Permissions as RolePermissions;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\Transaction_Logs;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

beforeEach(function (): void {
    if (! Route::has('superadmin.roles.permissions')) {
        Route::get(
            '/superadmin/roles/{roleId}/permissions',
            static fn (): string => '',
        )->name('superadmin.roles.permissions');
    }
});

// Task 13.1: RBAC management route registration
// **Validates: Requirements 7.6, 9.5**
it('registers RBAC management components as superadmin routes', function (): void {
    $expectedRoutes = [
        'superadmin.roles' => [
            'uri' => 'superadmin/roles',
            'component' => Index::class,
        ],
        'superadmin.roles.permissions' => [
            'uri' => 'superadmin/roles/{roleId}/permissions',
            'component' => RolePermissions::class,
        ],
        'superadmin.permissions' => [
            'uri' => 'superadmin/permissions',
            'component' => PermissionsIndex::class,
        ],
    ];

    foreach ($expectedRoutes as $name => $expectedRoute) {
        $route = Route::getRoutes()->getByName($name);

        expect($route)->not->toBeNull();
        expect($route->methods())
            ->toContain('GET')
            ->and($route->uri())->toBe($expectedRoute['uri'])
            ->and($route->getAction('uses'))->toBe($expectedRoute['component'].'@__invoke')
            ->and($route->gatherMiddleware())->toContain('auth', 'verified', 'role:superadmin');
    }
});

// Task 8.1: database-backed roles index
// **Validates: Requirements 2.6, 9.1**
it('declares the roles index state as public properties', function (): void {
    $component = new ReflectionClass(Index::class);
    $publicProperties = collect($component->getProperties(ReflectionProperty::IS_PUBLIC))
        ->pluck('name');

    expect($publicProperties)->toContain(
        'search',
        'createName',
        'deletingRoleId',
        'deletingRoleName',
        'editingName',
        'editingRoleId',
    );
});

// Task 8.1: database-backed roles index
// **Validates: Requirements 2.6, 9.1**
it('returns a searchable, paginated role list with accurate user counts', function (): void {
    $matchingRole = Roles::query()->create(['role_name' => 'team-00-alpha']);

    User::factory()->count(2)->create(['role_id' => $matchingRole->role_id]);

    foreach (range(1, 15) as $index) {
        Roles::query()->create(['role_name' => "team-{$index}"]);
    }

    Roles::query()->create(['role_name' => 'outside-search']);

    $component = new Index;
    $component->search = 'team-';

    $roles = (new ReflectionMethod(Index::class, 'roles'))->invoke($component);

    expect($roles)
        ->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($roles->perPage())->toBe(15)
        ->and($roles->total())->toBe(16)
        ->and($roles->firstWhere('role_id', $matchingRole->role_id)?->users_count)->toBe(2)
        ->and($roles->pluck('role_name'))->not->toContain('outside-search');
});

// Task 8.2: create role validation
// Validates: Requirements 2.2, 9.3
it('validates duplicate role names as the create name is updated and prevents duplicate persistence', function (): void {
    Roles::query()->create(['role_name' => 'Event Coordinators']);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);

    Livewire::actingAs($actor)->test(Index::class)
        ->set('createName', 'event coordinators')
        ->assertHasErrors(['createName' => ['unique']])
        ->call('createRole')
        ->assertHasErrors(['createName' => ['unique']]);

    expect(Roles::query()->where('role_name', 'Event Coordinators')->count())->toBeOne();
});

// Task 8.2: create role validation
// Validates: Requirements 2.1, 9.3
it('validates a create role name longer than one hundred characters as it is updated', function (): void {
    Livewire::test(Index::class)
        ->set('createName', str_repeat('a', 101))
        ->assertHasErrors(['createName' => ['max']]);
});

// Task 8.2: create role action
// Validates: Requirements 2.1, 7.5
it('creates a role and records the RBAC role-created transaction log', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $user = User::factory()->create(['role_id' => $superadminRole->role_id]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('createName', 'Event Coordinators')
        ->call('createRole')
        ->assertHasNoErrors('createName');

    $role = Roles::query()
        ->where('role_name', 'Event Coordinators')
        ->sole();
    $transactionLog = Transaction_Logs::query()
        ->where('user_id', $user->user_id)
        ->where('action', 'RBAC_ROLE_CREATED')
        ->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

    expect($role->is_protected)->toBeFalse()
        ->and($payload['record_id'])->toBe($role->role_id)
        ->and($payload['before'])->toBeNull()
        ->and($payload['after'])->toBe(['role_name' => 'Event Coordinators', 'base_portal' => 'viewer'])
        ->and($payload['timestamp'])->toEndWith('Z');
});

// Task 8.3: rename action initialization
// **Validates: Requirements 2.4, 6.6**
it('loads an existing role into the rename form without writing it', function (): void {
    $role = Roles::query()->create(['role_name' => 'Event Coordinators']);

    Livewire::test(Index::class)
        ->call('startRename', $role->role_id)
        ->assertSet('editingRoleId', $role->role_id)
        ->assertSet('editingName', 'Event Coordinators');

    expect($role->fresh()->role_name)->toBe('Event Coordinators')
        ->and(Transaction_Logs::query()->count())->toBe(0);
});

// Task 8.3: rename validation
// **Validates: Requirements 2.2, 2.4**
it('accepts the current role name when validating a rename and rejects names over one hundred characters', function (): void {
    $role = Roles::query()->create(['role_name' => 'Event Coordinators']);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);

    $this->actingAs($actor);

    Livewire::test(Index::class)
        ->call('startRename', $role->role_id)
        ->set('editingName', 'Event Coordinators')
        ->call('saveRename')
        ->assertHasNoErrors('editingName');

    Livewire::test(Index::class)
        ->call('startRename', $role->role_id)
        ->set('editingName', str_repeat('a', 101))
        ->call('saveRename')
        ->assertHasErrors(['editingName' => ['max']]);

    expect($role->fresh()->role_name)->toBe('Event Coordinators');
});

// Task 8.3: rename uniqueness validation
// **Validates: Requirements 2.2, 2.4**
it('rejects renaming a role to another role name without modifying either role or recording an audit entry', function (): void {
    $role = Roles::query()->create(['role_name' => 'Event Coordinators']);
    $existingRole = Roles::query()->create(['role_name' => 'Program Board']);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);

    $this->actingAs($actor);

    Livewire::test(Index::class)
        ->call('startRename', $role->role_id)
        ->set('editingName', 'program board')
        ->call('saveRename')
        ->assertHasErrors(['editingName' => ['unique']]);

    expect($role->fresh()->role_name)->toBe('Event Coordinators')
        ->and($existingRole->fresh()->role_name)->toBe('Program Board')
        ->and(Transaction_Logs::query()->where('action', 'RBAC_ROLE_RENAMED')->count())->toBe(0);
});

// Task 8.3: role rename persistence and audit
// **Validates: Requirements 2.4, 6.6, 7.5**
it('renames an unprotected role, clears the role ID cache, and records before and after values', function (): void {
    $role = Roles::query()->create(['role_name' => 'Event Coordinators']);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $roleIdCache = new ReflectionProperty(User::class, 'roleIdCache');

    $roleIdCache->setValue(null, ['event-coordinators' => $role->role_id, 'other-role' => 999]);
    $this->actingAs($actor);

    Livewire::test(Index::class)
        ->call('startRename', $role->role_id)
        ->set('editingName', 'Program Board')
        ->call('saveRename')
        ->assertHasNoErrors('editingName');

    $transactionLog = Transaction_Logs::query()
        ->where('user_id', $actor->user_id)
        ->where('action', 'RBAC_ROLE_RENAMED')
        ->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

    expect($role->fresh()->role_name)->toBe('Program Board')
        ->and($roleIdCache->getValue())->toBe([])
        ->and($payload['record_id'])->toBe($role->role_id)
        ->and($payload['before'])->toBe(['role_name' => 'Event Coordinators'])
        ->and($payload['after'])->toBe(['role_name' => 'Program Board'])
        ->and($payload['timestamp'])->toEndWith('Z');
});

// Task 8.3: protected-role guard
// **Validates: Requirements 2.4**
it('refuses to rename a protected role before modifying it or recording an audit entry', function (): void {
    $role = Roles::query()->create([
        'role_name' => 'Protected Role',
        'is_protected' => true,
    ]);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $roleIdCache = new ReflectionProperty(User::class, 'roleIdCache');

    $roleIdCache->setValue(null, ['protected-role' => $role->role_id]);
    $this->actingAs($actor);

    Livewire::test(Index::class)
        ->call('startRename', $role->role_id)
        ->set('editingName', 'System Administrator')
        ->call('saveRename')
        ->assertHasErrors('editingName');

    expect($role->fresh()->role_name)->toBe('Protected Role')
        ->and($roleIdCache->getValue())->toMatchArray(['protected-role' => $role->role_id])
        ->and(Transaction_Logs::query()->count())->toBe(0);
});

// Task 8.4: delete modal initialization
// **Validates: Requirements 2.4, 9.4**
it('loads a role into the delete modal without modifying it', function (): void {
    $role = Roles::query()->create(['role_name' => 'Event Coordinators']);

    Livewire::test(Index::class)
        ->call('openDeleteModal', $role->role_id)
        ->assertSet('deletingRoleId', $role->role_id)
        ->assertSet('deletingRoleName', 'Event Coordinators');

    expect($role->fresh()->role_name)->toBe('Event Coordinators')
        ->and(Transaction_Logs::query()->where('action', 'RBAC_ROLE_DELETED')->count())->toBe(0);
});

// Task 8.4: protected role deletion guard
// **Validates: Requirements 2.4**
it('refuses to delete a protected role without reassigning users or invalidating its permission cache', function (): void {
    $fallbackRole = Roles::query()->create(['role_name' => 'Delete Flow Fallback Role']);
    $protectedRole = Roles::query()->create([
        'role_name' => 'Protected Supervisors',
        'is_protected' => true,
    ]);
    $assignedUser = User::factory()->create(['role_id' => $protectedRole->role_id]);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $cacheKey = "rbac_role_permissions_{$protectedRole->role_id}";

    config()->set('rbac.fallback_role', $fallbackRole->role_name);
    Cache::put($cacheKey, ['roles.manage']);

    Livewire::actingAs($actor)->test(Index::class)
        ->call('openDeleteModal', $protectedRole->role_id)
        ->call('confirmDelete')
        ->assertHasErrors('deletingRoleId');

    expect(Roles::query()->find($protectedRole->role_id))->not->toBeNull()
        ->and($assignedUser->fresh()->role_id)->toBe($protectedRole->role_id)
        ->and(Cache::has($cacheKey))->toBeTrue()
        ->and(Transaction_Logs::query()->where('action', 'RBAC_ROLE_DELETED')->count())->toBe(0);
});

// Task 8.4: missing fallback guard
// **Validates: Requirements 2.5, 2.7**
it('aborts role deletion when its configured fallback is unavailable without changing users or pivots', function (): void {
    $role = Roles::query()->create(['role_name' => 'Event Coordinators']);
    $permission = Permission::query()->create(['name' => 'roles.delete']);
    $role->permissions()->attach($permission->permission_id);
    $assignedUser = User::factory()->create(['role_id' => $role->role_id]);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $cacheKey = "rbac_role_permissions_{$role->role_id}";

    config()->set('rbac.fallback_role', 'missing-fallback-role');
    Cache::put($cacheKey, ['roles.delete']);

    Livewire::actingAs($actor)->test(Index::class)
        ->call('openDeleteModal', $role->role_id)
        ->call('confirmDelete')
        ->assertHasErrors('deletingRoleId');

    expect(Roles::query()->find($role->role_id))->not->toBeNull()
        ->and($assignedUser->fresh()->role_id)->toBe($role->role_id)
        ->and(DB::table('role_permission')->where('role_id', $role->role_id)->count())->toBeOne()
        ->and(Cache::has($cacheKey))->toBeTrue()
        ->and(Transaction_Logs::query()->where('action', 'RBAC_ROLE_DELETED')->count())->toBe(0);
});

// Task 8.4: transactional role deletion
// **Validates: Requirements 2.5, 7.3, 7.5**
it('reassigns users, cascades permission mappings, invalidates the deleted role cache, audits, and resets modal state', function (): void {
    $fallbackRole = Roles::query()->create(['role_name' => 'Delete Flow Fallback Role']);
    $role = Roles::query()->create(['role_name' => 'Event Coordinators']);
    $unrelatedRole = Roles::query()->create(['role_name' => 'Program Board']);
    $permission = Permission::query()->create(['name' => 'roles.delete']);
    $role->permissions()->attach($permission->permission_id);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $firstAssignedUser = User::factory()->create(['role_id' => $role->role_id]);
    $secondAssignedUser = User::factory()->create(['role_id' => $role->role_id]);
    $trashedAssignedUser = User::factory()->create(['role_id' => $role->role_id]);
    $trashedAssignedUser->delete();
    $trashedAssignedUserId = $trashedAssignedUser->user_id;
    $deletedRoleId = $role->role_id;
    $deletedRoleCacheKey = "rbac_role_permissions_{$deletedRoleId}";
    $unrelatedRoleCacheKey = "rbac_role_permissions_{$unrelatedRole->role_id}";

    config()->set('rbac.fallback_role', $fallbackRole->role_name);
    Cache::put($deletedRoleCacheKey, ['roles.delete']);
    Cache::put($unrelatedRoleCacheKey, ['roles.view']);
    $this->actingAs($actor);

    Livewire::test(Index::class)
        ->call('openDeleteModal', $deletedRoleId)
        ->call('confirmDelete')
        ->assertHasNoErrors('deletingRoleId')
        ->assertSet('deletingRoleId', null)
        ->assertSet('deletingRoleName', null);

    $transactionLog = Transaction_Logs::query()
        ->where('user_id', $actor->user_id)
        ->where('action', 'RBAC_ROLE_DELETED')
        ->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);
    $trashedAssignedUser = User::withTrashed()->findOrFail($trashedAssignedUserId);

    expect(Roles::query()->find($deletedRoleId))->toBeNull()
        ->and($firstAssignedUser->fresh()->role_id)->toBe($fallbackRole->role_id)
        ->and($secondAssignedUser->fresh()->role_id)->toBe($fallbackRole->role_id)
        ->and($trashedAssignedUser->role_id)->toBe($fallbackRole->role_id)
        ->and($trashedAssignedUser->trashed())->toBeTrue()
        ->and(DB::table('role_permission')->where('role_id', $deletedRoleId)->count())->toBe(0)
        ->and(Cache::has($deletedRoleCacheKey))->toBeFalse()
        ->and(Cache::get($unrelatedRoleCacheKey))->toBe(['roles.view'])
        ->and($payload['record_id'])->toBe($deletedRoleId)
        ->and($payload['before'])->toBe(['role_name' => 'Event Coordinators'])
        ->and($payload['after'])->toBeNull()
        ->and($payload['timestamp'])->toEndWith('Z');
});

it('revokes active user credentials when deleting a role and reassigning its users', function (): void {
    $fallbackRole = Roles::query()->create(['role_name' => 'role-delete-credential-fallback']);
    $deletedRole = Roles::query()->create(['role_name' => 'role-delete-credential-target']);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $firstAssignedUser = User::factory()->create([
        'role_id' => $deletedRole->role_id,
        'remember_token' => 'role-delete-first-original-token',
    ]);
    $secondAssignedUser = User::factory()->create([
        'role_id' => $deletedRole->role_id,
        'remember_token' => 'role-delete-second-original-token',
    ]);

    config()->set('rbac.fallback_role', $fallbackRole->role_name);
    config()->set('session.driver', 'database');
    DB::table(config('session.table'))->insert([
        [
            'id' => 'role-delete-first-session',
            'user_id' => $firstAssignedUser->user_id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pest',
            'payload' => 'test-payload',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'role-delete-second-session',
            'user_id' => $secondAssignedUser->user_id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pest',
            'payload' => 'test-payload',
            'last_activity' => now()->timestamp,
        ],
    ]);

    Livewire::actingAs($actor)
        ->test(Index::class)
        ->call('openDeleteModal', $deletedRole->role_id)
        ->call('confirmDelete')
        ->assertHasNoErrors('deletingRoleId');

    expect(Roles::query()->find($deletedRole->role_id))->toBeNull()
        ->and($firstAssignedUser->fresh()->role_id)->toBe($fallbackRole->role_id)
        ->and($secondAssignedUser->fresh()->role_id)->toBe($fallbackRole->role_id)
        ->and($firstAssignedUser->fresh()->remember_token)->not->toBe('role-delete-first-original-token')
        ->and($secondAssignedUser->fresh()->remember_token)->not->toBe('role-delete-second-original-token')
        ->and(DB::table(config('session.table'))
            ->whereIn('user_id', [$firstAssignedUser->user_id, $secondAssignedUser->user_id])
            ->count())->toBe(0);
});

// Task 8.4: self-referential fallback guard
// **Validates: Requirements 2.5, 2.7**
it('aborts role deletion when the configured fallback is the role being deleted', function (): void {
    $role = Roles::query()->create(['role_name' => 'Self Fallback Role']);
    $permission = Permission::query()->create(['name' => 'roles.delete']);
    $role->permissions()->attach($permission->permission_id);
    $assignedUser = User::factory()->create(['role_id' => $role->role_id]);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $cacheKey = "rbac_role_permissions_{$role->role_id}";

    config()->set('rbac.fallback_role', $role->role_name);
    Cache::put($cacheKey, ['roles.delete']);

    Livewire::actingAs($actor)->test(Index::class)
        ->call('openDeleteModal', $role->role_id)
        ->call('confirmDelete')
        ->assertHasErrors('deletingRoleId');

    $remainingUser = User::withTrashed()->find($assignedUser->user_id);

    expect(Roles::query()->find($role->role_id))->not->toBeNull()
        ->and($remainingUser)->not->toBeNull();
    expect($remainingUser->role_id)->toBe($role->role_id)
        ->and(DB::table('role_permission')->where('role_id', $role->role_id)->count())->toBeOne()
        ->and(Cache::has($cacheKey))->toBeTrue()
        ->and(Transaction_Logs::query()->where('action', 'RBAC_ROLE_DELETED')->count())->toBe(0);
});

// Task 8.5: roles-management view
// **Validates: Requirements 2.6, 9.2, 9.3, 9.4**
it('renders role controls with counts, protected safeguards, and permission editor links', function (): void {
    $protectedRole = Roles::query()->create([
        'role_name' => 'Protected Coordinators',
        'is_protected' => true,
    ]);
    $customRole = Roles::query()->create(['role_name' => 'Event Coordinators']);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);

    User::factory()->count(2)->create(['role_id' => $customRole->role_id]);

    $component = Livewire::actingAs($actor)->test(Index::class)
        ->assertSee('Role management')
        ->assertSee('New role name')
        ->assertSeeHtml('name="role_name"')
        ->assertSee('Protected Coordinators')
        ->assertSee('Event Coordinators')
        ->assertSeeHtml('<span class="font-medium tabular-nums">2</span>')
        ->assertSee('Protected')
        ->assertSee('Custom')
        ->assertSee('Edit permissions')
        ->assertSee(route('superadmin.roles.permissions', $customRole->role_id))
        ->assertSeeHtml('aria-disabled="true"')
        ->assertSeeHtml('data-tip="Protected roles cannot be renamed or deleted"');

    Livewire::test(Index::class)
        ->set('createName', str_repeat('a', 101))
        ->assertHasErrors(['createName' => ['max']])
        ->assertSeeHtml('id="create-role-name-error"');

    $component
        ->call('startRename', $customRole->role_id)
        ->assertSeeHtml('wire:model.live.blur="editingName"')
        ->assertSeeHtml('name="role_name"')
        ->assertSee('Save')
        ->set('editingName', str_repeat('a', 101))
        ->call('saveRename')
        ->assertHasErrors(['editingName' => ['max']])
        ->assertSeeHtml('id="rename-role-error-'.$customRole->role_id.'"');
});

// Task 8.5: roles-management view
// **Validates: Requirements 9.4**
it('renders a destructive confirmation modal for the selected role', function (): void {
    $role = Roles::query()->create(['role_name' => 'Event Coordinators']);

    Livewire::test(Index::class)
        ->call('openDeleteModal', $role->role_id)
        ->assertSee('Delete role')
        ->assertSee('Event Coordinators')
        ->assertSee('Users assigned to this role will be moved to the configured fallback role.')
        ->assertSeeHtml('wire:click="confirmDelete"')
        ->assertSeeHtml('<dialog')
        ->assertSeeHtml('$el.showModal()')
        ->assertSeeHtml('$refs.cancelButton.focus()')
        ->assertSeeHtml('previousFocus?.focus()')
        ->assertSeeHtml('@click="dismiss()"')
        ->assertSeeHtml('x-on:cancel.prevent="dismiss()"')
        ->assertSeeHtml('$wire.deletingRoleId = null')
        ->assertSeeHtml('$wire.deletingRoleName = null');
});

it('forbids stale superadmin role mutations after the actor loses the superadmin role', function (string $mutation): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $osaRole = Roles::query()->where('role_name', 'osa')->sole();
    $fallbackRole = Roles::query()->create(['role_name' => 'stale-action-fallback']);
    $managedRole = Roles::query()->create(['role_name' => 'stale-action-managed']);
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);

    config()->set('rbac.fallback_role', $fallbackRole->role_name);

    $component = Livewire::actingAs($actor)->test(Index::class);

    if ($mutation === 'rename') {
        $component
            ->call('startRename', $managedRole->role_id)
            ->set('editingName', 'stale-action-renamed');
    }

    if ($mutation === 'delete') {
        $component->call('openDeleteModal', $managedRole->role_id);
    }

    $actor->update(['role_id' => $osaRole->role_id]);
    Auth::setUser($actor->fresh());

    if ($mutation === 'create') {
        $component
            ->set('createName', 'stale-action-created')
            ->call('createRole')
            ->assertForbidden();

        expect(Roles::query()->where('role_name', 'stale-action-created')->exists())->toBeFalse();

        return;
    }

    if ($mutation === 'rename') {
        $component
            ->call('saveRename')
            ->assertForbidden();

        expect($managedRole->fresh()->role_name)->toBe('stale-action-managed');

        return;
    }

    $component
        ->call('confirmDelete')
        ->assertForbidden();

    expect(Roles::query()->find($managedRole->role_id))->not->toBeNull();
})->with([
    'role creation' => 'create',
    'role rename' => 'rename',
    'role deletion' => 'delete',
]);

it('evicts durable old and new role-name cache entries after a role rename', function (): void {
    $oldRoleName = 'durable-cache-old-role';
    $newRoleName = 'durable-cache-new-role';
    $role = Roles::query()->create(['role_name' => $oldRoleName]);
    $unrelatedRole = Roles::query()->create(['role_name' => 'durable-cache-unrelated-role']);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);

    Cache::forever("role_id_{$oldRoleName}", $role->role_id);
    Cache::forever("role_id_{$newRoleName}", $unrelatedRole->role_id);
    User::clearRoleIdCache();

    Livewire::actingAs($actor)
        ->test(Index::class)
        ->call('startRename', $role->role_id)
        ->set('editingName', $newRoleName)
        ->call('saveRename')
        ->assertHasNoErrors('editingName');

    User::clearRoleIdCache();

    expect(User::getRoleId($oldRoleName))->toBeNull()
        ->and(User::getRoleId($newRoleName))->toBe($role->role_id);
});

it('evicts a deleted role name from the durable role cache', function (): void {
    $fallbackRole = Roles::query()->create(['role_name' => 'durable-delete-fallback']);
    $deletedRole = Roles::query()->create(['role_name' => 'durable-delete-target']);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);

    config()->set('rbac.fallback_role', $fallbackRole->role_name);
    Cache::forever("role_id_{$deletedRole->role_name}", $deletedRole->role_id);
    User::clearRoleIdCache();

    Livewire::actingAs($actor)
        ->test(Index::class)
        ->call('openDeleteModal', $deletedRole->role_id)
        ->call('confirmDelete')
        ->assertHasNoErrors('deletingRoleId');

    User::clearRoleIdCache();

    expect(User::getRoleId($deletedRole->role_name))->toBeNull();
});

it('keeps the deleted role permission cache warm when an outer transaction rolls back', function (): void {
    $fallbackRole = Roles::query()->create(['role_name' => 'role-cache-rollback-fallback']);
    $deletedRole = Roles::query()->create(['role_name' => 'role-cache-rollback-target']);
    $permission = Permission::query()->create(['name' => 'roles.cache_rollback']);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $managedUser = User::factory()->create([
        'role_id' => $deletedRole->role_id,
        'remember_token' => 'role-cache-rollback-original-token',
    ]);
    $sessionId = 'role-cache-rollback-session';
    $cacheKey = "rbac_role_permissions_{$deletedRole->role_id}";

    $deletedRole->permissions()->sync([$permission->permission_id]);
    Cache::put($cacheKey, [$permission->name], now()->addMinute());
    config()->set('rbac.fallback_role', $fallbackRole->role_name);
    config()->set('session.driver', 'database');
    DB::table(config('session.table'))->insert([
        'id' => $sessionId,
        'user_id' => $managedUser->user_id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'test-payload',
        'last_activity' => now()->timestamp,
    ]);

    DB::beginTransaction();

    try {
        Livewire::actingAs($actor)
            ->test(Index::class)
            ->call('openDeleteModal', $deletedRole->role_id)
            ->call('confirmDelete')
            ->assertHasNoErrors('deletingRoleId');
    } finally {
        DB::rollBack();
    }

    expect(Roles::query()->find($deletedRole->role_id))->not->toBeNull()
        ->and($managedUser->fresh()->role_id)->toBe($deletedRole->role_id)
        ->and($managedUser->fresh()->remember_token)->toBe('role-cache-rollback-original-token')
        ->and(DB::table(config('session.table'))->where('id', $sessionId)->exists())->toBeTrue()
        ->and(Cache::get($cacheKey))->toBe([$permission->name]);
});

it('refreshes the persisted role list when the page changes', function (): void {
    foreach (range(1, 16) as $index) {
        Roles::query()->create([
            'role_name' => sprintf('aa-paged-role-%02d', $index),
        ]);
    }

    Livewire::test(Index::class)
        ->assertSee('aa-paged-role-01')
        ->assertDontSee('aa-paged-role-16')
        ->call('setPage', 2)
        ->assertSee('aa-paged-role-16')
        ->assertDontSee('aa-paged-role-01');
});
