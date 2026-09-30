<?php

use App\Livewire\Superadmin\Permissions\Index;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\Transaction_Logs;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Livewire;

class TestablePermissionsIndex extends Index
{
    public function render(): View
    {
        return view('livewire.superadmin.permissions.index', [
            'permissions' => new LengthAwarePaginator([], 0, 15),
        ]);
    }
}

it('paginates permissions matching the search with assigned role counts', function (): void {
    $firstRole = Roles::query()->create(['role_name' => 'permissions-index-first-role']);
    $secondRole = Roles::query()->create(['role_name' => 'permissions-index-second-role']);
    $matchingPermission = Permission::query()->create(['name' => 'tickets.permissions_index']);
    $matchingPermission->roles()->sync([$firstRole->role_id, $secondRole->role_id]);
    Permission::query()->create(['name' => 'calendar.permissions_index']);

    $component = new Index;
    $component->search = 'tickets';

    $permissions = $component->permissions();

    $result = $permissions->getCollection()->sole('name', 'tickets.permissions_index');

    expect($permissions)
        ->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($permissions->perPage())->toBe(15)
        ->and($permissions->getCollection()->every(
            fn (Permission $permission): bool => str_starts_with($permission->name, 'tickets.'),
        ))->toBeTrue()
        ->and($result->roles_count)->toBe(2);
});

it('declares the permissions management state and page attributes', function (): void {
    $component = new ReflectionClass(Index::class);

    foreach (
        [
            'search',
            'createName',
            'editingPermissionId',
            'editingName',
            'deletingPermissionId',
            'deletingPermissionName',
            'deletingAssignedToSuperadmin',
        ] as $property
    ) {
        expect($component->getProperty($property)->isPublic())->toBeTrue();
    }

    expect($component->getAttributes(Layout::class)[0]->getArguments())->toBe(['components.layouts.superadmin'])
        ->and($component->getAttributes(Title::class)[0]->getArguments())->toBe(['Superadmin - Roles & Permissions'])
        ->and($component->getProperty('search')->getAttributes(Url::class)[0]->getArguments())->toBe(['except' => ''])
        ->and($component->getMethod('permissions')->getAttributes(Computed::class)[0]->getArguments())
        ->toBe(['persist' => true, 'seconds' => 300]);
});

it('creates a permission and records its RBAC audit payload', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $user = User::factory()->create(['role_id' => $superadminRole->role_id]);

    $this->actingAs($user);

    $component = new Index;
    $component->createName = 'reports.archive_export';
    $component->createPermission();

    $permission = Permission::query()->where('name', 'reports.archive_export')->sole();
    $transactionLog = Transaction_Logs::query()->where('user_id', $user->user_id)->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

    expect($permission->name)->toBe('reports.archive_export')
        ->and($transactionLog->user_id)->toBe($user->user_id)
        ->and($transactionLog->action)->toBe('RBAC_PERMISSION_CREATED')
        ->and($payload['record_id'])->toBe($permission->permission_id)
        ->and($payload['before'])->toBeNull()
        ->and($payload['after'])->toBe(['name' => 'reports.archive_export'])
        ->and($payload['timestamp'])->toBeString();
});

it('validates createName in real time', function (): void {
    Livewire::test(TestablePermissionsIndex::class)
        ->set('createName', 'Tickets.view')
        ->assertHasErrors(['createName' => 'regex']);
});

it('rejects invalid and duplicate create names', function (string $createName, string $rule): void {
    if ($rule === 'unique') {
        Permission::query()->create(['name' => $createName]);
    }

    $permissionCount = Permission::query()->count();
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $this->actingAs($actor);
    $component = new Index;
    $component->createName = $createName;

    expect(function () use ($component): void {
        $component->createPermission();
    })->toThrow(ValidationException::class);

    expect(Permission::query()->count())->toBe($permissionCount);
})->with([
    'a permission with no dot' => ['tickets', 'regex'],
    'a permission with uppercase characters' => ['Tickets.view', 'regex'],
    'an existing permission name' => ['reports.duplicate', 'unique'],
]);

