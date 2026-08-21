<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    use SupportsTags;

    /**
     * Cache TTL in seconds (5 minutes for stats)
     */
    public const TTL_SECONDS = 300;

    /**
     * Get or cache dashboard statistics for a specific role
     */
    public static function getRoleStats(string $role, callable $callback, ?int $userId = null, ?int $orgId = null)
    {
        $key = "dashboard:{$role}";
        $tags = ['dashboard', "role:{$role}"];

        if ($userId) {
            $key .= ":user:{$userId}";
            $tags[] = "user:{$userId}";
        }

        if ($orgId) {
            $key .= ":org:{$orgId}";
            $tags[] = "org:{$orgId}";
        }

        $key .= ':stats';

        if (self::supportsTags()) {
            return Cache::tags($tags)->remember($key, self::TTL_SECONDS, $callback);
        }

        self::trackKey('dashboard:known_keys', $key);

        return Cache::remember($key, self::TTL_SECONDS, $callback);
    }

    /**
     * Get or cache a specific dashboard widget
     */
    public static function getDashboardWidget(string $role, string $widgetName, callable $callback, ?int $userId = null, ?int $orgId = null, int $ttl = self::TTL_SECONDS)
    {
        $key = "dashboard:{$role}";
        $tags = ['dashboard', "role:{$role}"];

        if ($userId) {
            $key .= ":user:{$userId}";
            $tags[] = "user:{$userId}";
        }

        if ($orgId) {
            $key .= ":org:{$orgId}";
            $tags[] = "org:{$orgId}";
        }

        $key .= ":widget:{$widgetName}";

        if (self::supportsTags()) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        self::trackKey('dashboard:known_keys', $key);

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate dashboard stats for a specific role
     */
    public static function clearRoleStats(string $role, ?int $userId = null, ?int $orgId = null): void
    {
        $tags = ['dashboard', "role:{$role}"];

        if ($userId) {
            $tags[] = "user:{$userId}";
        }

        if ($orgId) {
            $tags[] = "org:{$orgId}";
        }

        $key = "dashboard:{$role}";
        if ($userId) {
            $key .= ":user:{$userId}";
        }
        if ($orgId) {
            $key .= ":org:{$orgId}";
        }
        $key .= ':stats';

        if (self::supportsTags()) {
            Cache::tags($tags)->flush();
        } else {
            Cache::forget($key);
        }
    }

    /**
     * Clear all dashboard caches (e.g., on a major state change)
     */
    public static function clearAllDashboards(): void
    {
        if (self::supportsTags()) {
            Cache::tags(['dashboard'])->flush();
        } else {
            self::clearTrackedKeys('dashboard:known_keys');
        }
    }
}
