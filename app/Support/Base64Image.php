<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Decode a raw base64 image (no data-URI), validate mime, store on the public disk. */
final class Base64Image
{
    private const MAX_DECODED_BYTES = 2_500_000;

    private const ALLOWED_MIME_TO_EXT = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /**
     * @return non-empty-string|null Public URL for the stored file, or null on failure.
     */
    public static function storeFromRawBase64(
        string $base64,
        string $directory,
        string $filePrefix,
    ): ?string {
        $raw = preg_replace('/\s+/', '', $base64) ?? '';
        if ($raw === '') {
            return null;
        }

        $binary = base64_decode($raw, true);
        if ($binary === false || strlen($binary) > self::MAX_DECODED_BYTES) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($binary);
        if (! is_string($mime) || ! isset(self::ALLOWED_MIME_TO_EXT[$mime])) {
            return null;
        }

        $ext = self::ALLOWED_MIME_TO_EXT[$mime];
        $name = Str::slug($filePrefix).'_'.time().'_'.Str::random(6).'.'.$ext;
        $relativePath = trim($directory, '/').'/'.$name;

        Storage::disk('public')->put($relativePath, $binary);

        return Storage::disk('public')->url($relativePath);
    }
}
