<?php

use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function profileEmployee(string $role): Employee
{
    return Employee::query()->create([
        'full_name' => "Profile {$role}",
        'email' => "profile-{$role}@foodify.test",
        'role' => $role,
        'password' => 'password123',
        'is_active' => true,
    ]);
}

it('allows admins and cashiers to open a customer profile', function (string $role): void {
    $employee = profileEmployee($role);
    $customer = User::factory()->create([
        'full_name' => 'Customer Profile User',
        'phone' => '+201111111111',
        'address' => 'Nasr City',
    ]);

    Order::query()->create([
        'order_number' => 'FDPROFILE001',
        'user_id' => $customer->id,
        'subtotal' => 100,
        'delivery_fee' => 20,
        'total' => 120,
        'payment_status' => 'paid',
        'status' => 'delivered',
        'delivery_address' => 'Nasr City',
        'notes' => 'Leave at door.',
    ]);

    $this->actingAs($employee, 'employee')
        ->get(route('admin.customers.show', $customer))
        ->assertOk()
        ->assertSee('Customer Profile User')
        ->assertSee('+201111111111')
        ->assertSee('FDPROFILE001')
        ->assertSee('Leave at door.');
})->with(['admin', 'cashier']);

it('shows duplicate delivery addresses only once on the customer profile', function (): void {
    $employee = profileEmployee('cashier');
    $customer = User::factory()->create(['address' => 'Main Address']);

    foreach (['FDPROFILE002', 'FDPROFILE003'] as $orderNumber) {
        Order::query()->create([
            'order_number' => $orderNumber,
            'user_id' => $customer->id,
            'subtotal' => 80,
            'delivery_fee' => 20,
            'total' => 100,
            'payment_status' => 'pending',
            'status' => 'pending',
            'delivery_address' => 'Repeated Address',
        ]);
    }

    $response = $this->actingAs($employee, 'employee')
        ->get(route('admin.customers.show', $customer))
        ->assertOk();

    expect(substr_count($response->getContent(), 'Repeated Address'))->toBe(1);
});
