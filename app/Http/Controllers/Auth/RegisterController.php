<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VisaUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'visa_approval_token' => ['required', 'string', 'min:20'],
        ]);

        $visa = VisaUpload::query()
            ->where('approval_token_hash', hash('sha256', $validated['visa_approval_token']))
            ->first();

        if (!$visa) {
            throw ValidationException::withMessages([
                'visa_approval_token' => ['Invalid visa approval token.'],
            ]);
        }
        if ($visa->status !== 'approved') {
            throw ValidationException::withMessages([
                'visa_approval_token' => ['Visa has not been approved yet.'],
            ]);
        }
        if ($visa->approval_token_used_at !== null) {
            throw ValidationException::withMessages([
                'visa_approval_token' => ['This visa approval token has already been used.'],
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'visa_status' => 'approved',
        ]);

        $visa->update([
            'user_id' => $user->id,
            'approval_token_used_at' => now(),
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json(
            [
                'user' => $user,
                'token' => $token,
            ],
            201
        );
    }
}
