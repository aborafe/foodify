<?php

use App\Models\Category;
use App\Models\Employee;
use App\Models\Meal;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function searchEmployee(string $role = 'admin'): Employee
{
    return Employee::query()->create([
        'full_name' => "Search {$role}",
        'email' => "search-{$role}@foodify.test",
        'role' => $role,
        'password' => 'password123',
        'is_active' => true,
    ]);
}

it('returns grouped global search preview results', function (): void {
    $employee = searchEmployee();
    $customer = User::factory()->create(['full_name' => 'Global Match Customer']);
    $category = Category::query()->create(['name' => 'Global Match Category', 'is_active' => true]);
    Meal::query()->create([
        'category_id' => $category->id,
        'name' => 'Global Match Bowl',
        'description' => 'Fresh food',
        'price' => 90,
        'nutrition' => [],
        'ingredients' => [],
        'rating' => 4.5,
        'is_recommended' => false,
        'is_available' => true,
    ]);
    Order::query()->create([
        'order_number' => 'GLOBAL-ORDER-001',
        'user_id' => $customer->id,
        'subtotal' => 90,
        'delivery_fee' => 10,
        'total' => 100,
        'payment_status' => 'paid',
        'status' => 'delivered',
        'delivery_address' => 'Global Street',
    ]);
    Notification::query()->create([
        'user_id' => $customer->id,
        'title' => 'Global Match Notice',
        'body' => 'Body',
        'type' => 'system',
    ]);

    $this->actingAs($employee, 'employee')
        ->getJson(route('admin.search.preview', ['q' => 'Global']))
        ->assertOk()
        ->assertJsonFragment(['label' => 'User'])
        ->assertJsonFragment(['label' => 'Order'])
        ->assertJsonFragment(['label' => 'Meal'])
        ->assertJsonFragment(['label' => 'Category'])
        ->assertJsonFragment(['label' => 'Notification']);
});

it('shows full global search result page', function (): void {
    $employee = searchEmployee('cashier');
    User::factory()->create(['full_name' => 'Full Result Customer']);

    $this->actingAs($employee, 'employee')
        ->get(route('admin.search', ['q' => 'Full Result']))
        ->assertOk()
        ->assertSee('Full Result Customer')
        ->assertSee('Search Results');
});
