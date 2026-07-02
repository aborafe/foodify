<?php

use App\Models\Category;
use App\Models\Employee;
use App\Models\Meal;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function cashierEmployee(): Employee
{
    return Employee::query()->create([
        'full_name' => 'Order Cashier',
        'email' => 'cashier@foodify.test',
        'role' => 'cashier',
        'password' => 'password123',
        'is_active' => true,
    ]);
}

function adminEmployeeForOrders(): Employee
{
    return Employee::query()->create([
        'full_name' => 'Order Admin',
        'email' => 'order-admin@foodify.test',
        'role' => 'admin',
        'password' => 'password123',
        'is_active' => true,
    ]);
}

function mealFixture(string $name, float $price): Meal
{
    $category = Category::query()->first() ?? Category::query()->create([
        'name' => 'Bowls',
        'is_active' => true,
    ]);

    return Meal::query()->create([
        'category_id' => $category->id,
        'name' => $name,
        'description' => $name.' description',
        'price' => $price,
        'nutrition' => [],
        'ingredients' => [],
        'rating' => 4.5,
        'is_recommended' => false,
        'is_available' => true,
    ]);
}

it('creates a dashboard order with selected meals as order items', function (): void {
    $cashier = cashierEmployee();
    $customer = User::factory()->create([
        'full_name' => 'Mohamed Customer',
        'phone' => '+201018919997',
        'address' => 'Cairo',
    ]);
    $meal = mealFixture('Grilled Chicken Bowl', 100);

    $this->actingAs($cashier, 'employee')
        ->post(route('admin.orders.store'), [
            'user_id' => $customer->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'delivery_fee' => 30,
            'manual_adjustment' => -10,
            'delivery_address' => 'Cairo',
            'notes' => 'No onions.',
            'estimated_delivery_time' => 45,
            'items' => [
                ['meal_id' => $meal->id, 'quantity' => 2],
            ],
        ])->assertRedirect(route('admin.orders'));

    $order = Order::query()->firstOrFail();

    expect((float) $order->subtotal)->toBe(200.0)
        ->and((float) $order->manual_adjustment)->toBe(-10.0)
        ->and((float) $order->total)->toBe(220.0)
        ->and($order->notes)->toBe('No onions.');

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'meal_id' => $meal->id,
        'meal_name' => 'Grilled Chicken Bowl',
        'quantity' => 2,
    ]);

    $notification = Notification::query()
        ->where('title', 'New order received')
        ->where('admin_context', 'order')
        ->firstOrFail();

    expect($notification->is_admin_visible)->toBeTrue()
        ->and($notification->admin_url)->toContain(route('admin.orders', ['search' => $order->order_number], false));
});

it('shows order items in the dashboard order view markup', function (): void {
    $cashier = cashierEmployee();
    $customer = User::factory()->create(['full_name' => 'Sarah Ahmed', 'phone' => '+201001234567']);
    $meal = mealFixture('Quinoa Salad', 80);
    $order = Order::query()->create([
        'order_number' => 'FDTEST001',
        'user_id' => $customer->id,
        'subtotal' => 80,
        'delivery_fee' => 30,
        'total' => 110,
        'payment_status' => 'paid',
        'status' => 'confirmed',
    ]);

    OrderItem::query()->create([
        'order_id' => $order->id,
        'meal_id' => $meal->id,
        'meal_name' => $meal->name,
        'quantity' => 1,
        'unit_price' => 80,
        'total' => 80,
    ]);

    $this->actingAs($cashier, 'employee')
        ->get(route('admin.orders'))
        ->assertOk()
        ->assertSee('FDTEST001')
        ->assertSee('Quinoa Salad')
        ->assertSee('01001234567');
});

