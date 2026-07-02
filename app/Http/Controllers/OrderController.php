<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success([
            'orders' => OrderResource::collection($this->orderService->list($request->user())),
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        return $this->success([
            'order' => new OrderResource($this->orderService->show($request->user(), $order)),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $order = $this->orderService->cancel($request->user(), $order);

        return $this->success([
            'message' => 'Order cancelled.',
            'order' => new OrderResource($order),
        ]);
    }
}
