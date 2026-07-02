<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\MealController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SavedReportController;
use App\Http\Controllers\Admin\SearchController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');
Route::redirect('/login', '/admin/login')->name('login');

Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');

Route::middleware(['auth:employee', 'employee.role:admin,cashier'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('search', [SearchController::class, 'index'])->name('search');
    Route::get('search/preview', [SearchController::class, 'preview'])->name('search.preview');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

    Route::resource('orders', OrderController::class)
        ->except(['show', 'create', 'edit'])
        ->middleware('employee.role:admin,cashier')
        ->names([
            'index' => 'orders',
            'store' => 'orders.store',
            'update' => 'orders.update',
            'destroy' => 'orders.destroy',
        ]);

    Route::middleware('employee.role:admin')->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('products', MealController::class)
            ->except(['show', 'create', 'edit'])
            ->parameters(['products' => 'meal'])
            ->names([
                'index' => 'products',
                'store' => 'products.store',
                'update' => 'products.update',
                'destroy' => 'products.destroy',
            ]);
        Route::resource('categories', AdminCategoryController::class)
            ->except(['show', 'create', 'edit'])
            ->parameters(['categories' => 'category'])
            ->names([
                'index' => 'categories',
                'store' => 'categories.store',
                'update' => 'categories.update',
                'destroy' => 'categories.destroy',
            ]);
        Route::resource('customers', CustomerController::class)
            ->except(['show', 'create', 'edit'])
            ->parameters(['customers' => 'customer'])
            ->names([
                'index' => 'customers',
                'store' => 'customers.store',
                'update' => 'customers.update',
                'destroy' => 'customers.destroy',
            ]);
        Route::resource('notifications', NotificationController::class)
            ->except(['index', 'show', 'create', 'edit'])
            ->names([
                'store' => 'notifications.store',
                'update' => 'notifications.update',
                'destroy' => 'notifications.destroy',
            ]);
        Route::resource('reports', SavedReportController::class)
            ->except(['show', 'create', 'edit'])
            ->names([
                'index' => 'reports',
                'store' => 'reports.store',
                'update' => 'reports.update',
                'destroy' => 'reports.destroy',
            ]);
        Route::resource('employees', EmployeeController::class)->except(['show', 'create', 'edit']);
    });
});
