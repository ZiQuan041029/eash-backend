<?php

namespace App\Http\Controllers;

use App\Models\VisaUpload;
use Illuminate\Http\Request;

/**
 * Authenticated: student visa verification state (bluetick / pending / rejected).
 */
class CurrentUserVisaController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $upload = VisaUpload::query()
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        return response()->json([
            'visa_status' => $user->visa_status,
            'upload_status' => $upload?->status,
            'verified' => $user->visa_status === 'approved',
        ]);
    }
}
