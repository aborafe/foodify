<?php

namespace App\Services\Admin;

use App\Models\Meal;
use App\Models\Order;
use App\Models\User;

class DashboardMetricsService
{
    /**
     * @return array<string, float|int>
     */
    public function metrics(): array
    {
        $revenue = (float) Order::query()->sum('total');
        $expenses = 0.0;

        return [
            'totalOrders' => Order::query()->count(),
            'totalRevenue' => $revenue,
            'totalExpenses' => $expenses,
            'netProfit' => $revenue - $expenses,
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'lowStockItems' => 0,
            'availableMeals' => Meal::query()->where('is_available', true)->count(),
        ];
    }
}
