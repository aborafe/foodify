<?php

use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminEmployeeForNotifications(): Employee
{
    return Employee::query()->create([
        'full_name' => 'Notification Admin',
        'email' => 'notification-admin@foodify.test',
        'role' => 'admin',
        'password' => 'password123',
        'is_active' => true,
    ]);
}

it('lets admins create update and delete notifications', function (): void {
    $admin = adminEmployeeForNotifications();
    $customer = User::factory()->create();

    $this->actingAs($admin, 'employee')
        ->post(route('admin.notifications.store'), [
            'audience' => 'single',
            'user_id' => $customer->id,
            'title' => 'Dashboard Notification',
            'body' => 'Created from admin dashboard.',
            'type' => 'system',
        ])->assertRedirect(route('admin.notifications'));

    $notification = Notification::query()->where('title', 'Dashboard Notification')->firstOrFail();

    $this->actingAs($admin, 'employee')
        ->put(route('admin.notifications.update', $notification), [
            'user_id' => $customer->id,
            'title' => 'Updated Notification',
            'body' => 'Updated body.',
            'type' => 'offer',
            'is_read' => '1',
        ])->assertRedirect(route('admin.notifications'));

    expect($notification->fresh()->type)->toBe('offer')
        ->and($notification->fresh()->is_read)->toBeTrue();

    $this->actingAs($admin, 'employee')
        ->delete(route('admin.notifications.destroy', $notification))
        ->assertRedirect(route('admin.notifications'));

    $this->assertModelMissing($notification);
});

it('creates notifications for all active customers', function (): void {
    $admin = adminEmployeeForNotifications();
    User::factory()->count(3)->create(['is_active' => true]);
    User::factory()->create(['is_active' => false]);

    $this->actingAs($admin, 'employee')
        ->post(route('admin.notifications.store'), [
            'audience' => 'all',
            'title' => 'Offer for everyone',
            'body' => 'Active customers only.',
            'type' => 'offer',
        ])->assertRedirect(route('admin.notifications'));

    expect(Notification::query()->where('title', 'Offer for everyone')->count())->toBe(3);
});

it('lets cashiers view notifications but not manage them', function (): void {
    $cashier = Employee::query()->create([
        'full_name' => 'Cashier',
        'email' => 'notification-cashier@foodify.test',
        'role' => 'cashier',
        'password' => 'password123',
        'is_active' => true,
    ]);
    $customer = User::factory()->create();
    $notification = Notification::query()->create([
        'user_id' => $customer->id,
        'title' => 'Cashier Visible Notification',
        'body' => 'Visible to cashier.',
        'type' => 'system',
    ]);

    $this->actingAs($cashier, 'employee')
        ->get(route('admin.notifications'))
        ->assertOk()
        ->assertSee('Cashier Visible Notification')
        ->assertDontSee('data-modal-target="#notificationCreateModal"', false);

    $this->actingAs($cashier, 'employee')
        ->delete(route('admin.notifications.destroy', $notification))
        ->assertForbidden();
});

it('shows only order and meal dashboard notifications in the header', function (): void {
    $admin = adminEmployeeForNotifications();
    $customer = User::factory()->create();

    Notification::query()->create([
        'user_id' => $customer->id,
        'title' => 'Manual customer offer',
        'body' => 'This should stay out of the dashboard bell.',
        'type' => 'offer',
        'is_admin_visible' => false,
    ]);

    Notification::query()->create([
        'user_id' => $customer->id,
        'title' => 'New order received',
        'body' => 'Order FDADMIN1001 from customer is waiting for review.',
        'type' => 'order',
        'is_admin_visible' => true,
        'admin_context' => 'order',
        'admin_url' => route('admin.orders', ['search' => 'FDADMIN1001']),
    ]);

    Notification::query()->create([
        'user_id' => null,
        'title' => 'Meal updated',
        'body' => 'Meal Salmon Bowl was updated in the products catalog.',
        'type' => 'system',
        'is_admin_visible' => true,
        'admin_context' => 'meal',
        'admin_url' => route('admin.products', ['search' => 'Salmon Bowl']),
    ]);

    $this->actingAs($admin, 'employee')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('New order received')
        ->assertSee('Meal updated')
        ->assertSee(route('admin.orders', ['search' => 'FDADMIN1001']), false)
        ->assertDontSee('Manual customer offer');
});
