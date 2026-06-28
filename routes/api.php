<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify-register-otp', [AuthController::class, 'verifyRegisterOtp']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('verify-forgot-password-otp', [AuthController::class, 'verifyForgotPasswordOtp']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});
