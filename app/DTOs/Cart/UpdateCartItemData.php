<?php

namespace App\DTOs\Cart;

readonly class UpdateCartItemData
{
    public function __construct(public int $quantity) {}

    /**
     * @param  array{quantity: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(quantity: $data['quantity']);
    }
}
