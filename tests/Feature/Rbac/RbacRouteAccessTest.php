<?php

use App\Models\Roles;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    foreach (['superadmin', 'osa'] as $roleName) {
        Cache::forget("role_id_{$roleName}");
        Roles::query()->firstOrCreate(['role_name' => $roleName]);
    }

    User::clearRoleIdCache();
});

/**
 * @return array<string, array<int, int>>
 */
function rbacManagementRouteParameters(Roles $superadminRole): array
{
    return [
        'superadmin.roles' => [],
        'superadmin.roles.permissions' => ['roleId' => $superadminRole->role_id],
        'superadmin.permissions' => [],
    ];
}

// Task 13.3: RBAC route access control
// **Validates: Requirements 7.6**
it('redirects unauthenticated users from every RBAC management route', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();

    foreach (rbacManagementRouteParameters($superadminRole) as $routeName => $parameters) {
        $this->get(route($routeName, $parameters))
            ->assertRedirect(route('login'));
    }
});

// Task 13.3: RBAC route access control
// **Validates: Requirements 7.6**
it('redirects non-superadmin users to their dashboard without logging them out', function (): void {
    $osaRole = Roles::query()->where('role_name', 'osa')->sole();
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $user = User::factory()->create(['role_id' => $osaRole->role_id]);

    foreach (rbacManagementRouteParameters($superadminRole) as $routeName => $parameters) {
        $this->actingAs($user)
            ->get(route($routeName, $parameters))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasErrors([
                'email' => 'You are not authorized to access this area.',
            ]);

        $this->assertAuthenticatedAs($user);
    }
});

// Task 13.3: RBAC route access control
// **Validates: Requirements 7.6**
it('allows superadmin users to load every RBAC management route', function (): void {
    $superadminRole = Roles::query()->where('role_name', 'superadmin')->sole();
    $user = User::factory()->create(['role_id' => $superadminRole->role_id]);

    foreach (rbacManagementRouteParameters($superadminRole) as $routeName => $parameters) {
        $this->actingAs($user)
            ->get(route($routeName, $parameters))
            ->assertOk();
    }
});
