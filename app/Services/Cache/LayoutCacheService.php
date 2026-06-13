<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class LayoutCacheService
{
    /**
     * Get or cache unread notification count for a user
     */
    public static function getUnreadNotificationCount(int $userId, callable $callback): int
    {
        $key = "notifications:{$userId}:unread_count";
        
        // Short TTL for notifications (2 minutes)
        return Cache::tags(['layout', 'notifications', "user:{$userId}"])
            ->remember($key, 120, $callback);
    }

    /**
     * Clear notification count cache for a user
     */
    public static function clearNotificationCount(int $userId): void
    {
        Cache::forget("notifications:{$userId}:unread_count");
    }

    /**
     * Get or cache system settings
     */
    public static function getSystemSettings(string $settingKey, callable $callback)
    {
        $key = "settings:{$settingKey}";
        
        return Cache::tags(['layout', 'settings'])
            ->remember($key, 3600 * 24, $callback); // Cache for 24 hours
    }

    /**
     * Clear specific system setting cache
     */
    public static function clearSystemSetting(string $settingKey): void
    {
        Cache::forget("settings:{$settingKey}");
    }

    /**
     * Clear all layout/settings cache
     */
    public static function clearAllSettings(): void
    {
        Cache::tags(['settings'])->flush();
    }
}
