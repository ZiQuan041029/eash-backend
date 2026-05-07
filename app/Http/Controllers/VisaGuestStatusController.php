<?php

namespace App\Http\Controllers;

use App\Models\VisaUpload;
use Illuminate\Http\Request;

class VisaGuestStatusController extends Controller
{
    public function __invoke(Request $request, string $guestId)
    {
        $row = VisaUpload::query()->where('guest_id', $guestId)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json([
            'status' => $row->status,
            'approved_at' => $row->approved_at,
            'rejected' => $row->status === 'rejected',
        ]);
    }
}

