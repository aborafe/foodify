<?php

namespace App\Repositories;

use App\Contracts\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function listForUser(User $user): LengthAwarePaginator
    {
        return $user
            ->orders()
            ->with(['orderItems', 'paymentMethod', 'payment'])
            ->latest()
            ->paginate(20);
    }

    public function createForUser(User $user, array $attributes): Order
    {
        return $user->orders()->create($attributes);
    }

    public function orderNumberExists(string $orderNumber): bool
    {
        return Order::query()->where('order_number', $orderNumber)->exists();
    }
}
