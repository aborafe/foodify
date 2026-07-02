<?php

namespace App\Services;

use App\Contracts\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly OrderRepositoryInterface $orderRepository) {}

    public function list(User $user): LengthAwarePaginator
    {
        return $this->orderRepository->listForUser($user);
    }

    public function show(User $user, Order $order): Order
    {
        $this->ensureOwnedBy($order, $user);

        return $order->load(['orderItems', 'paymentMethod', 'payment']);
    }

    public function cancel(User $user, Order $order): Order
    {
        $this->ensureOwnedBy($order, $user);

        if (! in_array($order->status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'order' => ['This order cannot be cancelled.'],
            ]);
        }

        $order->update(['status' => 'cancelled']);
        $order->refresh();

        return $order;
    }

    private function ensureOwnedBy(Order $order, User $user): void
    {
        if (Gate::forUser($user)->denies('view', $order)) {
            throw (new ModelNotFoundException)->setModel(Order::class, [$order->id]);
        }
    }
}
