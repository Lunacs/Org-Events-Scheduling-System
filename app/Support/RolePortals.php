<?php

namespace App\Support;

use App\Models\Roles;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Resolves which portal each role belongs to.
 *
 * The role table is small, so the whole `role_id => portal` map is cached and
 * memoized for the current request (the class is bound as a scoped singleton).
 */
class RolePortals
{
    public const CACHE_KEY = 'rbac_role_portals';

    /** @var array<int, string>|null */
    private ?array $portalsByRoleId = null;

    public function portalFor(?int $roleId): string
    {
        if ($roleId === null) {
            return Roles::VIEWER_PORTAL;
        }

        return $this->map()[$roleId] ?? Roles::VIEWER_PORTAL;
    }

    /**
     * @return list<int>
     */
    public function roleIdsFor(string $portal): array
    {
        return array_keys(array_filter(
            $this->map(),
            static fn (string $rolePortal): bool => $rolePortal === $portal,
        ));
    }

    /**
     * Forget the cached map now and again once any open transaction commits,
     * so a concurrent rebuild cannot keep a stale map.
     */
    public function flush(): void
    {
        $this->portalsByRoleId = null;
        Cache::forget(self::CACHE_KEY);

        DB::afterCommit(function (): void {
            $this->portalsByRoleId = null;
            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * @return array<int, string>
     */
    private function map(): array
    {
        return $this->portalsByRoleId ??= Cache::rememberForever(
            self::CACHE_KEY,
            static fn (): array => Roles::query()
                ->get(['role_id', 'role_name', 'base_portal'])
                ->mapWithKeys(static fn (Roles $role): array => [(int) $role->role_id => $role->portal()])
                ->all(),
        );
    }
}
