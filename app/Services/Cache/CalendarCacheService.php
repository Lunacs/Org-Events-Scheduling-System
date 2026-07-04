<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class CalendarCacheService
{
    use SupportsTags;

    /**
     * Cache TTL in seconds (1 hour for calendar data)
     */
    public const TTL_SECONDS = 3600;

    /**
     * Get or cache calendar events for a specific month and year
     */
    public static function getMonthlyEvents(int $year, int $month, callable $callback)
    {
        $key = "calendar:approved:{$year}:{$month}";

        if (self::supportsTags()) {
            return Cache::tags(['calendar', 'events', "month:{$year}-{$month}"])
                ->remember($key, self::TTL_SECONDS, $callback);
        }

        return Cache::remember($key, self::TTL_SECONDS, $callback);
    }

    /**
     * Get or cache calendar events filtered by role and user
     */
    public static function getRoleMonthlyEvents(string $role, int $userId, int $year, int $month, callable $callback)
    {
        $key = "calendar:role:{$role}:{$userId}:{$year}:{$month}";

        if (self::supportsTags()) {
            return Cache::tags(['calendar', 'events', "role:{$role}", "user:{$userId}", "month:{$year}-{$month}"])
                ->remember($key, self::TTL_SECONDS, $callback);
        }

        return Cache::remember($key, self::TTL_SECONDS, $callback);
    }

    /**
     * Invalidate specific month's calendar cache
     */
    public static function clearMonthlyEvents(int $year, int $month): void
    {
        if (self::supportsTags()) {
            Cache::tags(["month:{$year}-{$month}"])->flush();
        }
    }

    /**
     * Invalidate all calendar cache (useful when an event is approved/rejected/rescheduled)
     */
    public static function clearAllCalendar(): void
    {
        if (self::supportsTags()) {
            Cache::tags(['calendar'])->flush();
        }
    }
}
