<?php

use App\Models\Permission;
use App\Models\Roles;
use App\Support\PermissionCatalog;
use Database\Seeders\RbacSeeder;
use Illuminate\Support\Facades\DB;

// Feature: dynamic-rbac, Property 22: RbacSeeder Is Idempotent for Both Roles and Permissions
// Validates: Requirements 8.1, 8.2, 8.5
it('keeps built-in RBAC data idempotent across repeated seeder runs', function (int $timesToRun): void {
    $builtInRoles = [
        'superadmin',
        'osa',
        'gso',
        'student-org',
    ];

    $baselinePermissions = [
        'tickets.view',
        'tickets.create',
        'tickets.approve',
        'tickets.reject',
        'tickets.forward',
        'users.manage',
        'roles.manage',
        'reports.view',
        'calendar.view',
        'system.settings',
    ];

    $catalogPermissionCount = count(PermissionCatalog::PERMISSIONS);
    $defaultGrantCount = collect($builtInRoles)
        ->sum(fn (string $roleName): int => count(PermissionCatalog::defaultsFor($roleName)));

    $seeder = new RbacSeeder;

    for ($run = 0; $run < $timesToRun; $run++) {
        $seeder->run();

        expect(Roles::query()->where('is_protected', true)->count())->toBe(4)
            ->and(Roles::query()->whereIn('role_name', $builtInRoles)->where('is_protected', true)->count())->toBe(4)
            ->and(Permission::query()->count())->toBe($catalogPermissionCount)
            ->and(Permission::query()->whereIn('name', $baselinePermissions)->count())->toBe(10)
            ->and(DB::table('role_permission')->count())->toBe($defaultGrantCount)
            ->and($defaultGrantCount)->toBe(45);
    }
})->with([2, 3, 4, 5])->group('rbac', 'property');
