<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Contracts\OtpServiceInterface;
use App\Contracts\SmsServiceInterface;
use App\Models\Notification;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Services\VonageSmsService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsServiceInterface::class, VonageSmsService::class);
        $this->app->bind(OtpServiceInterface::class, OtpService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin', function ($view): void {
            $notifications = Notification::query()
                ->where('is_admin_visible', true)
                ->whereIn('admin_context', ['order', 'meal'])
                ->latest()
                ->take(6)
                ->get(['id', 'title', 'body', 'type', 'is_read', 'admin_context', 'admin_url', 'created_at']);

            $view->with([
                'headerNotifications' => $notifications,
                'unreadNotificationCount' => Notification::query()
                    ->where('is_admin_visible', true)
                    ->whereIn('admin_context', ['order', 'meal'])
                    ->where('is_read', false)
                    ->count(),
            ]);
        });
    }
}
