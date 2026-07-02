<?php

namespace App\Services;

use App\Contracts\CartRepositoryInterface;
use App\Contracts\OrderRepositoryInterface;
use App\Contracts\PaymentMethodRepositoryInterface;
use App\DTOs\Checkout\CheckoutData;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    private const DEFAULT_DELIVERY_FEE = 30.00;

    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {}

    public function placeOrder(User $user, CheckoutData $data): Order
    {
        $this->ensurePaymentMethodBelongsToUser($user, $data->paymentMethodId);

        $cartItems = $this->cartRepository->forUser($user);

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['Cart is empty.'],
            ]);
        }

        return DB::transaction(function () use ($user, $data, $cartItems): Order {
            $subtotal = round($cartItems->sum(fn ($item): float => (float) $item->unit_price * $item->quantity), 2);
            $deliveryFee = $this->deliveryFeeFor($user, $data);
            $total = round($subtotal + $deliveryFee, 2);

            $order = $this->orderRepository->createForUser($user, [
                'order_number' => $this->generateOrderNumber(),
                'payment_method_id' => $data->paymentMethodId,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'payment_status' => 'pending',
                'status' => 'pending',
                'delivery_address' => $data->deliveryAddress ?? $user->address,
                'estimated_delivery_time' => $data->estimatedDeliveryTime,
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
                'payment_method_id' => $data->paymentMethodId,
                'amount' => $total,
                'status' => 'pending',
            ]);

            $this->cartRepository->clear($user);

            return $order->load(['orderItems', 'payment', 'paymentMethod']);
        });
    }

    private function deliveryFeeFor(User $user, CheckoutData $data): float
    {
        return self::DEFAULT_DELIVERY_FEE;
    }

    private function ensurePaymentMethodBelongsToUser(User $user, ?int $paymentMethodId): void
    {
        if ($paymentMethodId === null) {
            return;
        }

        if (! $this->paymentMethodRepository->belongsToUser($paymentMethodId, $user)) {
            throw (new ModelNotFoundException)->setModel(PaymentMethod::class, [$paymentMethodId]);
        }
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'FD'.now()->format('YmdHis').random_int(1000, 9999);
        } while ($this->orderRepository->orderNumberExists($number));

        return $number;
    }
}
