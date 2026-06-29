<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProfileController;
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

Route::get('home', HomeController::class);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}/meals', [CategoryController::class, 'meals']);
Route::get('meals/{meal}', [MealController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::patch('profile', [ProfileController::class, 'update']);

    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites', [FavoriteController::class, 'store']);
    Route::delete('favorites/{meal}', [FavoriteController::class, 'destroy']);

    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::patch('cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('cart/{cartItem}', [CartController::class, 'destroy']);
    Route::delete('cart', [CartController::class, 'clear']);

    Route::get('payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('payment-methods', [PaymentMethodController::class, 'store']);
    Route::delete('payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy']);

    Route::post('checkout', CheckoutController::class);

    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
});
