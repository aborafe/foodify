<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Contracts\CartRepositoryInterface;
use App\Contracts\CatalogRepositoryInterface;
use App\Contracts\FavoriteRepositoryInterface;
use App\Contracts\MealRepositoryInterface;
use App\Contracts\NotificationRepositoryInterface;
use App\Contracts\OrderRepositoryInterface;
use App\Contracts\OtpServiceInterface;
use App\Contracts\PaymentMethodRepositoryInterface;
use App\Contracts\ProfileRepositoryInterface;
use App\Contracts\SmsServiceInterface;
use App\Models\CartItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Policies\CartItemPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentMethodPolicy;
use App\Repositories\EloquentCartRepository;
use App\Repositories\EloquentCatalogRepository;
use App\Repositories\EloquentFavoriteRepository;
use App\Repositories\EloquentMealRepository;
use App\Repositories\EloquentNotificationRepository;
use App\Repositories\EloquentOrderRepository;
use App\Repositories\EloquentPaymentMethodRepository;
use App\Repositories\EloquentProfileRepository;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Services\VonageSmsService;
use Illuminate\Support\Facades\Gate;
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
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(CatalogRepositoryInterface::class, EloquentCatalogRepository::class);
        $this->app->bind(MealRepositoryInterface::class, EloquentMealRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, EloquentNotificationRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(PaymentMethodRepositoryInterface::class, EloquentPaymentMethodRepository::class);
        $this->app->bind(FavoriteRepositoryInterface::class, EloquentFavoriteRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, EloquentProfileRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(CartItem::class, CartItemPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(PaymentMethod::class, PaymentMethodPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);

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
