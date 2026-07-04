<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class ReportCacheService
{
    use SupportsTags;

    /**
     * Cache tags for reports
     */
    protected static array $tags = ['reports'];

    /**
     * Default cache duration (10 minutes for reports)
     */
    protected static int $duration = 600;

    /**
     * Get cached report data.
     */
    public static function getReport(string $reportType, string $dateFrom, string $dateTo, string $organizationFilter, \Closure $callback)
    {
        $cacheKey = "reports:{$reportType}:{$dateFrom}:{$dateTo}:{$organizationFilter}";

        if (self::supportsTags()) {
            return Cache::tags(self::$tags)->remember($cacheKey, self::$duration, $callback);
        }

        return Cache::remember($cacheKey, self::$duration, $callback);
    }

    /**
     * Get cached chart data.
     */
    public static function getChartData(string $chartType, \Closure $callback)
    {
        $cacheKey = "reports:chart:{$chartType}";

        if (self::supportsTags()) {
            return Cache::tags(self::$tags)->remember($cacheKey, self::$duration, $callback);
        }

        return Cache::remember($cacheKey, self::$duration, $callback);
    }

    /**
     * Invalidate all report caches.
     * Call this when a ticket status changes (e.g., approved, for_revision)
     */
    public static function clearAllReports(): void
    {
        if (self::supportsTags()) {
            Cache::tags(self::$tags)->flush();
        }
    }
}
