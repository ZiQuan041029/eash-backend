<?php

namespace App\Http\Controllers;

use App\Models\VisaUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisaGuestStatusController extends Controller
{
    public function __invoke(Request $request, string $guestId)
    {
        $row = VisaUpload::query()->where('guest_id', $guestId)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        // If approved and token not issued yet, issue one-time approval token to the guest.
        $approvalToken = null;
        if ($row->status === 'approved' && $row->approval_token_hash === null && $row->approval_token_used_at === null) {
            $approvalToken = Str::random(48);
            $row->update([
                'approval_token_hash' => hash('sha256', $approvalToken),
            ]);
        }

        return response()->json([
            'status' => $row->status,
            'approved_at' => $row->approved_at,
            'rejected' => $row->status === 'rejected',
            'approval_token' => $approvalToken,
        ]);
    }
}

