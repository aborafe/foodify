<?php

namespace App\DTOs\Checkout;

readonly class CheckoutData
{
    public function __construct(
        public ?int $paymentMethodId = null,
        public ?string $deliveryAddress = null,
        public ?int $estimatedDeliveryTime = null,
    ) {}

    /**
     * @param  array{payment_method_id?: int|null, delivery_address?: string|null, estimated_delivery_time?: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            paymentMethodId: $data['payment_method_id'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            estimatedDeliveryTime: $data['estimated_delivery_time'] ?? null,
        );
    }
}
