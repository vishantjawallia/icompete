<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::middleware('api.guest')->group(function () {
    Route::post('register', [RegisterController::class, 'store'])->name('register');
    Route::post('organizer/register', [RegisterController::class, 'organizerRegistration'])->name('organizer.register');

    Route::post('login', [LoginController::class, 'store']);
    // Forgot password
    Route::post('password/forgot', [PasswordController::class, 'forgotPassword']);
    Route::post('password/resend', [PasswordController::class, 'resendCode']);
    Route::post('password/confirm', [PasswordController::class, 'confirmCode']);
    Route::post('password/reset', [PasswordController::class, 'resetPassword']);
});
Route::middleware('auth:sanctum')->group(function () {
    // Email verification
    Route::prefix('email')->controller(VerifyController::class)->group(function () {
        Route::post('verify', 'verifyCode');
        Route::post('resend', 'resend');
    });
    // Logout
    Route::post('logout', [LoginController::class, 'destroy']);
    // refresh token
    Route::post('/refresh', [LoginController::class, 'refresh']);
});
Route::get('/user', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'status'  => 'success',
        'message' => 'User retrieved successfully',
        'data'    => apiUserObject($user),
    ], 200);
})->middleware('auth:sanctum');
