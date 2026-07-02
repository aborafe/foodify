<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Services\Admin\DashboardMetricsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetricsService $metricsService) {}

    public function __invoke(): View
    {
        return view('welcome', [
            'dashboardMetrics' => $this->metricsService->metrics(),
            'recentOrders' => Order::query()
                ->with(['user:id,full_name,phone'])
                ->withCount('orderItems')
                ->latest()
                ->take(5)
                ->get(),
            'recentNotifications' => Notification::query()->latest()->take(5)->get(),
        ]);
    }
}
