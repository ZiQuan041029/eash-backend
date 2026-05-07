<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * S3-compatible presigned PUT for direct browser/app uploads to R2 (authenticated).
 */
class PresignedUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (!config('filesystems.disks.r2.bucket') || !config('filesystems.disks.r2.key')) {
            return response()->json([
                'message' => 'R2 is not configured.',
            ], 503);
        }

        $validated = $request->validate([
            'content_type' => ['required', 'string', 'regex:/^image\/(jpeg|png|webp)$/'],
        ]);

        $user = $request->user();
        $ext = match ($validated['content_type']) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $key = sprintf('uploads/user-%d/%s.%s', $user->id, Str::uuid()->toString(), $ext);

        $disk = Storage::disk('r2');

        if (!$disk->providesTemporaryUploadUrls()) {
            return response()->json(['message' => 'Presigned uploads are not available for this disk.'], 503);
        }

        $expires = now()->addMinutes(10);

        $result = $disk->temporaryUploadUrl($key, $expires, [
            'ContentType' => $validated['content_type'],
        ]);

        $headers = [];
        foreach ($result['headers'] ?? [] as $name => $value) {
            $headers[$name] = is_array($value) ? implode(',', $value) : (string) $value;
        }

        $publicBase = rtrim((string) config('filesystems.disks.r2.url'), '/');
        $publicUrl = $publicBase !== '' ? $publicBase.'/'.$key : null;

        return response()->json([
            'key' => $key,
            'upload_url' => $result['url'],
            'headers' => $headers,
            'expires_at' => $expires->toIso8601String(),
            'public_url' => $publicUrl,
        ]);
    }
}
