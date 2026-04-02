<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Cache;
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

    private function dispatchDraftAttachmentUrl(int $index, bool $forceDownload): void
    {
        $files = $this->draftAttachmentFileList();

        if (! isset($files[$index])) {
            $this->warning('Attachment not found.');

            return;
        }

        $file = $files[$index];
        $path = $file->getRealPath();

        if (! $path || ! is_readable($path)) {
            $this->warning('File is no longer available. Re-upload if needed.');

            return;
        }

        $token = (string) Str::uuid();

        Cache::put(
            'draft-attachment-preview:'.$token,
            [
                'user_id' => auth()->id(),
                'path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
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
