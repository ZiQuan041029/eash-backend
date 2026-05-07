<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use App\Models\VisaUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Multipart upload for onboarding visa image → R2 (guest, rate-limited).
 * Replace or gate behind auth when you harden uploads.
 */
class VisaGuestUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (!config('filesystems.disks.r2.bucket') || !config('filesystems.disks.r2.key')) {
            return response()->json([
                'message' => 'R2 is not configured. Set R2_ACCESS_KEY_ID, R2_SECRET_ACCESS_KEY, R2_BUCKET, R2_ENDPOINT in .env',
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

        $prefix = 'visa/guest/'.now()->format('Y/m');
        $key = $prefix.'/'.Str::uuid()->toString().'.'.$ext;
        $guestId = (string) Str::uuid();

        try {
            Storage::disk('r2')->put($key, file_get_contents($uploaded->getRealPath()), [
                'ContentType' => $uploaded->getMimeType() ?: 'image/jpeg',
            ]);

            $row = VisaUpload::create([
                'guest_id' => $guestId,
                'r2_key' => $key,
                'status' => 'pending',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => config('app.debug') ? $e->getMessage() : 'Upload failed.',
            ], 502);
        }

        $publicBase = rtrim((string) config('filesystems.disks.r2.url'), '/');
        $publicUrl = $publicBase !== '' ? $publicBase.'/'.$key : null;

        return response()->json([
            'upload_id' => $row->id,
            'guest_id' => $guestId,
            'key' => $key,
            'public_url' => $publicUrl,
        ], 201);
    }
}
