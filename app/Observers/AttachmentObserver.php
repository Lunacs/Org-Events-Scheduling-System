<?php

namespace App\Observers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttachmentObserver
{
    /**
     * Handle the Attachment "deleted" event.
     *
     * Automatically removes the file from storage when the attachment record is deleted.
     */
    public function deleted(Attachment $attachment): void
    {
        $disk = Storage::disk(config('filesystems.default'));

        if ($attachment->file_path && $disk->exists($attachment->file_path)) {
            try {
                $disk->delete($attachment->file_path);

                Log::info('Attachment file deleted from storage', [
                    'attachment_id' => $attachment->attachment_id,
                    'file_path' => $attachment->file_path,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to delete attachment file from storage', [
                    'attachment_id' => $attachment->attachment_id,
                    'file_path' => $attachment->file_path,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
