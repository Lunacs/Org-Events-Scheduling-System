<?php

namespace App\Services;

use App\Models\Office_Approval;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Centralized cache invalidation for all dashboard and report caches.
 *
 * Called by model observers (TicketObserver, OfficeApprovalObserver) so
 * that any ticket status change, approval decision, or new ticket
 * automatically busts stale dashboard data.
 */
class DashboardCacheService
{
    /**
     * Invalidate all dashboard caches affected by a ticket change.
     */
    public static function invalidateForTicket(Ticket $ticket): void
    {
        // OSA dashboard caches (global keys)
        Cache::forget('osa_dashboard_stats');
        Cache::forget('osa_dashboard_recent_tickets');
        Cache::forget('osa_dashboard_pending_approvals');

        // Superadmin dashboard caches (global keys)
        Cache::forget('superadmin_dashboard_stats');
        Cache::forget('superadmin_dashboard_today_snapshot');
        Cache::forget('superadmin_dashboard_attention');
        Cache::forget('superadmin_dashboard_upcoming_events');

        // StudentOrg dashboard (per-user key)
        if ($ticket->user_id) {
            Cache::forget("studentorg_dashboard_tickets_{$ticket->user_id}");
            Cache::forget("studentorg_dashboard_recent_{$ticket->user_id}");
            Cache::forget("studentorg_dashboard_upcoming_{$ticket->user_id}");
        }

        // EventCalendar caches use composite filter keys — use tag-like prefix flush
        static::flushPrefixed('event_calendar:');

        // OSA report caches use composite date/filter keys
        static::flushPrefixed('osa_report_');

        Log::debug('DashboardCacheService: Invalidated caches for ticket', [
            'ticket_id' => $ticket->ticket_id,
        ]);
    }

    /**
     * Invalidate all dashboard caches affected by an office approval change.
     */
    public static function invalidateForApproval(Office_Approval $approval): void
    {
        $officeId = $approval->office_id;

        // GSO dashboard caches (per-office keys)
        Cache::forget("gso_dashboard_stats_{$officeId}");
        Cache::forget("gso_dashboard_pending_{$officeId}");
        Cache::forget("gso_dashboard_snapshot_{$officeId}");
        Cache::forget("gso_dashboard_activities_{$officeId}");
        Cache::forget("gso_dashboard_queue_{$officeId}");

        // GSO report seed (per-office key)
        Cache::forget("gso_report_seed_{$officeId}");

        // Also bust OSA/Superadmin dashboards since approval decisions
        // affect ticket status counts there too
        Cache::forget('osa_dashboard_stats');
        Cache::forget('osa_dashboard_pending_approvals');
        Cache::forget('superadmin_dashboard_stats');
        Cache::forget('superadmin_dashboard_attention');

        Log::debug('DashboardCacheService: Invalidated caches for approval', [
            'approval_id' => $approval->id,
            'office_id' => $officeId,
        ]);
    }

    /**
     * Forget all cache keys matching a prefix pattern.
     *
     * Redis supports SCAN-based key deletion; for the database driver
     * we simply forget known key patterns. This method attempts a
     * Redis-native approach first and falls back gracefully.
     */
    protected static function flushPrefixed(string $prefix): void
    {
        $store = Cache::getStore();

        // phpredis or predis driver — use SCAN to find matching keys
        if (method_exists($store, 'getRedis')) {
            try {
                $redis = $store->getRedis();
                $connection = method_exists($redis, 'connection')
                    ? $redis->connection()
                    : $redis;

                $cachePrefix = config('cache.prefix', '');
                $pattern = $cachePrefix.$prefix.'*';

                // Use SCAN for safe, non-blocking iteration
                $cursor = null;
                $deletedCount = 0;
                do {
                    $result = $connection->scan($cursor, ['MATCH' => $pattern, 'COUNT' => 100]);

                    if ($result === false) {
                        break;
                    }

                    [$cursor, $keys] = $result;

                    if (! empty($keys)) {
                        $connection->del(...$keys);
                        $deletedCount += count($keys);
                    }
                } while ($cursor > 0);

                Log::debug('DashboardCacheService: Redis prefix flush completed', [
                    'prefix' => $prefix,
                    'deleted_count' => $deletedCount,
                ]);

                return;
            } catch (\Throwable $e) {
                Log::warning('DashboardCacheService: Redis prefix flush failed, skipping', [
                    'prefix' => $prefix,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // For non-Redis drivers (database, file), we cannot scan keys by prefix.
        // The TTL will handle expiry naturally — typically 5-10 minutes.
    }
}
