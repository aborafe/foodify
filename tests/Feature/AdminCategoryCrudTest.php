<?php

use App\Models\Category;
use App\Models\Employee;
use App\Models\Meal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function adminEmployeeForCategories(): Employee
{
    return Employee::query()->create([
        'full_name' => 'Category Admin',
        'email' => 'category-admin@foodify.test',
        'role' => 'admin',
        'password' => 'password123',
        'is_active' => true,
    ]);
}

it('lets admins create update and delete categories', function (): void {
    Storage::fake('public');

    $admin = adminEmployeeForCategories();

    $this->actingAs($admin, 'employee')
        ->post(route('admin.categories.store'), [
            'name' => 'Healthy Bowls',
            'image' => UploadedFile::fake()->image('bowls.webp'),
            'is_active' => '1',
        ])->assertRedirect(route('admin.categories'));

    $category = Category::query()->where('name', 'Healthy Bowls')->firstOrFail();
    $storedImagePath = substr((string) $category->image, strlen('/storage/'));

    expect($category->image)->toStartWith('/storage/categories/')
        ->and($category->is_active)->toBeTrue();

    Storage::disk('public')->assertExists($storedImagePath);

    $this->actingAs($admin, 'employee')
        ->put(route('admin.categories.update', $category), [
            'name' => 'Protein Bowls',
        ])->assertRedirect(route('admin.categories'));

    expect($category->fresh()->name)->toBe('Protein Bowls')
        ->and($category->fresh()->image)->toBe('/storage/'.$storedImagePath)
        ->and($category->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin, 'employee')
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories'));

    $this->assertModelMissing($category);
    Storage::disk('public')->assertMissing($storedImagePath);
});

it('prevents deleting categories that have meals', function (): void {
    $admin = adminEmployeeForCategories();
    $category = Category::query()->create(['name' => 'Meals Category', 'is_active' => true]);

    Meal::query()->create([
        'category_id' => $category->id,
        'name' => 'Linked Meal',
        'price' => 100,
        'is_available' => true,
    ]);

    $this->actingAs($admin, 'employee')
        ->delete(route('admin.categories.destroy', $category))
        ->assertSessionHasErrors('category');

    $this->assertModelExists($category);
});

it('prevents cashiers from managing categories', function (): void {
    $cashier = Employee::query()->create([
        'full_name' => 'Category Cashier',
        'email' => 'category-cashier@foodify.test',
        'role' => 'cashier',
        'password' => 'password123',
        'is_active' => true,
    ]);

    $this->actingAs($cashier, 'employee')
        ->get(route('admin.categories'))
        ->assertForbidden();
});

it('filters categories by search and status', function (): void {
    $admin = adminEmployeeForCategories();

    Category::query()->create(['name' => 'Green Bowls', 'is_active' => true]);
    Category::query()->create(['name' => 'Archived Drinks', 'is_active' => false]);

    $this->actingAs($admin, 'employee')
        ->get(route('admin.categories', ['search' => 'Green', 'status' => 'active']))
        ->assertOk()
        ->assertSee('Green Bowls')
        ->assertDontSee('Archived Drinks');
});
