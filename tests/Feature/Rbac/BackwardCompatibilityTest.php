<?php

use App\Models\Roles;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $roleNames = ['superadmin', 'osa', 'gso', 'student-org'];

    foreach ($roleNames as $roleName) {
        Cache::forget("role_id_{$roleName}");
        Roles::query()->firstOrCreate(['role_name' => $roleName]);
    }

    User::clearRoleIdCache();
});

// Feature: dynamic-rbac, Property 17: CheckUserRole Middleware Grants Access for Valid Roles and Denies Unknown Roles
// **Validates: Requirements 6.1, 6.2**
it('allows users through the role middleware for every persisted built-in role slug', function (): void {
    foreach (['superadmin', 'osa', 'gso', 'student-org'] as $roleName) {
        $role = Roles::query()->where('role_name', $roleName)->sole();
        $user = User::factory()->create(['role_id' => $role->role_id]);
        $path = '/rbac-backward-compatible/'.Str::uuid();

        Route::get($path, static fn (): string => 'authorized')
            ->middleware("role:{$roleName}");

        $this->actingAs($user)
            ->get($path)
            ->assertOk()
            ->assertSee('authorized');
    }
})->repeat(25)->group('rbac', 'property');

// Feature: dynamic-rbac, Property 17: CheckUserRole Middleware Grants Access for Valid Roles and Denies Unknown Roles
// **Validates: Requirements 6.2**
it('denies arbitrary unknown role slugs through the role middleware', function (): void {
    $userRole = Roles::query()->where('role_name', 'osa')->sole();
    $user = User::factory()->create(['role_id' => $userRole->role_id]);
    $unknownRole = 'rbac-unknown-'.Str::uuid();
    $path = '/rbac-unknown-role/'.Str::uuid();

    Route::get($path, static fn (): string => 'authorized')
        ->middleware("role:{$unknownRole}");

    $this->actingAs($user)
        ->get($path)
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'email' => 'You are not authorized to access this area.',
        ]);
})->repeat(100)->group('rbac', 'property');

// Feature: dynamic-rbac, Property 18: hasRole and Convenience Helpers Return Correct Boolean for Any User
// **Validates: Requirements 6.3, 6.4, 6.5**
it('matches role IDs in hasRole and preserves the built-in helper and dashboard-route mappings', function (): void {
    $dashboardRoutes = [
        'superadmin' => 'superadmin.dashboard',
        'osa' => 'admin.dashboard',
        'gso' => 'gso.dashboard',
        'student-org' => 'student-org.dashboard',
    ];
    $helperMethods = [
        'superadmin' => 'isSuperAdmin',
        'osa' => 'isOSA',
        'gso' => 'isGSO',
        'student-org' => 'isStudentOrg',
    ];

    foreach ($dashboardRoutes as $roleName => $dashboardRoute) {
        $role = Roles::query()->where('role_name', $roleName)->sole();
        $user = User::factory()->create(['role_id' => $role->role_id]);

        expect($user->hasRole($roleName))->toBeTrue()
            ->and($user->getDashboardRoute())->toBe($dashboardRoute);

        foreach (array_keys($dashboardRoutes) as $otherRoleName) {
            expect($user->hasRole($otherRoleName))
                ->toBe($user->role_id === User::getRoleId($otherRoleName));
        }

        foreach ($helperMethods as $helperRoleName => $helperMethod) {
            expect($user->{$helperMethod}())
                ->toBe($user->hasRole($helperRoleName));
        }
    }
})->repeat(25)->group('rbac', 'property');

// Feature: dynamic-rbac, Property 18: hasRole and Convenience Helpers Return Correct Boolean for Any User
// **Validates: Requirements 6.3**
it('does not treat an unassigned user as holding an unknown role', function (): void {
    $user = User::factory()->make(['role_id' => null]);
    $unknownRole = 'rbac-unassigned-'.Str::uuid();

    expect(User::getRoleId($unknownRole))->toBeNull()
        ->and($user->hasRole($unknownRole))->toBeFalse();
});

// **Validates: Requirements 6.1, 6.5**
it('uses the persisted student-org slug on student organization portal routes', function (): void {
    $route = Route::getRoutes()->getByName('student-org.dashboard');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain('role:student-org');
});
