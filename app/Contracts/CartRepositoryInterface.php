<?php

namespace App\Contracts;

use App\Models\CartItem;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface CartRepositoryInterface
{
    /**
     * @return Collection<int, CartItem>
     */
    public function forUser(User $user): Collection;

    public function firstOrNew(User $user, Meal $meal): CartItem;

    public function save(CartItem $cartItem): bool;

    public function delete(CartItem $cartItem): ?bool;

    public function clear(User $user): int;
}