it('starts a permission rename with the selected permission state', function (): void {
    $permission = Permission::query()->create(['name' => 'reports.archive_export']);

    $component = new Index;
    $component->startRename($permission->permission_id);

    expect($component->editingPermissionId)->toBe($permission->permission_id)
        ->and($component->editingName)->toBe('reports.archive_export');
});

it('renames a permission, invalidates every assigned role cache, and records its audit payload', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $user = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $firstRole = Roles::query()->create(['role_name' => 'permission-rename-first-role']);
    $secondRole = Roles::query()->create(['role_name' => 'permission-rename-second-role']);
    $unaffectedRole = Roles::query()->create(['role_name' => 'permission-rename-unaffected-role']);
    $permission = Permission::query()->create(['name' => 'reports.archive_export']);
    $permission->roles()->sync([$firstRole->role_id, $secondRole->role_id]);

    foreach ([$firstRole, $secondRole, $unaffectedRole] as $role) {
        Cache::put(
            "rbac_role_permissions_{$role->role_id}",
            ['reports.archive_export'],
            now()->addMinute(),
        );
    }

    $this->actingAs($user);

    $component = new Index;
    $component->startRename($permission->permission_id);
    $component->editingName = 'reports.archive';
    $component->saveRename();

    $transactionLog = Transaction_Logs::query()
        ->where('user_id', $user->user_id)
        ->where('action', 'RBAC_PERMISSION_RENAMED')
        ->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

    expect($permission->fresh()->name)->toBe('reports.archive')
        ->and(Permission::query()->where('name', 'reports.archive_export')->exists())->toBeFalse()
        ->and(Cache::has("rbac_role_permissions_{$firstRole->role_id}"))->toBeFalse()
        ->and(Cache::has("rbac_role_permissions_{$secondRole->role_id}"))->toBeFalse()
        ->and(Cache::has("rbac_role_permissions_{$unaffectedRole->role_id}"))->toBeTrue()
        ->and($payload['record_id'])->toBe($permission->permission_id)
        ->and($payload['before'])->toBe(['name' => 'reports.archive_export'])
        ->and($payload['after'])->toBe(['name' => 'reports.archive'])
        ->and($payload['timestamp'])->toBeString();
});

it('accepts a permission rename to its existing name', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $user = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $permission = Permission::query()->create(['name' => 'reports.archive_export']);

    $this->actingAs($user);

    $component = new Index;
    $component->startRename($permission->permission_id);
    $component->editingName = $permission->name;
    $component->saveRename();

    expect(Permission::query()->where('name', 'reports.archive_export')->count())->toBe(1);
});

it('rejects invalid or other-existing permission rename names', function (string $name, string $rule): void {
    $permission = Permission::query()->create(['name' => 'reports.archive_export']);

    if ($rule === 'unique') {
        Permission::query()->create(['name' => $name]);
    }

    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $this->actingAs($actor);
    $component = new Index;
    $component->startRename($permission->permission_id);
    $component->editingName = $name;

    expect(function () use ($component): void {
        $component->saveRename();
    })->toThrow(ValidationException::class);

    expect($permission->fresh()->name)->toBe('reports.archive_export');
})->with([
    'an uppercase permission segment' => ['Reports.archive', 'regex'],
    'a permission without a dot' => ['reports', 'regex'],
    'a name belonging to a different permission' => ['reports.archive', 'unique'],
]);

it('validates editingName in real time while renaming a permission', function (): void {
    $permission = Permission::query()->create(['name' => 'reports.archive_export']);

    Livewire::test(TestablePermissionsIndex::class)
        ->call('startRename', $permission->permission_id)
        ->set('editingName', 'Reports.archive')
        ->assertHasErrors(['editingName' => 'regex']);
});

it('captures whether a permission deletion affects the superadmin role', function (bool $isAssignedToSuperadmin): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $permission = Permission::query()->create(['name' => 'reports.archive_export']);

    if ($isAssignedToSuperadmin) {
        $permission->roles()->attach($superadminRole->role_id);
    }

    $component = new Index;
    $component->openDeleteModal($permission->permission_id);

    expect($component->deletingPermissionId)->toBe($permission->permission_id)
        ->and($component->deletingPermissionName)->toBe($permission->name)
        ->and($component->deletingAssignedToSuperadmin)->toBe($isAssignedToSuperadmin);
})->with([
    'when assigned to superadmin' => true,
    'when not assigned to superadmin' => false,
]);

