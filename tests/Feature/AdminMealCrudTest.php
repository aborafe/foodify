<?php

use App\Models\Category;
use App\Models\Employee;
use App\Models\Meal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminEmployeeForMeals(): Employee
{
    return Employee::query()->create([
        'full_name' => 'Admin',
        'email' => 'meal-admin@foodify.test',
        'role' => 'admin',
        'password' => 'password123',
        'is_active' => true,
    ]);
}

it('lets admins create update and delete meals from dashboard', function (): void {
    $admin = adminEmployeeForMeals();
    $category = Category::query()->create(['name' => 'Bowls', 'is_active' => true]);

    $this->actingAs($admin, 'employee')
        ->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Admin Salmon Bowl',
            'description' => 'Fresh salmon bowl',
            'price' => 150,
            'nutrition' => '{"protein":"35g"}',
            'ingredients' => '["salmon","rice"]',
            'rating' => 4.7,
            'is_recommended' => '1',
            'is_available' => '1',
        ])->assertRedirect(route('admin.products'));

    $meal = Meal::query()->where('name', 'Admin Salmon Bowl')->firstOrFail();

    expect($meal->nutrition)->toBe(['protein' => '35g'])
        ->and($meal->ingredients)->toBe(['salmon', 'rice']);

    $this->actingAs($admin, 'employee')
        ->put(route('admin.products.update', $meal), [
            'category_id' => $category->id,
            'name' => 'Updated Salmon Bowl',
            'price' => 175,
            'nutrition' => '{"protein":"40g"}',
            'ingredients' => '["salmon","quinoa"]',
            'rating' => 4.9,
            'is_available' => '1',
        ])->assertRedirect(route('admin.products'));

    expect($meal->fresh()->name)->toBe('Updated Salmon Bowl');

    $this->actingAs($admin, 'employee')
        ->delete(route('admin.products.destroy', $meal))
        ->assertRedirect(route('admin.products'));

    $this->assertModelMissing($meal);
});

it('prevents cashier from managing meals', function (): void {
    $cashier = Employee::query()->create([
        'full_name' => 'Cashier',
        'email' => 'meal-cashier@foodify.test',
        'role' => 'cashier',
        'password' => 'password123',
        'is_active' => true,
    ]);

    $this->actingAs($cashier, 'employee')
        ->get(route('admin.products'))
        ->assertForbidden();
});

it('filters meals by search category availability and recommendation', function (): void {
    $admin = adminEmployeeForMeals();
    $bowls = Category::query()->create(['name' => 'Bowls', 'is_active' => true]);
    $drinks = Category::query()->create(['name' => 'Drinks', 'is_active' => true]);

    Meal::query()->create([
        'category_id' => $bowls->id,
        'name' => 'Green Protein Bowl',
        'description' => 'High protein meal',
        'price' => 120,
        'is_available' => true,
        'is_recommended' => true,
    ]);

    Meal::query()->create([
        'category_id' => $drinks->id,
        'name' => 'Orange Detox Juice',
        'description' => 'Fresh juice',
        'price' => 60,
        'is_available' => false,
        'is_recommended' => false,
    ]);

    $this->actingAs($admin, 'employee')
        ->get(route('admin.products', [
            'search' => 'protein',
            'category_id' => $bowls->id,
            'availability' => 'available',
            'recommendation' => 'recommended',
        ]))
        ->assertOk()
        ->assertSee('Green Protein Bowl')
        ->assertDontSee('Orange Detox Juice');
});