it('updates and deletes dashboard orders', function (): void {
    $cashier = cashierEmployee();
    $customer = User::factory()->create();
    $meal = mealFixture('Protein Smoothie', 50);
    $order = Order::query()->create([
        'order_number' => 'FDTEST002',
        'user_id' => $customer->id,
        'subtotal' => 50,
        'delivery_fee' => 30,
        'total' => 80,
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $this->actingAs($cashier, 'employee')
        ->put(route('admin.orders.update', $order), [
            'user_id' => $customer->id,
            'status' => 'preparing',
            'payment_status' => 'paid',
            'delivery_fee' => 20,
            'manual_adjustment' => 15,
            'delivery_address' => 'Updated address',
            'notes' => 'Call before delivery.',
            'estimated_delivery_time' => 30,
            'items' => [
                ['meal_id' => $meal->id, 'quantity' => 3],
            ],
        ])->assertRedirect(route('admin.orders'));

    expect($order->fresh()->status)->toBe('preparing')
        ->and((float) $order->fresh()->manual_adjustment)->toBe(15.0)
        ->and((float) $order->fresh()->total)->toBe(185.0)
        ->and($order->fresh()->notes)->toBe('Call before delivery.');

    $this->actingAs($cashier, 'employee')
        ->delete(route('admin.orders.destroy', $order))
        ->assertRedirect(route('admin.orders'));

    $this->assertModelMissing($order);
});

it('keeps the original customer when editing a dashboard order', function (): void {
    $cashier = cashierEmployee();
    $oldCustomer = User::factory()->create(['full_name' => 'Old Customer']);
    $newCustomer = User::factory()->create(['full_name' => 'New Customer']);
    $meal = mealFixture('Avocado Toast', 90);
    $order = Order::query()->create([
        'order_number' => 'FDTEST003',
        'user_id' => $oldCustomer->id,
        'subtotal' => 90,
        'delivery_fee' => 30,
        'total' => 120,
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $this->actingAs($cashier, 'employee')
        ->put(route('admin.orders.update', $order), [
            'user_id' => $newCustomer->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'delivery_fee' => 30,
            'delivery_address' => 'New customer address',
            'notes' => 'Changed customer.',
            'estimated_delivery_time' => 35,
            'items' => [
                ['meal_id' => $meal->id, 'quantity' => 1],
            ],
        ])->assertRedirect(route('admin.orders'));

    expect($order->fresh()->user_id)->toBe($oldCustomer->id);
});

it('prevents cashier from setting protected delivery statuses', function (string $status): void {
    $cashier = cashierEmployee();
    $customer = User::factory()->create();
    $meal = mealFixture('Protected Status Meal', 75);
    $order = Order::query()->create([
        'order_number' => 'FDTEST004'.$status,
        'user_id' => $customer->id,
        'subtotal' => 75,
        'delivery_fee' => 30,
        'total' => 105,
        'payment_status' => 'pending',
        'status' => 'preparing',
    ]);

    $this->actingAs($cashier, 'employee')
        ->put(route('admin.orders.update', $order), [
            'user_id' => $customer->id,
            'status' => $status,
            'payment_status' => 'paid',
            'delivery_fee' => 30,
            'delivery_address' => 'Protected address',
            'estimated_delivery_time' => 35,
            'items' => [
                ['meal_id' => $meal->id, 'quantity' => 1],
            ],
        ])->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe('preparing');
})->with(['on_the_way', 'cancelled']);

it('lets cashiers mark dashboard orders as delivered', function (): void {
    $cashier = cashierEmployee();
    $customer = User::factory()->create();
    $meal = mealFixture('Delivered Status Meal', 75);
    $order = Order::query()->create([
        'order_number' => 'FDTEST004DELIVERED',
        'user_id' => $customer->id,
        'subtotal' => 75,
        'delivery_fee' => 30,
        'total' => 105,
        'payment_status' => 'pending',
        'status' => 'preparing',
    ]);

    $this->actingAs($cashier, 'employee')
        ->put(route('admin.orders.update', $order), [
            'user_id' => $customer->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'delivery_fee' => 30,
            'delivery_address' => 'Delivered address',
            'estimated_delivery_time' => 35,
            'items' => [
                ['meal_id' => $meal->id, 'quantity' => 1],
            ],
        ])->assertRedirect(route('admin.orders'));

    expect($order->fresh()->status)->toBe('delivered');
});

it('lets admins set protected delivery statuses', function (): void {
    $admin = adminEmployeeForOrders();
    $customer = User::factory()->create();
    $meal = mealFixture('Admin Status Meal', 75);
    $order = Order::query()->create([
        'order_number' => 'FDTEST005',
        'user_id' => $customer->id,
        'subtotal' => 75,
        'delivery_fee' => 30,
        'total' => 105,
        'payment_status' => 'pending',
        'status' => 'preparing',
    ]);

    $this->actingAs($admin, 'employee')
        ->put(route('admin.orders.update', $order), [
            'user_id' => $customer->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'delivery_fee' => 30,
            'delivery_address' => 'Admin address',
            'estimated_delivery_time' => 35,
            'items' => [
                ['meal_id' => $meal->id, 'quantity' => 1],
            ],
        ])->assertRedirect(route('admin.orders'));

    expect($order->fresh()->status)->toBe('delivered');
});

it('filters dashboard orders and exports the same filtered pdf', function (): void {
    $admin = adminEmployeeForOrders();
    $customer = User::factory()->create(['full_name' => 'Filter Customer']);

    Order::query()->create([
        'order_number' => 'FDFILTER001',
        'user_id' => $customer->id,
        'subtotal' => 100,
        'delivery_fee' => 20,
        'total' => 120,
        'payment_status' => 'paid',
        'status' => 'delivered',
    ]);
    Order::query()->create([
        'order_number' => 'FDFILTER002',
        'user_id' => $customer->id,
        'subtotal' => 80,
        'delivery_fee' => 20,
        'total' => 100,
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $this->actingAs($admin, 'employee')
        ->get(route('admin.orders', ['status' => 'delivered', 'payment_status' => 'paid', 'search' => 'FDFILTER']))
        ->assertOk()
        ->assertSee('FDFILTER001')
        ->assertDontSee('FDFILTER002');

    $exportResponse = $this->actingAs($admin, 'employee')
        ->get(route('admin.orders.export', ['status' => 'delivered', 'payment_status' => 'paid', 'search' => 'FDFILTER']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($exportResponse->getContent())->toStartWith('%PDF');
});

it('shows printable invoice data with manual adjustment', function (): void {
    $cashier = cashierEmployee();
    $customer = User::factory()->create(['full_name' => 'Invoice Customer']);
    $meal = mealFixture('Invoice Bowl', 100);
    $order = Order::query()->create([
        'order_number' => 'FDINVOICE001',
        'user_id' => $customer->id,
        'subtotal' => 200,
        'delivery_fee' => 30,
        'manual_adjustment' => -15,
        'total' => 215,
        'payment_status' => 'paid',
        'status' => 'delivered',
    ]);
    OrderItem::query()->create([
        'order_id' => $order->id,
        'meal_id' => $meal->id,
        'meal_name' => $meal->name,
        'quantity' => 2,
        'unit_price' => 100,
        'total' => 200,
    ]);

    $this->actingAs($cashier, 'employee')
        ->get(route('admin.orders.invoice', $order))
        ->assertOk()
        ->assertSee('FDINVOICE001')
        ->assertSee('Invoice Customer')
        ->assertSee('Invoice Bowl')
        ->assertSee('-$15.00')
        ->assertSee('$215.00');
});
