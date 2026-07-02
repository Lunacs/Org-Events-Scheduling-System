<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
            $fileName     = $file['file_name'] ?? 'download';
            $mime         = $file['file_type'] ?? 'application/octet-stream';

            if (! $relativePath || ! Storage::disk('local')->exists($relativePath)) {
                $this->warning('File is no longer available. Re-upload if needed.');

                return;
            }

            $absolutePath = Storage::disk('local')->path($relativePath);

            $token = (string) Str::uuid();

            Cache::put(
                'draft-attachment-preview:'.$token,
                [
                    'user_id'   => auth()->id(),
                    'path'      => $absolutePath,
                    'file_name' => $fileName,
                    'mime'      => $mime,
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

        // ── Legacy TemporaryUploadedFile object ────────────────────────────────────
        $path = $file->getRealPath();

        if (! $path || ! is_readable($path)) {
            $this->warning('File is no longer available. Re-upload if needed.');

            return;
        }

        $token = (string) Str::uuid();

        Cache::put(
            'draft-attachment-preview:'.$token,
            [
                'user_id'   => auth()->id(),
                'path'      => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime'      => $file->getMimeType(),
            ],
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
