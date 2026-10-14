<?php

use App\Livewire\Superadmin\Roles\Index;
use App\Models\Roles;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Livewire\Livewire;

// Feature: dynamic-rbac, Property 5: Protected Roles Cannot Be Renamed or Deleted
// **Validates: Requirements 2.4**
it('keeps each seeded built-in role immutable through the rename and delete actions', function (): void {
    $builtInRoleNames = [
        'superadmin',
        'osa',
        'gso',
        'student-org',
    ];

    (new RbacSeeder)->run();

    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $this->actingAs($actor);

    foreach ($builtInRoleNames as $roleName) {
        $role = Roles::query()
            ->where('role_name', $roleName)
            ->sole();
        $originalRoleName = $role->role_name;
        $originalIsProtected = $role->is_protected;

        expect($originalIsProtected)->toBeTrue();

        Livewire::test(Index::class)
            ->call('startRename', $role->role_id)
            ->set('editingName', "renamed-{$roleName}")
            ->call('saveRename')
            ->assertHasErrors('editingName')
            ->call('openDeleteModal', $role->role_id)
            ->call('confirmDelete')
            ->assertHasErrors('deletingRoleId');

        $unchangedRole = Roles::query()->findOrFail($role->role_id);

        expect($unchangedRole->role_name)->toBe($originalRoleName)
            ->and($unchangedRole->is_protected)->toBe($originalIsProtected);
    }
})->group('rbac', 'property');

// Feature: dynamic-rbac, Property 6: Non-Protected Role Deletion Reassigns All Users to Fallback
// **Validates: Requirements 2.5**
it('reassigns every user from a deleted non-protected role to the configured fallback role', function (): void {
    $fallbackRole = Roles::query()->create([
        'role_name' => 'rbac-property-6-fallback',
    ]);
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $role = Roles::query()->create([
        'role_name' => 'rbac-property-6-deletable',
    ]);
    $userCount = random_int(1, 10);
    $assignedUsers = User::factory()
        ->count($userCount)
        ->create(['role_id' => $role->role_id]);
    $administrator = User::factory()->create([
        'role_id' => $superadminRole->role_id,
    ]);

    config()->set('rbac.fallback_role', $fallbackRole->role_name);
    $this->actingAs($administrator);

    Livewire::test(Index::class)
        ->call('openDeleteModal', $role->role_id)
        ->call('confirmDelete')
        ->assertHasNoErrors('deletingRoleId');

    expect($userCount)->toBeGreaterThan(0)
        ->and(Roles::query()->find($role->role_id))->toBeNull();

    foreach ($assignedUsers as $assignedUser) {
        expect($assignedUser->fresh()->role_id)->toBe($fallbackRole->role_id);
    }
})->repeat(100)->group('rbac', 'property');

// Feature: dynamic-rbac, Property 7: Roles List Contains Name, Protected Status, and User Count for Every Role
// **Validates: Requirements 2.6**
it('lists every role with a name, boolean protection status, and accurate user count', function (): void {
    $expectedRoleData = collect(range(0, 14))->map(function (int $userCount): array {
        $role = Roles::query()->create([
            'role_name' => "0-rbac-property-7-role-{$userCount}",
            'is_protected' => $userCount % 2 === 0,
        ]);

        if ($userCount > 0) {
            User::factory()
                ->count($userCount)
                ->create(['role_id' => $role->role_id]);
        }

        return [
            'role_id' => $role->role_id,
            'users_count' => $userCount,
        ];
    });

    $listedRoles = collect(
        Livewire::test(Index::class)
            ->instance()
            ->roles
            ->items(),
    )->keyBy('role_id');

    expect($listedRoles)->toHaveCount($expectedRoleData->count());

    foreach ($expectedRoleData as $expectedRole) {
        $listedRole = $listedRoles->get($expectedRole['role_id']);

        expect($listedRole)->not->toBeNull()
            ->and($listedRole->role_name)->not->toBeNull()
            ->and($listedRole->is_protected)->toBeBool()
            ->and($listedRole->users_count)->toBeInt()
            ->and($listedRole->users_count)->toBe($expectedRole['users_count']);
    }
})->repeat(10)->group('rbac', 'property');

it('keeps built-in roles immutable even when they were never flagged as protected', function (string $roleName): void {
    (new RbacSeeder)->run();

    $role = Roles::query()->where('role_name', $roleName)->sole();
    $role->forceFill(['is_protected' => false])->save();

    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $this->actingAs($actor);

    Livewire::test(Index::class)
        ->call('startRename', $role->role_id)
        ->set('editingName', "renamed-{$roleName}")
        ->call('saveRename')
        ->assertHasErrors('editingName')
        ->call('openDeleteModal', $role->role_id)
        ->call('confirmDelete')
        ->assertHasErrors('deletingRoleId');

    expect(Roles::query()->find($role->role_id))
        ->not->toBeNull()
        ->role_name->toBe($roleName);
})->with(['superadmin', 'osa', 'gso', 'student-org']);

it('flags existing built-in roles as protected when the data migration runs', function (): void {
    (new RbacSeeder)->run();

    $customRole = Roles::query()->create(['role_name' => 'migration-custom-role']);
    Roles::query()->update(['is_protected' => false]);

    $migration = require database_path('migrations/2026_09_29_223230_mark_built_in_roles_as_protected.php');
    $migration->up();

    expect(Roles::query()->whereIn('role_name', Roles::BUILT_IN_ROLE_NAMES)->pluck('is_protected')->all())
        ->toBe([true, true, true, true])
        ->and($customRole->fresh()->is_protected)->toBeFalse();
});
