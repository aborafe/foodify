<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()
            ->cartItems()
            ->with('meal.category')
            ->latest()
            ->get();

        return response()->json([
            'cart_items' => $items,
            'summary' => [
                'subtotal' => round($items->sum(fn (CartItem $item): float => (float) $item->unit_price * $item->quantity), 2),
                'items_count' => $items->sum('quantity'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'meal_id' => ['required', 'integer', 'exists:meals,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $meal = Meal::query()
            ->where('id', $data['meal_id'])
            ->where('is_available', true)
            ->firstOrFail();

        $quantity = $data['quantity'] ?? 1;

        $item = CartItem::query()->firstOrNew([
            'user_id' => $request->user()->id,
            'meal_id' => $meal->id,
        ]);

        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->unit_price = $meal->price;
        $item->save();

        return response()->json([
            'message' => 'Meal added to cart.',
            'cart_item' => $item->load('meal.category'),
        ], 201);
    }

    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartItem->update(['quantity' => $data['quantity']]);

        return response()->json([
            'message' => 'Cart item updated.',
            'cart_item' => $cartItem->load('meal.category'),
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);

        $cartItem->delete();

        return response()->json([
            'message' => 'Cart item removed.',
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->user()->cartItems()->delete();

        return response()->json([
            'message' => 'Cart cleared.',
        ]);
    }
}
