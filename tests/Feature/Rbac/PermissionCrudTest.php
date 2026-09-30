<?php

use App\Livewire\Superadmin\Permissions\Index;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// Feature: dynamic-rbac, Property 1: Permission Name Validation Accepts Valid Dot-Notation and Rejects Others
// **Validates: Requirements 1.1**
it('accepts every generated valid dot-notation name and rejects malformed names', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $this->actingAs($actor);

    $validNames = collect(range(1, 120))->map(
        fn (int $iteration): string => 'validation_'.$iteration.'.'.fake()->regexify('[a-z][a-z0-9_]{2,8}'),
    );

    foreach ($validNames as $name) {
        expect(preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/', $name))->toBe(1);

        $component = new Index;
        $component->createName = $name;
        $component->createPermission();

        expect(Permission::query()->where('name', $name)->exists())->toBeTrue();
    }

    $invalidNames = collect(range(1, 25))->flatMap(
        fn (int $iteration): array => [
            'Validation_'.$iteration.'.'.fake()->regexify('[a-z][a-z0-9_]{2,8}'),
            'validation_'.$iteration,
            $iteration.'validation.'.fake()->regexify('[a-z][a-z0-9_]{2,8}'),
            'validation_'.$iteration.'..'.fake()->regexify('[a-z][a-z0-9_]{2,8}'),
        ],
    );

    foreach ($invalidNames as $name) {
        expect(preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/', $name))->toBe(0);

        $component = new Index;
        $component->createName = $name;

        expect(function () use ($component): void {
            $component->createPermission();
        })->toThrow(ValidationException::class);
        expect(Permission::query()->where('name', $name)->doesntExist())->toBeTrue();
    }
})->group('rbac', 'property');

// Feature: dynamic-rbac, Property 2: Permission Create-Then-Retrieve Round Trip
// **Validates: Requirements 1.2, 1.3**
it('creates exactly one retrievable permission for each generated valid dot-notation name', function (): void {
    $names = collect(range(1, 100))
        ->map(fn (): string => fake()->unique()->regexify('[a-z][a-z0-9_]{2,8}\.[a-z][a-z0-9_]{2,8}'));

    foreach ($names as $name) {
        $permission = Permission::create(['name' => $name]);

        expect(Permission::query()->where('name', $name)->count())->toBeOne();

        $permission->delete();
    }
})->group('rbac', 'property');

// Feature: dynamic-rbac, Property 3: Permission Rename Leaves Exactly One Row
// **Validates: Requirements 1.4**
it('renames every generated distinct permission name pair to exactly one new row', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $actor = User::factory()->create(['role_id' => $superadminRole->role_id]);
    $this->actingAs($actor);

    $renamePairs = collect(range(1, 100))->map(
        fn (int $iteration): array => [
            'old' => 'permissions.old_'.$iteration.'_'.fake()->regexify('[a-z][a-z0-9_]{2,8}'),
            'new' => 'permissions.new_'.$iteration.'_'.fake()->regexify('[a-z][a-z0-9_]{2,8}'),
        ],
    );

    foreach ($renamePairs as $renamePair) {
        $permission = Permission::query()->create(['name' => $renamePair['old']]);

        $component = new Index;
        $component->startRename($permission->permission_id);
        $component->editingName = $renamePair['new'];
        $component->saveRename();

        expect(Permission::query()->where('name', $renamePair['new'])->count())->toBeOne()
            ->and(Permission::query()->where('name', $renamePair['old'])->count())->toBe(0);
    }
})->group('rbac', 'property');

// Feature: dynamic-rbac, Property 4: Permission Deletion Cascades to All Pivot Rows
// **Validates: Requirements 1.5, 7.4**
it('removes every role assignment when a permission is deleted', function (): void {
    $permissionId = DB::table('permissions')->insertGetId([
        'name' => 'permissions.'.Str::uuid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $assignmentCount = random_int(0, 10);

    for ($assignmentIndex = 0; $assignmentIndex < $assignmentCount; $assignmentIndex++) {
        $roleId = DB::table('roles')->insertGetId([
            'role_name' => 'role-'.Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('role_permission')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
        ]);
    }

    expect(DB::table('permissions')->where('permission_id', $permissionId)->count())->toBeOne();
    expect(DB::table('role_permission')->where('permission_id', $permissionId)->count())
        ->toBe($assignmentCount);

    DB::table('permissions')->where('permission_id', $permissionId)->delete();

    expect(DB::table('permissions')->where('permission_id', $permissionId)->count())->toBe(0);
    expect(DB::table('role_permission')->where('permission_id', $permissionId)->count())->toBe(0);
})->repeat(100)->group('rbac', 'property');
