<?php

namespace App\Http\Controllers;

use App\Support\AttachmentMimeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DraftAttachmentController extends Controller
{
    /**
     * Stream a wizard-uploaded file (cached path) for preview or download.
     */
    public function show(Request $request, string $token): BinaryFileResponse
    {
        $payload = Cache::get('draft-attachment-preview:'.$token);

        abort_if(! is_array($payload), 404);
        abort_if((int) ($payload['user_id'] ?? 0) !== (int) auth()->id(), 403);

        $path = $payload['path'] ?? null;
        abort_if(! is_string($path) || ! is_file($path) || ! is_readable($path), 404);

        $fileName = $payload['file_name'] ?? basename($path);
        $storedMime = $payload['mime'] ?? null;
        $detected = @mime_content_type($path) ?: null;
        $mime = AttachmentMimeType::resolve($fileName, is_string($storedMime) ? $storedMime : null, is_string($detected) ? $detected : null);

        $forceDownload = $request->boolean('download');

        $response = new BinaryFileResponse($path, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=300',
        ], false);

        $response->setContentDisposition(
            $forceDownload ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE,
            $fileName
        );

        return $response;
    }
}
