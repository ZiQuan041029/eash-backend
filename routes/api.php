<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\VisaVerificationController;
use App\Http\Controllers\VisaGuestStatusController;
use App\Http\Controllers\Upload\PresignedUploadController;
use App\Http\Controllers\Upload\VisaGuestUploadController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', fn () => ['ok' => true]);

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class)->middleware('throttle:60,1');

Route::post('/upload/visa', VisaGuestUploadController::class)->middleware('throttle:60,1');
Route::post('/upload/presign', PresignedUploadController::class)->middleware(['auth:sanctum', 'throttle:30,1']);

Route::get('/visa/status/{guestId}', VisaGuestStatusController::class)->middleware('throttle:120,1');

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/visa-uploads', [VisaVerificationController::class, 'index']);
    Route::get('/visa-uploads/{visaUpload}', [VisaVerificationController::class, 'show']);
    Route::post('/visa-uploads/{visaUpload}/approve', [VisaVerificationController::class, 'approve']);
    Route::post('/visa-uploads/{visaUpload}/reject', [VisaVerificationController::class, 'reject']);
});
