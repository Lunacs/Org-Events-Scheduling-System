<?php

use App\Models\Permission;
use App\Models\Roles;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

// Validates: Requirements 5.7
it('allows a superadmin to perform an unregistered ability', function (): void {
    $superadminRole = Roles::query()->firstOrCreate([
        'role_name' => 'superadmin',
    ]);
    $superadmin = User::factory()->create([
        'role_id' => $superadminRole->role_id,
    ]);

    expect(Gate::forUser($superadmin)->check('rbac.task-5-1.unregistered'))->toBeTrue();
});

// Validates: Requirements 5.7
it('falls through to normal ability definitions for non-superadmins', function (): void {
    $osaRole = Roles::query()->firstOrCreate([
        'role_name' => 'osa',
    ]);
    $osaUser = User::factory()->create([
        'role_id' => $osaRole->role_id,
    ]);

    Gate::define('rbac.task-5-1.fall-through', fn (): bool => true);

    expect(Gate::forUser($osaUser)->check('rbac.task-5-1.fall-through'))->toBeTrue();
});

// Validates: Requirements 5.1, 5.2, 5.4
it('registers permission gates with strict cached role membership', function (): void {
    $role = Roles::query()->create([
        'role_name' => 'rbac-task-5-2',
    ]);
    $grantedPermission = Permission::query()->create([
        'name' => 'tickets.export',
    ]);
    $deniedPermission = Permission::query()->create([
        'name' => 'tickets.delete',
    ]);
    $strictPermission = Permission::query()->create([
        'name' => '123',
    ]);
    $role->permissions()->sync([$grantedPermission->permission_id]);
    $user = User::factory()->create([
        'role_id' => $role->role_id,
    ]);
    $cacheKey = "rbac_role_permissions_{$role->role_id}";

    (new AppServiceProvider($this->app))->boot();
    Cache::forget($cacheKey);

    expect(Gate::forUser($user)->check($grantedPermission->name))->toBeTrue()
        ->and(Gate::forUser($user)->check($deniedPermission->name))->toBeFalse()
        ->and(Cache::has($cacheKey))->toBeTrue();

    Cache::put($cacheKey, [123], now()->addMinute());

    expect(Gate::forUser($user)->check($strictPermission->name))->toBeFalse();
});

// Validates: Requirements 5.3
it('denies permission gates for unauthenticated users and users without a role', function (): void {
    $permission = Permission::query()->create([
        'name' => 'calendar.export',
    ]);
    $userWithoutRole = User::factory()->make([
        'role_id' => null,
    ]);

    (new AppServiceProvider($this->app))->boot();

    expect(Gate::forUser($userWithoutRole)->check($permission->name))->toBeFalse()
        ->and(Gate::forUser(null)->check($permission->name))->toBeFalse();
});

// Validates: Requirements 5.6
it('logs a warning and skips permission gate registration when permission loading fails', function (): void {
    $permission = Permission::query()->create([
        'name' => 'system.audit',
    ]);
    $connectionResolver = Permission::getConnectionResolver();

    Log::spy();
    Permission::setConnectionResolver(new class implements ConnectionResolverInterface
    {
        public function connection($name = null)
        {
            throw new RuntimeException('Permission database connection is unavailable.');
        }

        public function getDefaultConnection()
        {
            return 'mysql';
        }

        public function setDefaultConnection($name): void {}
    });

    try {
        (new AppServiceProvider($this->app))->boot();
    } finally {
        Permission::setConnectionResolver($connectionResolver);
    }

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'RBAC: Failed to load permissions at boot — Gate definitions skipped.'
            && array_key_exists('error', $context));

    expect(Gate::abilities())->not->toHaveKey($permission->name);
});
// Feature: dynamic-rbac, Property 13: Gate::check Returns Correct Authorization for Any User-Role-Permission Triple
// **Validates: Requirements 5.1, 5.2**
it('authorizes randomized role-permission assignments while always allowing superadmin', function (): void {
    $permissions = collect(range(1, 10))->map(
        fn (int $permissionIndex): Permission => Permission::query()->create([
            'name' => "rbac.property-13-{$permissionIndex}.check",
        ]),
    );
    $roles = collect(range(1, 4))->map(
        fn (int $roleIndex): Roles => Roles::query()->create([
            'role_name' => "rbac-property-13-role-{$roleIndex}",
        ]),
    );
    $superadminRole = Roles::query()->firstOrCreate([
        'role_name' => 'superadmin',
    ]);

    foreach ($roles as $role) {
        $role->permissions()->sync(
            $permissions
                ->filter(fn (): bool => (bool) random_int(0, 1))
                ->pluck('permission_id')
                ->all(),
        );
    }

    $superadminRole->permissions()->sync([]);
    $roles->push($superadminRole);

    Cache::flush();
    (new AppServiceProvider($this->app))->boot();

    foreach ($roles as $role) {
        $user = User::factory()->create([
            'role_id' => $role->role_id,
        ]);
        $assignedPermissionIds = $role->permissions()->pluck('permissions.permission_id')->all();

        foreach ($permissions as $permission) {
            $expectedAuthorization = $role->role_name === 'superadmin'
                || in_array($permission->permission_id, $assignedPermissionIds, true);

            expect(Gate::forUser($user)->check($permission->name))->toBe($expectedAuthorization);
        }
    }
})->repeat(10)->group('rbac', 'property');
// Feature: dynamic-rbac, Property 14: Gate Denies All Checks for Null Role or Unauthenticated User
// **Validates: Requirements 5.3**
it('denies every registered permission to unauthenticated and role-less users', function (): void {
    $permissions = collect(range(1, 10))->map(
        fn (int $permissionIndex): Permission => Permission::query()->create([
            'name' => "rbac.property-14-{$permissionIndex}.deny",
        ]),
    );
    $userWithoutRole = User::factory()->make([
        'role_id' => null,
    ]);

    (new AppServiceProvider($this->app))->boot();

    foreach ($permissions as $permission) {
        expect(Gate::forUser(null)->check($permission->name))->toBeFalse()
            ->and(Gate::forUser($userWithoutRole)->check($permission->name))->toBeFalse();
    }
})->repeat(10)->group('rbac', 'property');

