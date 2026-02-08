<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Serve a file for preview (inline disposition) via signed URL.
     * This endpoint requires a valid signature to access.
     */
    public function preview(Request $request, Attachment $attachment): StreamedResponse
    {
        return $this->serveFile($attachment, false);
    }

    /**
     * Serve a file for download (attachment disposition) via signed URL.
     * This endpoint requires a valid signature to access.
     */
    public function download(Request $request, Attachment $attachment): StreamedResponse
    {
        return $this->serveFile($attachment, true);
    }

    /**
     * Stream the file from storage with proper headers.
     */
    private function serveFile(Attachment $attachment, bool $forceDownload): StreamedResponse
    {
        $disk = Storage::disk(config('filesystems.default'));
        $path = $attachment->file_path;

        // Check if file exists
        if (!$disk->exists($path)) {
            abort(404, 'File not found.');
        }

        $filename = $attachment->file_name;
        $mimeType = $attachment->file_type ?? $disk->mimeType($path) ?? 'application/octet-stream';
        $disposition = $forceDownload ? 'attachment' : 'inline';

        return $disk->response($path, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . addslashes($filename) . '"',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
