<?php

namespace App\DTOs\Favorite;

readonly class StoreFavoriteData
{
    public function __construct(public int $mealId) {}

    /**
     * @param  array{meal_id: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(mealId: $data['meal_id']);
    }
}
