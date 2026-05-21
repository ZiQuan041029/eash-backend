<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Authenticated profile avatar upload → R2, updates user.profile_photo_url.
 */
class ProfilePhotoUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (!config('filesystems.disks.r2.bucket') || !config('filesystems.disks.r2.key')) {
            return response()->json([
                'message' => 'R2 is not configured.',
            ], 503);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $uploaded = $request->file('file');
        $ext = strtolower($uploaded->getClientOriginalExtension() ?: 'jpg');
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        if (!in_array($ext, ['jpg', 'png', 'webp'], true)) {
            $ext = 'jpg';
        }

        $user = $request->user();
        $key = sprintf(
            'uploads/user-%d/avatar-%s.%s',
            $user->id,
            Str::uuid()->toString(),
            $ext
        );

        try {
            Storage::disk('r2')->put($key, file_get_contents($uploaded->getRealPath()), [
                'ContentType' => $uploaded->getMimeType() ?: 'image/jpeg',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => config('app.debug') ? $e->getMessage() : 'Upload failed.',
            ], 502);
        }

        $publicBase = rtrim((string) config('filesystems.disks.r2.url'), '/');
        $publicUrl = $publicBase !== '' ? $publicBase.'/'.$key : null;

        $user->update(['profile_photo_url' => $publicUrl]);

        return response()->json([
            'key' => $key,
            'public_url' => $publicUrl,
            'profile_photo_url' => $publicUrl,
        ], 201);
    }
}
