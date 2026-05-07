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
            'visa_upload_id' => ['nullable', 'integer'],
        ]);

        $visa = null;
        if (isset($validated['visa_upload_id'])) {
            $visa = VisaUpload::query()->whereKey($validated['visa_upload_id'])->first();
            if (!$visa) {
                throw ValidationException::withMessages([
                    'visa_upload_id' => ['Invalid visa upload. Please re-upload your visa.'],
                ]);
            }
            if ($visa->user_id !== null) {
                throw ValidationException::withMessages([
                    'visa_upload_id' => ['This visa upload has already been linked to an account.'],
                ]);
            }
        }

        $visaStatus = 'none';
        if ($visa !== null) {
            $visaStatus = match ($visa->status) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                default => 'pending',
            };
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'visa_status' => $visaStatus,
        ]);

        if ($visa !== null) {
            $visa->update([
                'user_id' => $user->id,
            ]);
        }

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
