<?php

use App\Models\Permission;
use App\Models\Roles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

uses(TestCase::class);

test('it configures protected roles and their permission pivot relation', function (): void {
    $role = new Roles;
    $role->is_protected = '1';

    $permissions = $role->permissions();

    expect($role->getFillable())
        ->toBe(['role_name', 'is_protected', 'base_portal'])
        ->and($role->is_protected)->toBeTrue()
        ->and($permissions)->toBeInstanceOf(BelongsToMany::class)
        ->and($permissions->getRelated())->toBeInstanceOf(Permission::class)
        ->and($permissions->getTable())->toBe('role_permission')
        ->and($permissions->getForeignPivotKeyName())->toBe('role_id')
        ->and($permissions->getRelatedPivotKeyName())->toBe('permission_id');
});
