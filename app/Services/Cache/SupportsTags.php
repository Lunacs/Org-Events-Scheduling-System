<?php

namespace App\Services\Cache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

trait SupportsTags
{
    /**
     * Check if the current cache store supports tagging.
     */
    protected static function supportsTags(): bool
    {
        return Cache::store()->getStore() instanceof TaggableStore;
    }

    /**
     * Track a cache key under a specific tracker key (for non-taggable stores).
     * Uses a lock to prevent concurrent write race conditions and caps growth.
     */
    protected static function trackKey(string $trackerKey, string $cacheKey): void
    {
        try {
            // Use cache lock to prevent concurrent read-modify-write race conditions.
            // If the driver doesn't support locks (e.g. file driver without database lock),
            // it will throw a BadMethodCallException which we catch and gracefully fall back.
            $lock = Cache::lock($trackerKey.':lock', 5);
            $lock->block(2, function () use ($trackerKey, $cacheKey) {
                self::appendKeyToTracker($trackerKey, $cacheKey);
            });
        } catch (\BadMethodCallException $e) {
            // Lock not supported by driver - fallback to non-locked append
            self::appendKeyToTracker($trackerKey, $cacheKey);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SupportsTags: Failed to track key', [
                'tracker' => $trackerKey,
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Helper to append a key to the tracker array with size limits.
     */
    private static function appendKeyToTracker(string $trackerKey, string $cacheKey): void
    {
        $keys = Cache::get($trackerKey, []);
        if (!is_array($keys)) {
            $keys = [];
        }
        
        // Cap the tracker to 500 keys to avoid unbounded memory/storage growth.
        // If cap is reached, we stop tracking new keys and let them expire naturally.
        if (count($keys) >= 500) {
            return;
        }

        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::put($trackerKey, $keys, 2592000); // Reset TTL to 30 days
        }
    }

    /**
     * Clear all keys tracked under a specific tracker key, and delete the tracker key.
     */
    protected static function clearTrackedKeys(string $trackerKey): void
    {
        try {
            $keys = Cache::get($trackerKey, []);
            if (is_array($keys)) {
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }
            Cache::forget($trackerKey);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SupportsTags: Failed to clear tracked keys', [
                'tracker' => $trackerKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear tracked keys matching a specific suffix, and update the tracker key.
     */
    protected static function clearTrackedKeysMatching(string $trackerKey, string $suffix): void
    {
        try {
            $keys = Cache::get($trackerKey, []);
            if (is_array($keys)) {
                $remaining = [];
                foreach ($keys as $key) {
                    if (str_ends_with($key, $suffix)) {
                        Cache::forget($key);
                    } else {
                        $remaining[] = $key;
                    }
                }
                if (empty($remaining)) {
                    Cache::forget($trackerKey);
                } else {
                    Cache::put($trackerKey, $remaining, 2592000);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SupportsTags: Failed to clear matching tracked keys', [
                'tracker' => $trackerKey,
                'suffix' => $suffix,
                'error' => $e->getMessage()
            ]);
        }
    }
}
