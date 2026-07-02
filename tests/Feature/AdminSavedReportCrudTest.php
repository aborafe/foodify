<?php

use App\Models\Employee;
use App\Models\SavedReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminEmployeeForReports(): Employee
{
    return Employee::query()->create([
        'full_name' => 'Reports Admin',
        'email' => 'reports-admin@foodify.test',
        'role' => 'admin',
        'password' => 'password123',
        'is_active' => true,
    ]);
}

it('lets admins create update and delete saved reports', function (): void {
    $admin = adminEmployeeForReports();

    $this->actingAs($admin, 'employee')
        ->post(route('admin.reports.store'), [
            'name' => 'Weekly Orders Report',
            'metric' => 'orders',
            'date_range' => 'This Week',
            'export_format' => 'pdf',
            'included_sections' => 'Revenue trend, Order status mix',
            'status' => 'active',
        ])->assertRedirect(route('admin.reports'));

    $report = SavedReport::query()->where('name', 'Weekly Orders Report')->firstOrFail();

    expect($report->included_sections)->toBe(['Revenue trend', 'Order status mix']);

    $this->actingAs($admin, 'employee')
        ->put(route('admin.reports.update', $report), [
            'name' => 'Updated Orders Report',
            'metric' => 'sales',
            'date_range' => 'This Month',
            'export_format' => 'xlsx',
            'included_sections' => 'Top selling meals',
            'status' => 'draft',
        ])->assertRedirect(route('admin.reports'));

    expect($report->fresh()->name)->toBe('Updated Orders Report')
        ->and($report->fresh()->metric)->toBe('sales')
        ->and($report->fresh()->status)->toBe('draft');

    $this->actingAs($admin, 'employee')
        ->delete(route('admin.reports.destroy', $report))
        ->assertRedirect(route('admin.reports'));

    $this->assertModelMissing($report);
});

it('prevents cashier from managing reports', function (): void {
    $cashier = Employee::query()->create([
        'full_name' => 'Reports Cashier',
        'email' => 'reports-cashier@foodify.test',
        'role' => 'cashier',
        'password' => 'password123',
        'is_active' => true,
    ]);

    $this->actingAs($cashier, 'employee')
        ->get(route('admin.reports'))
        ->assertForbidden();
});
