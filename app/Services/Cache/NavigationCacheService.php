<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class NavigationCacheService
{
    use SupportsTags;

    /**
     * Cache TTL in seconds (1 hour)
     */
    public const TTL_SECONDS = 3600;

    /**
     * Get or cache the sidebar navigation data for a specific user and role.
     */
    public static function getSidebarLinks(int $userId, string $role, callable $callback): array
    {
        $key = "navigation:{$role}:{$userId}";

        if (self::supportsTags()) {
            return Cache::tags(['navigation', "user:{$userId}"])
                ->remember($key, self::TTL_SECONDS, $callback);
        }

        return Cache::remember($key, self::TTL_SECONDS, $callback);
    }

    /**
     * Invalidate the navigation cache for a specific user.
     */
    public static function clearUserNavigation(int $userId): void
    {
        if (self::supportsTags()) {
            Cache::tags(["navigation:user:{$userId}"])->flush();
        } else {
            // Fallback: clear by key
            Cache::forget("navigation:superadmin:{$userId}");
            Cache::forget("navigation:osa:{$userId}");
            Cache::forget("navigation:gso:{$userId}");
            Cache::forget("navigation:student-org:{$userId}");
        }
    }
}
