<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class EventCacheService
{
    /**
     * Cache TTL in seconds (10 minutes)
     */
    public const TTL_SECONDS = 600;

    /**
     * Get or cache paginated event requests list
     */
    public static function getRequestList(string $role, string $status, int $page, callable $callback, ?int $userId = null, ?int $orgId = null)
    {
        $key = "xhr:tickets:{$role}:{$status}:page:{$page}";
        $tags = ['events', 'requests', "role:{$role}", "status:{$status}"];

        if ($userId) {
            $key .= ":user:{$userId}";
            $tags[] = "user:{$userId}";
        }

        if ($orgId) {
            $key .= ":org:{$orgId}";
            $tags[] = "org:{$orgId}";
        }

        return Cache::tags($tags)->remember($key, self::TTL_SECONDS, $callback);
    }

    /**
     * Invalidate specific request lists based on tags
     */
    public static function clearRequestLists(): void
    {
        Cache::tags(['requests'])->flush();
    }

    /**
     * Clear all related event caches (dashboards, calendar, requests)
     */
    public static function clearAllEventRelatedCaches(): void
    {
        Cache::tags(['events', 'dashboard', 'calendar', 'requests'])->flush();
    }
}
