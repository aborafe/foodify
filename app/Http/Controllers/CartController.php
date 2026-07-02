<?php

namespace App\Http\Controllers;

use App\DTOs\Cart\StoreCartItemData;
use App\DTOs\Cart\UpdateCartItemData;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Services\CartService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->list($request->user());

        return $this->success([
            'cart_items' => CartItemResource::collection($cart['cart_items']),
            'summary' => $cart['summary'],
        ]);
    }

    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $item = $this->cartService->add($request->user(), StoreCartItemData::fromArray($request->validated()));

        return $this->created([
            'message' => 'Meal added to cart.',
            'cart_item' => new CartItemResource($item),
        ]);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $cartItem = $this->cartService->update($request->user(), $cartItem, UpdateCartItemData::fromArray($request->validated()));

        return $this->success([
            'message' => 'Cart item updated.',
            'cart_item' => new CartItemResource($cartItem),
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->cartService->remove($request->user(), $cartItem);

        return $this->success([
            'message' => 'Cart item removed.',
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clear($request->user());

        return $this->success([
            'message' => 'Cart cleared.',
        ]);
    }
}
