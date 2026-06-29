<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'orders' => $request->user()
                ->orders()
                ->with(['orderItems', 'paymentMethod', 'payment'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return response()->json([
            'order' => $order->load(['orderItems', 'paymentMethod', 'payment']),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        if (! in_array($order->status, ['pending', 'confirmed'], true)) {
            return response()->json([
                'message' => 'This order cannot be cancelled.',
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Order cancelled.',
            'order' => $order->fresh(),
        ]);
    }
}
