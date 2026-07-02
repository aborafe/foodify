<?php

use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows dashboard metrics from database values', function (): void {
    $admin = Employee::query()->create([
        'full_name' => 'Metrics Admin',
        'email' => 'metrics-admin@foodify.test',
        'role' => 'admin',
        'password' => 'password123',
        'is_active' => true,
    ]);
    $customer = User::factory()->create(['is_active' => true]);

    Order::query()->create([
        'order_number' => 'FDMETRIC001',
        'user_id' => $customer->id,
        'subtotal' => 100,
        'delivery_fee' => 20,
        'total' => 120,
        'payment_status' => 'paid',
        'status' => 'delivered',
    ]);
    Order::query()->create([
        'order_number' => 'FDMETRIC002',
        'user_id' => $customer->id,
        'subtotal' => 50,
        'delivery_fee' => 10,
        'total' => 60,
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $this->actingAs($admin, 'employee')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('2')
        ->assertSee('$180.00')
        ->assertSee('FDMETRIC002');
});
