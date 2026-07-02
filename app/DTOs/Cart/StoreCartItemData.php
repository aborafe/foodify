<?php

namespace App\DTOs\Cart;

readonly class StoreCartItemData
{
    public function __construct(
        public int $mealId,
        public int $quantity = 1,
    ) {}

    /**
     * @param  array{meal_id: int, quantity?: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mealId: $data['meal_id'],
            quantity: $data['quantity'] ?? 1,
        );
    }
}
