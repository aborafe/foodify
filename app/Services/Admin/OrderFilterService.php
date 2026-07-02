<?php

namespace App\Services\Admin;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderFilterService
{
    /**
     * @return Builder<Order>
     */
    public function query(Request $request): Builder
    {
        return Order::query()
            ->with(['user:id,full_name,phone,email', 'orderItems:id,order_id,meal_id,meal_name,quantity,unit_price,total'])
            ->withCount('orderItems')
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('payment_status', 'like', "%{$search}%")
                        ->orWhere('delivery_address', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $query) use ($search): void {
                            $query->where('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('payment_status'), fn (Builder $query): Builder => $query->where('payment_status', $request->string('payment_status')->toString()))
            ->when($request->filled('customer_id'), fn (Builder $query): Builder => $query->where('user_id', $request->integer('customer_id')))
            ->when($request->filled('date_from'), fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest();
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        return [
            'pending' => Order::query()->where('status', 'pending')->count(),
            'preparing' => Order::query()->where('status', 'preparing')->count(),
            'onTheWay' => Order::query()->where('status', 'on_the_way')->count(),
            'cancelled' => Order::query()->where('status', 'cancelled')->count(),
        ];
    }
}
