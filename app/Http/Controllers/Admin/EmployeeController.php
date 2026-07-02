<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        return view('admin.employees', [
            'employees' => Employee::query()->latest()->paginate(10),
            'employeeStats' => [
                'total' => Employee::query()->count(),
                'admins' => Employee::query()->where('role', 'admin')->count(),
                'cashiers' => Employee::query()->where('role', 'cashier')->count(),
                'inactive' => Employee::query()->where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.employees.index');
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        Employee::query()->create($data);

        return redirect()
            ->route('admin.employees.index')
            ->with('status', 'Employee created successfully.');
    }

    public function show(Employee $employee): RedirectResponse
    {
        return redirect()->route('admin.employees.index');
    }

    public function edit(Employee $employee): RedirectResponse
    {
        return redirect()->route('admin.employees.index');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $employee->update($data);

        return redirect()
            ->route('admin.employees.index')
            ->with('status', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        if ($employee->isAdmin() && Employee::query()->where('role', 'admin')->where('is_active', true)->count() === 1) {
            return redirect()
                ->route('admin.employees.index')
                ->withErrors(['employee' => 'At least one active admin must remain.']);
        }

        $employee->delete();

        return redirect()
            ->route('admin.employees.index')
            ->with('status', 'Employee deleted successfully.');
    }
}
