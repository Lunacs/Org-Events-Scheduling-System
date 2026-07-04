<?php

namespace App\Http\Middleware;

use App\Models\TicketDraft;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Terminating middleware that removes stale ticket drafts (and their associated
 * disk files) once every 23 hours via a cache lock.
 *
 * "Terminating" means the cleanup runs AFTER the HTTP response is sent, so it
 * never adds latency to the user-facing request.
 *
 * Why 30 days? Gives users a reasonable window to resume an incomplete submission
 * without permanently bloating the `draft-attachments/` directory.
 */
class CleanupStaleDrafts
{
    private const LAST_RUN_KEY = 'cleanup_stale_drafts_last_run';

    /**
     * Pass the request through unchanged; all work happens in terminate().
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Runs after the response is sent to the client.
     * Skipped if cleanup already ran within the last 23 hours.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (Cache::has(self::LAST_RUN_KEY)) {
            return;
        }

        // Lock for the next 23 hours before cleaning up
        Cache::put(self::LAST_RUN_KEY, now()->toDateTimeString(), now()->addHours(23));

        $this->cleanup();
    }

    /**
     * Delete ticket_drafts rows that haven't been touched in > 30 days,
     * along with their associated files from disk.
     */
    public function cleanup(): void
    {
        try {
            $stale = TicketDraft::with('attachments')
                ->where('updated_at', '<', now()->subDays(30))
                ->get();

            foreach ($stale as $draft) {
                // Remove every physical file attached to this draft
                foreach ($draft->attachments as $attachment) {
                    Storage::delete($attachment->file_path);
                }

                // Cascades to ticket_draft_attachments rows automatically
                $draft->delete();
            }

            Log::info('[CleanupStaleDrafts] Removed ' . $stale->count() . ' stale draft(s).');
        } catch (\Throwable $e) {
            Log::error('[CleanupStaleDrafts] Cleanup failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
