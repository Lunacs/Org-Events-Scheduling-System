<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait HandlesDraftAttachmentPreviews
{
    /**
     * @return array<int, mixed>
     */
    abstract protected function draftAttachmentFileList(): array;

    public function previewDraftAttachment(int $index): void
    {
        $this->dispatchDraftAttachmentUrl($index, false);
    }

    public function downloadDraftAttachment(int $index): void
    {
        $this->dispatchDraftAttachmentUrl($index, true);
    }

    /**
     * Resolve preview/download URL for a draft attachment.
     *
     * Supports two shapes for each item in the file list:
     *   - DB-backed array  (keys: file_path, file_name, file_type) — stored on the
     *     'public' disk via Storage::put().
     *   - TemporaryUploadedFile object — legacy path for in-memory uploads that have
     *     not yet been persisted to the database.
     */
    private function dispatchDraftAttachmentUrl(int $index, bool $forceDownload): void
    {
        $files = $this->draftAttachmentFileList();

        if (! isset($files[$index])) {
            $this->warning('Attachment not found.');

            return;
        }

        $file = $files[$index];

        // ── DB-backed attachment (stored in ticket_draft_attachments) ──────────────
        if (is_array($file)) {
            $relativePath = $file['file_path'] ?? null;
            $fileName = $file['file_name'] ?? 'download';
            $mime = $file['file_type'] ?? 'application/octet-stream';

            if (! $relativePath || ! Storage::exists($relativePath)) {
                $this->warning('File is no longer available. Re-upload if needed.');

                return;
            }

            $token = (string) Str::uuid();

            Cache::put(
                'draft-attachment-preview:'.$token,
                [
                    'user_id' => auth()->id(),
                    'storage_path' => $relativePath,
                    'file_name' => $fileName,
                    'mime' => $mime,
                ],
                now()->addMinutes(5)
            );

            $params = ['token' => $token];
            if ($forceDownload) {
                $params['download'] = 1;
            }

            $url = URL::temporarySignedRoute('attachments.draft', now()->addMinutes(5), $params);

            if ($forceDownload) {
                $this->dispatch('download-attachment', url: $url, filename: $fileName);
            } else {
                $this->dispatch('open-attachment-preview', url: $url);
            }

            return;
        }

        // ── TemporaryUploadedFile object (FilePond / S3/R2 compatible) ─────────────

        // Check file availability using storage-aware methods for S3/R2 compatibility
        if ($file instanceof TemporaryUploadedFile) {
            if (! $file->exists()) {
                $this->warning('File is no longer available. Re-upload if needed.');

                return;
            }
        } else {
            $path = $file->getRealPath();
            if (! $path || ! is_readable($path)) {
                $this->warning('File is no longer available. Re-upload if needed.');

                return;
            }
        }

        $token = (string) Str::uuid();

        // Store file reference in cache — use the Livewire storage path for S3 files
        $cachePayload = [
            'user_id' => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
        ];

        if ($file instanceof TemporaryUploadedFile) {
            // Store the Livewire temp filename so we can read from the correct disk
            $cachePayload['livewire_filename'] = $file->getFilename();
        } else {
            $cachePayload['path'] = $file->getRealPath();
        }

        Cache::put(
            'draft-attachment-preview:'.$token,
            $cachePayload,
            now()->addMinutes(5)
        );

        $params = ['token' => $token];
        if ($forceDownload) {
            $params['download'] = 1;
        }

        $url = URL::temporarySignedRoute('attachments.draft', now()->addMinutes(5), $params);

        if ($forceDownload) {
            $this->dispatch('download-attachment', url: $url, filename: $file->getClientOriginalName());
        } else {
            $this->dispatch('open-attachment-preview', url: $url);
        }
    }
}
