<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the permissions table with a unique name index', function (): void {
    expect(Schema::hasTable('permissions'))->toBeTrue()
        ->and(Schema::hasIndex('permissions', ['name'], 'unique'))->toBeTrue();
});

it('adds a protected boolean flag that defaults to false', function (): void {
    $isProtectedColumn = collect(Schema::getColumns('roles'))->firstWhere('name', 'is_protected');

    expect($isProtectedColumn)->not->toBeNull()
        ->and($isProtectedColumn['type'])->toContain('tinyint');

    DB::table('roles')->insert([
        'role_name' => 'unprotected-role',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect((bool) DB::table('roles')->where('role_name', 'unprotected-role')->value('is_protected'))->toBeFalse();
});

it('creates role permission foreign keys for both pivot columns', function (): void {
    $foreignKeys = collect(Schema::getForeignKeys('role_permission'));

    expect(Schema::hasTable('role_permission'))->toBeTrue()
        ->and(Schema::hasColumn('role_permission', 'role_id'))->toBeTrue()
        ->and(Schema::hasColumn('role_permission', 'permission_id'))->toBeTrue()
        ->and($foreignKeys->contains(
            fn (array $foreignKey): bool => $foreignKey['columns'] === ['role_id']
                && $foreignKey['foreign_table'] === 'roles'
                && $foreignKey['foreign_columns'] === ['role_id'],
        ))->toBeTrue()
        ->and($foreignKeys->contains(
            fn (array $foreignKey): bool => $foreignKey['columns'] === ['permission_id']
                && $foreignKey['foreign_table'] === 'permissions'
                && $foreignKey['foreign_columns'] === ['permission_id'],
        ))->toBeTrue();
});

it('enforces unique role names at the database level', function (): void {
    $attributes = [
        'role_name' => 'unique-role',
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('roles')->insert($attributes);

    expect(fn () => DB::table('roles')->insert($attributes))
        ->toThrow(QueryException::class);
});
