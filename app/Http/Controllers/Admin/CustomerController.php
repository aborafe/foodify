<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = User::query()
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->latest()
            ->paginate(12);

        return view('admin.customers', [
            'customers' => $customers,
            'customerStats' => [
                'total' => User::query()->count(),
                'verified' => User::query()->whereNotNull('phone_verified_at')->count(),
                'repeat' => User::query()->has('orders', '>=', 2)->count(),
                'inactive' => User::query()->where('is_active', false)->count(),
            ],
            'recentCustomers' => User::query()->latest()->take(3)->get(['id', 'full_name', 'phone_verified_at', 'created_at']),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.customers');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        User::query()->create($this->preparedData($request->validated(), true));

        return redirect()
            ->route('admin.customers')
            ->with('status', 'Customer created successfully.');
    }

    public function show(User $customer): View
    {
        $customer->load([
            'orders' => fn ($query) => $query
                ->with(['orderItems:id,order_id,meal_name,quantity,unit_price,total'])
                ->latest(),
        ]);

        $addresses = $customer->orders
            ->pluck('delivery_address')
            ->filter()
            ->unique()
            ->values();

        return view('admin.customer-profile', [
            'customer' => $customer,
            'addresses' => $addresses,
        ]);
    }

    public function edit(User $customer): RedirectResponse
    {
        return redirect()->route('admin.customers');
    }

    public function update(UpdateCustomerRequest $request, User $customer): RedirectResponse
    {
        $customer->update($this->preparedData($request->validated(), false));

        return redirect()
            ->route('admin.customers')
            ->with('status', 'Customer updated successfully.');
    }

    public function destroy(User $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('admin.customers')
            ->with('status', 'Customer deleted successfully.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparedData(array $data, bool $creating): array
    {
        $data['phone_verified_at'] = (bool) ($data['phone_verified_at'] ?? false) ? now() : null;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if (! $creating && blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }
}
