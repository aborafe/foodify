<?php

namespace App\Http\Controllers;

use App\DTOs\PaymentMethod\StorePaymentMethodData;
use App\Http\Requests\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Services\PaymentMethodService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentMethodController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PaymentMethodService $paymentMethodService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success([
            'payment_methods' => PaymentMethodResource::collection($this->paymentMethodService->list($request->user())),
        ]);
    }

    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $paymentMethod = $this->paymentMethodService->create($request->user(), StorePaymentMethodData::fromArray($request->validated()));

        return $this->created([
            'message' => 'Payment method created.',
            'payment_method' => new PaymentMethodResource($paymentMethod),
        ]);
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        Gate::authorize('delete', $paymentMethod);

        $this->paymentMethodService->delete($paymentMethod);

        return $this->success([
            'message' => 'Payment method deleted.',
        ]);
    }
}
