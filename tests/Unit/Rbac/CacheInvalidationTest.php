<?php

use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    Cache::forget('rbac_role_permissions_123');
    Cache::forget('rbac_role_permissions_456');
});

afterEach(function (): void {
    Cache::forget('rbac_role_permissions_123');
    Cache::forget('rbac_role_permissions_456');
});

test('it forgets only the specified role permission cache entry', function (): void {
    Cache::put('rbac_role_permissions_123', ['tickets.view'], now()->addMinute());
    Cache::put('rbac_role_permissions_456', ['users.manage'], now()->addMinute());

    expect(method_exists(TransactionLogService::class, 'invalidateRolePermissionCache'))->toBeTrue();

    TransactionLogService::invalidateRolePermissionCache(123);

    expect(Cache::has('rbac_role_permissions_123'))
        ->toBeFalse()
        ->and(Cache::has('rbac_role_permissions_456'))
        ->toBeTrue();
});

// Feature: dynamic-rbac, Property 15: Only the Affected Role's Cache Entry Is Invalidated on Permission Change
// **Validates: Requirements 5.5**
it('invalidates only the affected role cache entry across populated role sets', function (): void {
    $roleCount = random_int(2, 20);
    $firstRoleId = random_int(1_000_000, 9_000_000);
    $roleIds = range($firstRoleId, $firstRoleId + $roleCount - 1);
    $affectedRoleId = $roleIds[array_rand($roleIds)];

    foreach ($roleIds as $roleId) {
        Cache::put("rbac_role_permissions_{$roleId}", ["permissions.{$roleId}"], now()->addMinute());
    }

    try {
        TransactionLogService::invalidateRolePermissionCache($affectedRoleId);

        expect(Cache::has("rbac_role_permissions_{$affectedRoleId}"))->toBeFalse();

        foreach ($roleIds as $roleId) {
            if ($roleId === $affectedRoleId) {
                continue;
            }

            expect(Cache::has("rbac_role_permissions_{$roleId}"))->toBeTrue();
        }
    } finally {
        foreach ($roleIds as $roleId) {
            Cache::forget("rbac_role_permissions_{$roleId}");
        }
    }
})->repeat(100)->group('rbac', 'property');
