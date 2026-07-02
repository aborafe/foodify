<?php

namespace App\Services\Admin;

use App\Models\Meal;
use App\Models\Notification;
use App\Models\Order;

class DashboardNotificationService
{
    public function orderCreated(Order $order): void
    {
        $order->loadMissing('user:id,full_name');

        Notification::query()->create([
            'user_id' => $order->user_id,
            'title' => 'New order received',
            'body' => sprintf(
                'Order %s from %s is waiting for review.',
                $order->order_number,
                $order->user?->full_name ?? 'customer'
            ),
            'type' => 'order',
            'is_read' => false,
            'is_admin_visible' => true,
            'admin_context' => 'order',
            'admin_url' => route('admin.orders', ['search' => $order->order_number]),
        ]);
    }

    public function mealCreated(Meal $meal): void
    {
        $this->mealChanged($meal, 'New meal added', 'created');
    }

    public function mealUpdated(Meal $meal): void
    {
        $this->mealChanged($meal, 'Meal updated', 'updated');
    }

    public function mealDeleted(Meal $meal): void
    {
        $this->mealChanged($meal, 'Meal removed', 'removed');
    }

    private function mealChanged(Meal $meal, string $title, string $action): void
    {
        Notification::query()->create([
            'user_id' => null,
            'title' => $title,
            'body' => sprintf('Meal %s was %s in the products catalog.', $meal->name, $action),
            'type' => 'system',
            'is_read' => false,
            'is_admin_visible' => true,
            'admin_context' => 'meal',
            'admin_url' => route('admin.products', ['search' => $meal->name]),
        ]);
    }
}
