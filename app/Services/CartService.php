<?php

namespace App\Services;

use App\Contracts\CartRepositoryInterface;
use App\Contracts\MealRepositoryInterface;
use App\DTOs\Cart\StoreCartItemData;
use App\DTOs\Cart\UpdateCartItemData;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class CartService
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly MealRepositoryInterface $mealRepository,
    ) {}

    /**
     * @return array{cart_items: Collection<int, CartItem>, summary: array{subtotal: float, items_count: int}}
     */
    public function list(User $user): array
    {
        $items = $this->cartRepository->forUser($user);

        return [
            'cart_items' => $items,
            'summary' => [
                'subtotal' => round($items->sum(fn (CartItem $item): float => (float) $item->unit_price * $item->quantity), 2),
                'items_count' => (int) $items->sum('quantity'),
            ],
        ];
    }

    /**
     */
    public function add(User $user, StoreCartItemData $data): CartItem
    {
        $meal = $this->mealRepository->findAvailableOrFail($data->mealId);

        $item = $this->cartRepository->firstOrNew($user, $meal);

        $item->quantity = ($item->exists ? $item->quantity : 0) + $data->quantity;
        $item->unit_price = $meal->price;
        $this->cartRepository->save($item);

        return $item->load('meal.category');
    }

    public function update(User $user, CartItem $cartItem, UpdateCartItemData $data): CartItem
    {
        $this->ensureOwnedBy($cartItem, $user);

        $cartItem->update(['quantity' => $data->quantity]);

        return $cartItem->load('meal.category');
    }

    public function remove(User $user, CartItem $cartItem): void
    {
        $this->ensureOwnedBy($cartItem, $user);

        $this->cartRepository->delete($cartItem);
    }

    public function clear(User $user): void
    {
        $this->cartRepository->clear($user);
    }

    private function ensureOwnedBy(CartItem $cartItem, User $user): void
    {
        if (Gate::forUser($user)->denies('update', $cartItem)) {
            throw (new ModelNotFoundException)->setModel(CartItem::class, [$cartItem->id]);
        }
    }
}
