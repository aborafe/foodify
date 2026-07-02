<?php

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs an admin employee into the dashboard', function (): void {
    Employee::query()->create([
        'full_name' => 'Dashboard Admin',
        'email' => 'admin@foodify.test',
        'role' => 'admin',
        'password' => 'password123',
        'is_active' => true,
    ]);

    $this->post(route('admin.login.store'), [
        'email' => 'admin@foodify.test',
        'password' => 'password123',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticated('employee');
});

it('allows admins to manage employee accounts', function (): void {
    $admin = Employee::query()->create([
        'full_name' => 'Dashboard Admin',
        'email' => 'admin@foodify.test',
        'role' => 'admin',
        'password' => 'password123',
        'is_active' => true,
    ]);

    $this->actingAs($admin, 'employee')
        ->post(route('admin.employees.store'), [
            'full_name' => 'Order Cashier',
            'email' => 'cashier@foodify.test',
            'phone' => '+201000000001',
            'role' => 'cashier',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => '1',
        ])->assertRedirect(route('admin.employees.index'));

    $this->assertDatabaseHas('employees', [
        'email' => 'cashier@foodify.test',
        'role' => 'cashier',
        'is_active' => true,
    ]);
});

it('limits cashier employees to order management', function (): void {
    $cashier = Employee::query()->create([
        'full_name' => 'Order Cashier',
        'email' => 'cashier@foodify.test',
        'role' => 'cashier',
        'password' => 'password123',
        'is_active' => true,
    ]);

    $this->post(route('admin.login.store'), [
        'email' => 'cashier@foodify.test',
        'password' => 'password123',
    ])->assertRedirect(route('admin.orders'));

    $this->actingAs($cashier, 'employee')
        ->get(route('admin.orders'))
        ->assertOk();

    $this->actingAs($cashier, 'employee')
        ->get(route('admin.products'))
        ->assertForbidden();

    $this->actingAs($cashier, 'employee')
        ->get(route('admin.employees.index'))
        ->assertForbidden();
});