// Feature: dynamic-rbac, Property 16: Superadmin Gate::before Bypass Applies to All Permission Names
// **Validates: Requirements 5.7**
it('allows every randomized registered and unregistered permission name for a superadmin', function (): void {
    $superadminRole = Roles::query()->firstOrCreate([
        'role_name' => 'superadmin',
    ]);
    $superadmin = User::factory()->create([
        'role_id' => $superadminRole->role_id,
    ]);
    $permissionNamePrefix = 'rbac.property_16_'.bin2hex(random_bytes(8));
    $registeredPermission = Permission::query()->create([
        'name' => "{$permissionNamePrefix}.registered",
    ]);
    $unregisteredPermissionNames = collect(range(1, 100))->map(
        fn (int $iteration): string => "{$permissionNamePrefix}.unregistered_{$iteration}_".bin2hex(random_bytes(4)),
    );

    (new AppServiceProvider($this->app))->boot();

    expect(Gate::forUser($superadmin)->check($registeredPermission->name))->toBeTrue();

    foreach ($unregisteredPermissionNames as $permissionName) {
        expect(Permission::query()->where('name', $permissionName)->doesntExist())->toBeTrue()
            ->and(Gate::abilities())->not->toHaveKey($permissionName)
            ->and(Gate::forUser($superadmin)->check($permissionName))->toBeTrue();
    }
})->group('rbac', 'property');

it('leaves non-permission policy abilities to their own definitions for a superadmin', function (): void {
    $superadminRole = Roles::query()->firstOrCreate([
        'role_name' => 'superadmin',
    ]);
    $superadmin = User::factory()->create([
        'role_id' => $superadminRole->role_id,
    ]);

    Gate::define('rbacPolicyStyleAbility', fn (): bool => false);

    expect(Gate::forUser($superadmin)->check('rbacPolicyStyleAbility'))->toBeFalse()
        ->and(Gate::forUser($superadmin)->check('rbacUndefinedPolicyStyleAbility'))->toBeFalse()
        ->and(Gate::forUser($superadmin)->check('rbac.dotted.ability'))->toBeTrue();
});

it('reads permission names from the cache instead of querying the permissions table on every boot', function (): void {
    Permission::query()->create(['name' => 'rbac.cached_boot.view']);
    Cache::forget(Permission::NAMES_CACHE_KEY);

    (new AppServiceProvider($this->app))->boot();

    DB::enableQueryLog();
    DB::flushQueryLog();
    (new AppServiceProvider($this->app))->boot();
    $permissionQueries = collect(DB::getQueryLog())
        ->filter(fn (array $query): bool => str_contains($query['query'], '`permissions`'));
    DB::disableQueryLog();

    expect($permissionQueries)->toBeEmpty()
        ->and(Cache::get(Permission::NAMES_CACHE_KEY))->toContain('rbac.cached_boot.view');
});

it('forgets the cached permission names whenever a permission is created, renamed, or deleted', function (): void {
    $permission = Permission::query()->create(['name' => 'rbac.cache_bust.original']);

    (new AppServiceProvider($this->app))->boot();
    expect(Cache::has(Permission::NAMES_CACHE_KEY))->toBeTrue();

    $permission->update(['name' => 'rbac.cache_bust.renamed']);
    expect(Cache::has(Permission::NAMES_CACHE_KEY))->toBeFalse();

    (new AppServiceProvider($this->app))->boot();
    expect(Cache::get(Permission::NAMES_CACHE_KEY))->toContain('rbac.cache_bust.renamed')
        ->not->toContain('rbac.cache_bust.original')
        ->and(Gate::abilities())->toHaveKey('rbac.cache_bust.renamed');

    $permission->delete();
    expect(Cache::has(Permission::NAMES_CACHE_KEY))->toBeFalse();

    Permission::query()->create(['name' => 'rbac.cache_bust.created']);
    (new AppServiceProvider($this->app))->boot();
    expect(Gate::abilities())->toHaveKey('rbac.cache_bust.created');
});
