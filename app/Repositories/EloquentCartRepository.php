<?php

namespace App\Repositories;

use App\Contracts\CartRepositoryInterface;
use App\Models\CartItem;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EloquentCartRepository implements CartRepositoryInterface
{
    public function forUser(User $user): Collection
    {
        return $user
            ->cartItems()
            ->with('meal.category')
            ->latest()
            ->get();
    }

    public function firstOrNew(User $user, Meal $meal): CartItem
    {
        return CartItem::query()->firstOrNew([
            'user_id' => $user->id,
            'meal_id' => $meal->id,
        ]);
    }

    public function save(CartItem $cartItem): bool
    {
        return $cartItem->save();
    }

    public function delete(CartItem $cartItem): ?bool
    {
        return $cartItem->delete();
    }

    public function clear(User $user): int
    {
        return $user->cartItems()->delete();
    }
}
