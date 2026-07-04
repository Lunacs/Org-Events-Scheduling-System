<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class ArchiveCacheService
{
    use SupportsTags;

    /**
     * Cache tags for archives
     */
    protected static array $tags = ['archives'];

    /**
     * Default cache duration (60 minutes for archives since they rarely change)
     */
    protected static int $duration = 3600;

    /**
     * Get cached available years.
     */
    public static function getAvailableYears(string $role, \Closure $callback)
    {
        $cacheKey = "archives:{$role}:years";

        if (self::supportsTags()) {
            return Cache::tags(self::$tags)->remember($cacheKey, self::$duration, $callback);
        }

        self::trackKey('archives:known_keys', $cacheKey);

        return Cache::remember($cacheKey, self::$duration, $callback);
    }

    /**
     * Get cached archive paginated results.
     * Note: For paginated queries, we cache the page number as well.
     */
    public static function getArchivedEvents(string $role, string $search, string $status, string $organization, string $year, string $eventType, int $page, \Closure $callback)
    {
        // Hash search term to keep key length reasonable
        $searchHash = $search ? md5($search) : 'none';
        $cacheKey = "archives:{$role}:events:{$searchHash}:{$status}:{$organization}:{$year}:{$eventType}:page:{$page}";

        if (self::supportsTags()) {
            return Cache::tags(self::$tags)->remember($cacheKey, self::$duration, $callback);
        }

        self::trackKey('archives:known_keys', $cacheKey);

        return Cache::remember($cacheKey, self::$duration, $callback);
    }

    /**
     * Get cached superadmin archive collection.
     */
    public static function getSuperadminArchives(string $search, string $type, string $dateFrom, string $dateTo, \Closure $callback)
    {
        $searchHash = $search ? md5($search) : 'none';
        $cacheKey = "archives:superadmin:items:{$searchHash}:{$type}:{$dateFrom}:{$dateTo}";

        if (self::supportsTags()) {
            return Cache::tags(self::$tags)->remember($cacheKey, self::$duration, $callback);
        }

        self::trackKey('archives:known_keys', $cacheKey);

        return Cache::remember($cacheKey, self::$duration, $callback);
    }

    /**
     * Invalidate all archive caches.
     * Call this when a new event is archived/completed.
     */
    public static function clearAllArchives(): void
    {
        if (self::supportsTags()) {
            Cache::tags(self::$tags)->flush();
        } else {
            self::clearTrackedKeys('archives:known_keys');
        }
    }
}
