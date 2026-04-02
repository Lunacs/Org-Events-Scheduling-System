<?php

namespace App\Support;

use Illuminate\Support\Str;

final class AttachmentMimeType
{
    /**
     * @var array<string, string>
     */
    private const EXTENSION_MAP = [
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public static function resolve(string $filename, ?string $preferredMime, ?string $fallbackMime = null): string
    {
        $preferred = $preferredMime !== null && $preferredMime !== ''
            ? Str::lower(trim($preferredMime))
            : null;

        if ($preferred !== null && $preferred !== 'application/octet-stream') {
            return $preferred;
        }

        $ext = Str::lower(pathinfo($filename, PATHINFO_EXTENSION));
        if (isset(self::EXTENSION_MAP[$ext])) {
            return self::EXTENSION_MAP[$ext];
        }

        $fallback = $fallbackMime !== null && $fallbackMime !== ''
            ? Str::lower(trim($fallbackMime))
            : null;

        if ($fallback !== null && $fallback !== 'application/octet-stream') {
            return $fallback;
        }

        return 'application/octet-stream';
    }
}
