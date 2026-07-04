<?php

namespace App\Jobs;

use App\Models\Attachment;
use App\Services\Cache\DashboardCacheService;
use App\Services\Cache\EventCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Process ticket attachments asynchronously to avoid blocking the user-facing
 * request on R2 network I/O during ticket submission.
 */
class ProcessTicketAttachments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $ticketId,
        public array $attachments
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! empty($this->attachments)) {
            foreach ($this->attachments as $fileData) {
                // Use stream copy instead of move to avoid S3 ACL restrictions on Cloudflare R2
                $newPath = "tickets/{$this->ticketId}/attachments/".basename($fileData['file_path']);

                if (Storage::exists($fileData['file_path'])) {
                    $stream = Storage::readStream($fileData['file_path']);
                    if ($stream) {
                        Storage::writeStream($newPath, $stream);
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                        Storage::delete($fileData['file_path']);
                    }
                }

                Attachment::create([
                    'ticket_id' => $this->ticketId,
                    'file_name' => $fileData['file_name'],
                    'file_path' => $newPath,
                    'file_type' => $fileData['file_type'],
                    'file_size' => $fileData['file_size'],
                ]);
            }
        }

        // Clear related caches after processing attachments
        DashboardCacheService::clearAllDashboards();
        EventCacheService::clearRequestLists();
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $e): void
    {
        $fileNames = array_column($this->attachments, 'file_name');

        Log::error('ProcessTicketAttachments job failed', [
            'ticket_id' => $this->ticketId,
            'files' => $fileNames,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