it('deletes a permission with its assignments, invalidates affected caches, audits the deletion, and resets modal state', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $assignedRole = Roles::query()->create(['role_name' => 'permission-delete-assigned-role']);
    $unaffectedRole = Roles::query()->create(['role_name' => 'permission-delete-unaffected-role']);
    $permission = Permission::query()->create(['name' => 'reports.archive_export']);
    $permission->roles()->sync([$superadminRole->role_id, $assignedRole->role_id]);

    foreach ([$superadminRole, $assignedRole, $unaffectedRole] as $role) {
        Cache::put(
            "rbac_role_permissions_{$role->role_id}",
            ['reports.archive_export'],
            now()->addMinute(),
        );
    }

    $this->actingAs($actor);

    $component = new Index;
    $component->openDeleteModal($permission->permission_id);
    $component->confirmDelete();

    $transactionLog = Transaction_Logs::query()
        ->where('user_id', $actor->user_id)
        ->where('action', 'RBAC_PERMISSION_DELETED')
        ->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

    expect(Permission::query()->find($permission->permission_id))->toBeNull()
        ->and(DB::table('role_permission')->where('permission_id', $permission->permission_id)->exists())->toBeFalse()
        ->and(Cache::has("rbac_role_permissions_{$superadminRole->role_id}"))->toBeFalse()
        ->and(Cache::has("rbac_role_permissions_{$assignedRole->role_id}"))->toBeFalse()
        ->and(Cache::has("rbac_role_permissions_{$unaffectedRole->role_id}"))->toBeTrue()
        ->and($payload['record_id'])->toBe($permission->permission_id)
        ->and($payload['before'])->toBe(['name' => 'reports.archive_export'])
        ->and($payload['after'])->toBeNull()
        ->and($payload['timestamp'])->toBeString()
        ->and($component->deletingPermissionId)->toBeNull()
        ->and($component->deletingPermissionName)->toBeNull()
        ->and($component->deletingAssignedToSuperadmin)->toBeFalse();
});

// Task 7.5: permission-management view
// **Validates: Requirements 1.6, 1.7, 9.2, 9.3, 9.4**
it('renders permission controls, inline validation, and an impact-aware delete confirmation', function (): void {
    expect(view()->exists('livewire.superadmin.permissions.index'))->toBeTrue();

    $superadminRole = Roles::query()->firstOrCreate(
        ['role_name' => 'superadmin'],
        ['is_protected' => true],
    );
    $permission = Permission::query()->create(['name' => 'reports.archive_export']);
    $permission->roles()->attach($superadminRole->role_id);

    Livewire::test(Index::class)
        ->assertSee('Permission management')
        ->assertSee('Create permission')
        ->assertSee('Assigned roles')
        ->set('createName', 'Reports.export')
        ->assertHasErrors(['createName' => 'regex'])
        ->assertSee('The create name field format is invalid.')
        ->call('openDeleteModal', $permission->permission_id)
        ->assertSet('deletingAssignedToSuperadmin', true)
        ->assertSee('This permission is assigned to the superadmin role.')
        ->assertSeeHtml('<dialog')
        ->assertSeeHtml('x-ref="cancelButton"')
        ->assertSeeHtml('x-on:cancel.prevent="deletionId = null"');
});

// Task 7.5: paginated permission-management view
// **Validates: Requirements 1.7**
it('refreshes the persisted permission list when the page or search changes', function (): void {
    foreach (range(1, 16) as $index) {
        Permission::query()->create([
            'name' => sprintf('aaworkflow.item_%02d', $index),
        ]);
    }

    Livewire::test(Index::class)
        ->assertSee('aaworkflow.item_01')
        ->assertDontSee('aaworkflow.item_16')
        ->call('setPage', 2)
        ->assertSee('aaworkflow.item_16')
        ->assertDontSee('aaworkflow.item_01')
        ->set('search', 'aaworkflow.item_16')
        ->assertSee('aaworkflow.item_16')
        ->assertDontSee('aaworkflow.item_15');
});

