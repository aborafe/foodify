<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminEmployeeForCustomers(): Employee
{
    return Employee::query()->create([
        'full_name' => 'Customer Admin',
        'email' => 'customer-admin@foodify.test',
        'role' => 'admin',
        'password' => 'password123',
        'is_active' => true,
    ]);
}

it('lets admins create update and delete customers from dashboard', function (): void {
    $admin = adminEmployeeForCustomers();

    $this->actingAs($admin, 'employee')
        ->post(route('admin.customers.store'), [
            'full_name' => 'Dashboard Customer',
            'phone' => '+201018919997',
            'email' => 'dashboard-customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'address' => 'Cairo',
            'phone_verified_at' => '1',
            'is_active' => '1',
        ])->assertRedirect(route('admin.customers'));

    $customer = User::query()->where('phone', '+201018919997')->firstOrFail();

    expect($customer->phone_verified_at)->not->toBeNull();

    $this->actingAs($admin, 'employee')
        ->put(route('admin.customers.update', $customer), [
            'full_name' => 'Updated Customer',
            'phone' => '+201018919997',
            'email' => 'updated-customer@example.com',
            'address' => 'Giza',
            'is_active' => '1',
        ])->assertRedirect(route('admin.customers'));

    expect($customer->fresh()->full_name)->toBe('Updated Customer');

    $this->actingAs($admin, 'employee')
        ->delete(route('admin.customers.destroy', $customer))
        ->assertRedirect(route('admin.customers'));

    $this->assertModelMissing($customer);
});

it('prevents cashier from managing customers', function (): void {
    $cashier = Employee::query()->create([
        'full_name' => 'Cashier',
        'email' => 'customer-cashier@foodify.test',
        'role' => 'cashier',
        'password' => 'password123',
        'is_active' => true,
    ]);

    $this->actingAs($cashier, 'employee')
        ->get(route('admin.customers'))
        ->assertForbidden();
});

