<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use App\Services\Admin\DashboardNotificationService;
use App\Services\Admin\OrderExportService;
use App\Services\Admin\OrderFilterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(
        private OrderFilterService $orderFilter,
        private OrderExportService $orderExport,
        private DashboardNotificationService $dashboardNotifications,
    ) {}

    public function index(Request $request): View
    {
        $orders = $this->orderFilter->query($request)->paginate(12)->withQueryString();

        return view('admin.orders', [
            'orders' => $orders,
            'orderStats' => $this->orderFilter->stats(),
            'customers' => $this->customersForCombobox(),
            'meals' => Meal::query()
                ->where('is_available', true)
                ->orderBy('name')
                ->get(['id', 'name', 'price', 'image']),
        ]);
    }

    public function export(Request $request): Response
    {
        return $this->orderExport->pdf($this->orderFilter->query($request));
    }

    public function invoice(Order $order): View
    {
        $order->load(['user', 'orderItems', 'paymentMethod', 'payment']);

        return view('admin.orders-invoice', ['order' => $order]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.orders');
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = new Order();

        $this->persistOrder($order, $request->validated());
        $this->dashboardNotifications->orderCreated($order);

        return redirect()
            ->route('admin.orders')
            ->with('status', 'Order created successfully.');
    }

    public function show(Order $order): RedirectResponse
    {
        return redirect()->route('admin.orders');
    }

    public function edit(Order $order): RedirectResponse
    {
        return redirect()->route('admin.orders');
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $this->persistOrder($order, $request->validated());

        return redirect()
            ->route('admin.orders')
            ->with('status', 'Order updated successfully.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()
            ->route('admin.orders')
            ->with('status', 'Order deleted successfully.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function persistOrder(Order $order, array $data): void
    {
        DB::transaction(function () use ($order, $data): void {
            $mealIds = collect($data['items'])->pluck('meal_id')->unique()->values();
            $meals = Meal::query()->whereIn('id', $mealIds)->get()->keyBy('id');
            $subtotal = collect($data['items'])->sum(function (array $item) use ($meals): float {
                $meal = $meals->get((int) $item['meal_id']);

                return round((float) $meal->price * (int) $item['quantity'], 2);
            });
            $deliveryFee = round((float) $data['delivery_fee'], 2);
            $manualAdjustment = round((float) ($data['manual_adjustment'] ?? 0), 2);

            $order->fill([
                'order_number' => $order->exists ? $order->order_number : 'FD'.now()->format('YmdHis').Str::upper(Str::random(4)),
                'user_id' => $order->exists ? $order->user_id : $data['user_id'],
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'manual_adjustment' => $manualAdjustment,
                'total' => $subtotal + $deliveryFee + $manualAdjustment,
                'payment_status' => $data['payment_status'],
                'status' => $data['status'],
                'delivery_address' => $data['delivery_address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'estimated_delivery_time' => $data['estimated_delivery_time'] ?? null,
            ])->save();

            $order->orderItems()->delete();

            foreach ($data['items'] as $item) {
                $meal = $meals->get((int) $item['meal_id']);
                $quantity = (int) $item['quantity'];

                $order->orderItems()->create([
                    'meal_id' => $meal->id,
                    'meal_name' => $meal->name,
                    'meal_image' => $meal->image,
                    'quantity' => $quantity,
                    'unit_price' => $meal->price,
                    'total' => round((float) $meal->price * $quantity, 2),
                ]);
            }
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function customersForCombobox()
    {
        return User::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone', 'email', 'address']);
    }
}