// Task 7.8: Livewire permission-management actions
// **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 7.5**
it('creates a permission through Livewire and records its audit entry', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);

    $this->actingAs($actor);

    Livewire::test(Index::class)
        ->set('createName', 'reports.livewire_created')
        ->call('createPermission')
        ->assertSet('createName', '')
        ->assertSee('reports.livewire_created');

    $permission = Permission::query()->where('name', 'reports.livewire_created')->sole();
    $transactionLog = Transaction_Logs::query()
        ->where('user_id', $actor->user_id)
        ->where('action', 'RBAC_PERMISSION_CREATED')
        ->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

    expect($payload['record_id'])->toBe($permission->permission_id)
        ->and($payload['before'])->toBeNull()
        ->and($payload['after'])->toBe(['name' => 'reports.livewire_created'])
        ->and($payload['timestamp'])->toBeString();
});

it('rejects duplicate permission names through Livewire without auditing and renders the create error adjacent to its field', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $permission = Permission::query()->create(['name' => 'reports.livewire_duplicate']);

    $this->actingAs($actor);

    Livewire::test(Index::class)
        ->set('createName', $permission->name)
        ->call('createPermission')
        ->assertHasErrors(['createName' => ['unique']])
        ->assertSeeHtml('aria-describedby="create-permission-name-error"')
        ->assertSeeHtml('id="create-permission-name-error"');

    expect(Permission::query()->where('name', $permission->name)->count())->toBeOne()
        ->and(Transaction_Logs::query()
            ->where('user_id', $actor->user_id)
            ->where('action', 'RBAC_PERMISSION_CREATED')
            ->doesntExist())->toBeTrue();
});

it('renames a permission through Livewire and records its audit entry', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $permission = Permission::query()->create(['name' => 'reports.livewire_rename']);

    $this->actingAs($actor);

    Livewire::test(Index::class)
        ->call('startRename', $permission->permission_id)
        ->set('editingName', 'reports.livewire_renamed')
        ->call('saveRename')
        ->assertSet('editingPermissionId', null)
        ->assertSet('editingName', '')
        ->assertSee('reports.livewire_renamed');

    $transactionLog = Transaction_Logs::query()
        ->where('user_id', $actor->user_id)
        ->where('action', 'RBAC_PERMISSION_RENAMED')
        ->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

    expect($permission->fresh()->name)->toBe('reports.livewire_renamed')
        ->and($payload['record_id'])->toBe($permission->permission_id)
        ->and($payload['before'])->toBe(['name' => 'reports.livewire_rename'])
        ->and($payload['after'])->toBe(['name' => 'reports.livewire_renamed'])
        ->and($payload['timestamp'])->toBeString();
});

it('renders invalid rename feedback adjacent to the inline rename field', function (): void {
    $permission = Permission::query()->create(['name' => 'reports.livewire_rename']);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);

    Livewire::actingAs($actor)->test(Index::class)
        ->call('startRename', $permission->permission_id)
        ->set('editingName', 'Reports.livewire_rename')
        ->call('saveRename')
        ->assertHasErrors(['editingName' => ['regex']])
        ->assertSeeHtml('aria-describedby="rename-permission-name-error"')
        ->assertSeeHtml('id="rename-permission-name-error"');

    expect($permission->fresh()->name)->toBe('reports.livewire_rename');
});

