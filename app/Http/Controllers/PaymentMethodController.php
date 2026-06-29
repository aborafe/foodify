<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'payment_methods' => $request->user()
                ->paymentMethods()
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['card', 'wallet', 'net_banking', 'cash_on_delivery'])],
            'card_brand' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'last_four' => ['nullable', 'string', 'max:4'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($data['is_default'] ?? false) {
            $request->user()->paymentMethods()->update(['is_default' => false]);
        }

        $paymentMethod = $request->user()->paymentMethods()->create($data);

        return response()->json([
            'message' => 'Payment method created.',
            'payment_method' => $paymentMethod,
        ], 201);
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        abort_unless($paymentMethod->user_id === $request->user()->id, 404);

        $paymentMethod->delete();

        return response()->json([
            'message' => 'Payment method deleted.',
        ]);
    }
}
