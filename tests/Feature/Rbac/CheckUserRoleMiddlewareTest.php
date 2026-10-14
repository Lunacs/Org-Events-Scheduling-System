<?php

use App\Models\Roles;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Validates: Requirements 6.2
it('redirects an authenticated user with an unauthorized error when the requested role is unknown', function (): void {
    $role = Roles::query()->create([
        'role_name' => 'rbac-task-12-1-role',
    ]);
    $user = User::factory()->create([
        'role_id' => $role->role_id,
    ]);
    $unknownRole = 'rbac-task-12-1-unknown-'.Str::uuid();
    $path = '/rbac-task-12-1/'.Str::uuid();

    Route::get($path, static fn (): string => 'authorized')->middleware("role:{$unknownRole}");

    $this->actingAs($user)
        ->get($path)
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'email' => 'You are not authorized to access this area.',
        ]);
});

it('redirects valid role mismatches to the authenticated user dashboard without logging them out', function (string $actualRoleName, string $requestedRoleName, string $dashboardRoute): void {
    foreach ([$actualRoleName, $requestedRoleName] as $roleName) {
        Cache::forget("role_id_{$roleName}");
        Roles::query()->firstOrCreate(['role_name' => $roleName]);
    }

    User::clearRoleIdCache();

    $actualRole = Roles::query()->where('role_name', $actualRoleName)->sole();
    $user = User::factory()->create(['role_id' => $actualRole->role_id]);
    $path = '/rbac-known-role-mismatch/'.Str::uuid();

    Route::get($path, static fn (): string => 'authorized')->middleware("role:{$requestedRoleName}");

    $this->actingAs($user)
        ->get($path)
        ->assertRedirect(route($dashboardRoute))
        ->assertSessionHasErrors([
            'email' => 'You are not authorized to access this area.',
        ]);

    $this->assertAuthenticatedAs($user);
})->with([
    'superadmin redirected from OSA' => ['superadmin', 'osa', 'superadmin.dashboard'],
    'OSA redirected from GSO' => ['osa', 'gso', 'admin.dashboard'],
    'GSO redirected from student organizations' => ['gso', 'student-org', 'gso.dashboard'],
    'student organization redirected from superadmin' => ['student-org', 'superadmin', 'student-org.dashboard'],
]);

it('sends a custom viewer role without permitted pages to the no-access page instead of looping', function (): void {
    $customRole = Roles::query()->create(['role_name' => 'portalless-custom-role']);
    $user = User::factory()->create([
        'role_id' => $customRole->role_id,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('no-access'));

    $this->actingAs($user)
        ->get(route('no-access'))
        ->assertOk()
        ->assertSee('No pages assigned yet');

    $this->assertAuthenticatedAs($user);
});
