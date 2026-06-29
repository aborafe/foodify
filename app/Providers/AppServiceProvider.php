<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Contracts\OtpServiceInterface;
use App\Contracts\SmsServiceInterface;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Services\VonageSmsService;
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
        //
    }
}
