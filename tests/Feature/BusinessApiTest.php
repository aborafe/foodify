<?php

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function verifiedUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'phone_verified_at' => now(),
        'is_active' => true,
    ], $attributes));
}

function availableMeal(array $attributes = []): Meal
{
    $category = Category::query()->create([
        'name' => 'Bowls',
        'is_active' => true,
    ]);

    return Meal::query()->create(array_merge([
        'category_id' => $category->id,
        'name' => 'Tuna Bowl',
        'description' => 'Fresh bowl',
        'price' => 120,
        'nutrition' => ['calories' => 320],
        'ingredients' => ['tuna', 'rice'],
        'rating' => 4.5,
        'is_recommended' => true,
        'is_available' => true,
    ], $attributes));
}

it('returns home, categories, category meals, and meal details', function (): void {
    $meal = availableMeal();

    $this->getJson('/api/home')
        ->assertOk()
        ->assertJsonStructure(['categories', 'recommended_meals', 'popular_meals']);

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonPath('categories.data.0.name', 'Bowls');

    $this->getJson("/api/categories/{$meal->category_id}/meals")
        ->assertOk()
        ->assertJsonPath('meals.data.0.name', 'Tuna Bowl');

    $this->getJson("/api/meals/{$meal->id}")
        ->assertOk()
        ->assertJsonPath('meal.name', 'Tuna Bowl');
});

it('manages favorites for the authenticated customer', function (): void {
    $user = verifiedUser();
    $meal = availableMeal();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/favorites', ['meal_id' => $meal->id])
        ->assertCreated()
        ->assertJsonPath('favorite.meal_id', $meal->id);

    $this->assertDatabaseHas('favorites', [
        'user_id' => $user->id,
        'meal_id' => $meal->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/favorites')
        ->assertOk()
        ->assertJsonPath('favorites.data.0.meal_id', $meal->id);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/favorites/{$meal->id}")
        ->assertOk();

    $this->assertDatabaseMissing('favorites', [
        'user_id' => $user->id,
        'meal_id' => $meal->id,
    ]);
});

it('manages cart items and creates an order during checkout', function (): void {
    $user = verifiedUser(['address' => 'Cairo']);
    $meal = availableMeal(['price' => 100]);
    $paymentMethod = PaymentMethod::query()->create([
        'user_id' => $user->id,
        'type' => 'cash_on_delivery',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/cart', [
            'meal_id' => $meal->id,
            'quantity' => 2,
        ])
        ->assertCreated()
        ->assertJsonPath('cart_item.quantity', 2);

    $cartItem = CartItem::query()->where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/cart/{$cartItem->id}", ['quantity' => 3])
        ->assertOk()
        ->assertJsonPath('cart_item.quantity', 3);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/checkout', [
            'payment_method_id' => $paymentMethod->id,
            'delivery_fee' => 30,
            'delivery_address' => 'Giza',
        ])
        ->assertCreated()
        ->assertJsonPath('order.subtotal', '300.00')
        ->assertJsonPath('order.total', '330.00')
        ->assertJsonCount(1, 'order.order_items');

    $this->assertDatabaseCount('cart_items', 0);
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);
    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'amount' => 330,
        'status' => 'pending',
    ]);
});

it('lists and cancels customer orders', function (): void {
    $user = verifiedUser();
    $order = Order::query()->create([
        'order_number' => 'FDTEST1001',
        'user_id' => $user->id,
        'subtotal' => 100,
        'delivery_fee' => 30,
        'total' => 130,
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/orders')
        ->assertOk()
        ->assertJsonPath('orders.data.0.order_number', 'FDTEST1001');

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/orders/{$order->id}/cancel")
        ->assertOk()
        ->assertJsonPath('order.status', 'cancelled');
});

it('updates profile and marks notifications as read', function (): void {
    $user = verifiedUser();
    $notification = Notification::query()->create([
        'user_id' => $user->id,
        'title' => 'Order update',
        'body' => 'Your order is pending.',
        'type' => 'order',
        'is_read' => false,
    ]);

    $this->actingAs($user, 'sanctum')
        ->patchJson('/api/profile', ['full_name' => 'Updated Name'])
        ->assertOk()
        ->assertJsonPath('user.full_name', 'Updated Name');

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/notifications')
        ->assertOk()
        ->assertJsonPath('notifications.data.0.title', 'Order update');

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/notifications/{$notification->id}/read")
        ->assertOk()
        ->assertJsonPath('notification.is_read', true);
});

