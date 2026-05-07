<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisaUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VisaVerificationController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $status = $validated['status'] ?? 'pending';

        return VisaUpload::query()
            ->with(['user:id,email,name,visa_status'])
            ->where('status', $status)
            ->orderByDesc('id')
            ->paginate(50);
    }

    public function show(Request $request, VisaUpload $visaUpload)
    {
        $expiresInSeconds = 60 * 10;
        $signedUrl = Storage::disk('r2')->temporaryUrl(
            $visaUpload->r2_key,
            now()->addSeconds($expiresInSeconds)
        );

        return response()->json([
            'id' => $visaUpload->id,
            'status' => $visaUpload->status,
            'r2_key' => $visaUpload->r2_key,
            'user' => $visaUpload->user,
            'admin_note' => $visaUpload->admin_note,
            'signed_url' => $signedUrl,
            'expires_in' => $expiresInSeconds,
        ]);
    }

    public function approve(Request $request, VisaUpload $visaUpload)
    {
        $visaUpload->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approval_token_hash' => null,
            'approval_token_used_at' => null,
            'admin_note' => $request->input('admin_note'),
        ]);

        if ($visaUpload->user) {
            $visaUpload->user->update(['visa_status' => 'approved']);
        }

        return response()->json(['ok' => true]);
    }

    public function reject(Request $request, VisaUpload $visaUpload)
    {
        $visaUpload->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approval_token_hash' => null,
            'approval_token_used_at' => null,
            'admin_note' => $request->input('admin_note'),
        ]);

        if ($visaUpload->user) {
            $visaUpload->user->update(['visa_status' => 'rejected']);
        }

        return response()->json(['ok' => true]);
    }
}

