<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\Admin\DashboardNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private readonly DashboardNotificationService $dashboardNotifications) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery_time' => ['nullable', 'integer', 'min:1'],
        ]);

        $user = $request->user();

        if (! empty($data['payment_method_id'])) {
            PaymentMethod::query()
                ->where('id', $data['payment_method_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        $cartItems = $user->cartItems()->with('meal')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty.',
            ], 422);
        }

        $order = DB::transaction(function () use ($user, $data, $cartItems): Order {
            $subtotal = round($cartItems->sum(fn ($item): float => (float) $item->unit_price * $item->quantity), 2);
            $deliveryFee = (float) ($data['delivery_fee'] ?? 30.00);
            $total = round($subtotal + $deliveryFee, 2);

            $order = $user->orders()->create([
                'order_number' => $this->generateOrderNumber(),
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'payment_status' => 'pending',
                'status' => 'pending',
                'delivery_address' => $data['delivery_address'] ?? $user->address,
                'estimated_delivery_time' => $data['estimated_delivery_time'] ?? null,
            ]);

            foreach ($cartItems as $cartItem) {
                $order->orderItems()->create([
                    'meal_id' => $cartItem->meal_id,
                    'meal_name' => $cartItem->meal?->name ?? 'Meal',
                    'meal_image' => $cartItem->meal?->image,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'total' => round((float) $cartItem->unit_price * $cartItem->quantity, 2),
                ]);
            }

            $order->payment()->create([
                'user_id' => $user->id,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'amount' => $total,
                'status' => 'pending',
            ]);

            $user->cartItems()->delete();

            return $order->load(['orderItems', 'payment', 'paymentMethod']);
        });

        $this->dashboardNotifications->orderCreated($order);

        return response()->json([
            'message' => 'Order placed successfully.',
            'order' => $order,
        ], 201);
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'FD'.now()->format('YmdHis').random_int(1000, 9999);
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