it('deletes a permission through Livewire and records its audit entry regardless of superadmin assignment', function (bool $isAssignedToSuperadmin): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $assignedRole = Roles::query()->create(['role_name' => 'livewire-delete-assigned-role']);
    $permission = Permission::query()->create(['name' => 'reports.livewire_delete']);
    $permission->roles()->attach($isAssignedToSuperadmin ? $superadminRole->role_id : $assignedRole->role_id);

    $this->actingAs($actor);

    $component = Livewire::test(Index::class)
        ->call('openDeleteModal', $permission->permission_id)
        ->assertSet('deletingAssignedToSuperadmin', $isAssignedToSuperadmin);

    if ($isAssignedToSuperadmin) {
        $component->assertSee('This permission is assigned to the superadmin role.');
    } else {
        $component->assertDontSee('This permission is assigned to the superadmin role.');
    }

    $component
        ->call('confirmDelete')
        ->assertSet('deletingPermissionId', null)
        ->assertSet('deletingPermissionName', null)
        ->assertSet('deletingAssignedToSuperadmin', false);

    $transactionLog = Transaction_Logs::query()
        ->where('user_id', $actor->user_id)
        ->where('action', 'RBAC_PERMISSION_DELETED')
        ->sole();
    $payload = json_decode($transactionLog->details, true, flags: JSON_THROW_ON_ERROR);

    expect(Permission::query()->find($permission->permission_id))->toBeNull()
        ->and(DB::table('role_permission')->where('permission_id', $permission->permission_id)->doesntExist())->toBeTrue()
        ->and($payload['record_id'])->toBe($permission->permission_id)
        ->and($payload['before'])->toBe(['name' => 'reports.livewire_delete'])
        ->and($payload['after'])->toBeNull()
        ->and($payload['timestamp'])->toBeString();
})->with([
    'when assigned to superadmin' => true,
    'when not assigned to superadmin' => false,
]);

it('forbids stale superadmin permission mutations after the actor loses the superadmin role', function (string $mutation): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $osaRole = Roles::query()->where('role_name', 'osa')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $permission = Permission::query()->create(['name' => 'stale_action.current']);

    $component = Livewire::actingAs($actor)->test(TestablePermissionsIndex::class);

    if ($mutation === 'rename') {
        $component
            ->call('startRename', $permission->permission_id)
            ->set('editingName', 'stale_action.renamed');
    }

    if ($mutation === 'delete') {
        $component->call('openDeleteModal', $permission->permission_id);
    }

    $actor->update(['role_id' => $osaRole->role_id]);
    Auth::setUser($actor->fresh());

    if ($mutation === 'create') {
        $component
            ->set('createName', 'stale_action.created')
            ->call('createPermission')
            ->assertForbidden();

        expect(Permission::query()->where('name', 'stale_action.created')->exists())->toBeFalse();

        return;
    }

    if ($mutation === 'rename') {
        $component
            ->call('saveRename')
            ->assertForbidden();

        expect($permission->fresh()->name)->toBe('stale_action.current');

        return;
    }

    $component
        ->call('confirmDelete')
        ->assertForbidden();

    expect(Permission::query()->find($permission->permission_id))->not->toBeNull();
})->with([
    'permission creation' => 'create',
    'permission rename' => 'rename',
    'permission deletion' => 'delete',
]);

it('keeps affected permission caches warm when a permission rename is rolled back', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $role = Roles::query()->create(['role_name' => 'permission-rename-rollback-role']);
    $permission = Permission::query()->create(['name' => 'permission_rollback.current']);
    $cacheKey = "rbac_role_permissions_{$role->role_id}";

    $permission->roles()->sync([$role->role_id]);
    Cache::put($cacheKey, [$permission->name], now()->addMinute());

    DB::beginTransaction();

    try {
        Livewire::actingAs($actor)
            ->test(TestablePermissionsIndex::class)
            ->call('startRename', $permission->permission_id)
            ->set('editingName', 'permission_rollback.renamed')
            ->call('saveRename');
    } finally {
        DB::rollBack();
    }

    expect($permission->fresh()->name)->toBe('permission_rollback.current')
        ->and(Cache::get($cacheKey))->toBe(['permission_rollback.current']);
});

it('keeps affected permission caches warm when a permission deletion is rolled back', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $role = Roles::query()->create(['role_name' => 'permission-delete-rollback-role']);
    $permission = Permission::query()->create(['name' => 'permission_rollback.deleted']);
    $cacheKey = "rbac_role_permissions_{$role->role_id}";

    $permission->roles()->sync([$role->role_id]);
    Cache::put($cacheKey, [$permission->name], now()->addMinute());

    DB::beginTransaction();

    try {
        Livewire::actingAs($actor)
            ->test(TestablePermissionsIndex::class)
            ->call('openDeleteModal', $permission->permission_id)
            ->call('confirmDelete');
    } finally {
        DB::rollBack();
    }

    expect(Permission::query()->find($permission->permission_id))->not->toBeNull()
        ->and(Cache::get($cacheKey))->toBe(['permission_rollback.deleted']);
});
