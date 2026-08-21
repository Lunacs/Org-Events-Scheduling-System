<?php

namespace App\Http\Controllers;

use App\Support\AttachmentMimeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DraftAttachmentController extends Controller
{
    /**
     * Stream a wizard-uploaded file (cached path) for preview or download.
     *
     * Supports both local filesystem paths and Livewire temporary files
     * stored on S3/R2 cloud storage.
     */
    public function show(Request $request, string $token): BinaryFileResponse|StreamedResponse
    {
        $payload = Cache::get('draft-attachment-preview:'.$token);

        abort_if(! is_array($payload), 404);
        abort_if((int) ($payload['user_id'] ?? 0) !== (int) auth()->id(), 403);

        $fileName = $payload['file_name'] ?? 'attachment';
        $forceDownload = $request->boolean('download');

        // Handle Livewire temporary files (S3/R2)
        if (isset($payload['livewire_filename'])) {
            return $this->streamFromLivewireDisk($payload, $fileName, $forceDownload);
        }

        // Handle Storage files (S3/R2/local)
        return $this->streamFromStorage($payload, $fileName, $forceDownload);
    }

    /**
     * Stream from Livewire's configured disk (works with S3/R2/local).
     */
    private function streamFromLivewireDisk(array $payload, string $fileName, bool $forceDownload): StreamedResponse
    {
        $livewireFile = TemporaryUploadedFile::createFromLivewire($payload['livewire_filename']);

        abort_if(! $livewireFile->exists(), 404);

        $storedMime = $payload['mime'] ?? null;
        $mime = AttachmentMimeType::resolve(
            $fileName,
            is_string($storedMime) ? $storedMime : null,
            null
        );

        $disposition = $forceDownload
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT
            : ResponseHeaderBag::DISPOSITION_INLINE;

        $dispositionHeader = (new ResponseHeaderBag)->makeDisposition($disposition, $fileName);

        return new StreamedResponse(function () use ($livewireFile) {
            $stream = $livewireFile->readStream();
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $dispositionHeader,
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    /**
     * Stream from Storage (works with S3/R2/local).
     */
    private function streamFromStorage(array $payload, string $fileName, bool $forceDownload): StreamedResponse
    {
        $relativePath = $payload['storage_path'] ?? null;
        abort_if(! is_string($relativePath) || ! Storage::exists($relativePath), 404);

        $storedMime = $payload['mime'] ?? null;
        $mime = AttachmentMimeType::resolve($fileName, is_string($storedMime) ? $storedMime : null, null);

        $disposition = $forceDownload
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT
            : ResponseHeaderBag::DISPOSITION_INLINE;

        $dispositionHeader = (new ResponseHeaderBag)->makeDisposition($disposition, $fileName);

        return new StreamedResponse(function () use ($relativePath) {
            $stream = Storage::readStream($relativePath);
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $dispositionHeader,
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
