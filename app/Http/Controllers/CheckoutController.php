<?php

namespace App\Http\Controllers;

use App\DTOs\Checkout\CheckoutData;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Services\Admin\DashboardNotificationService;
use App\Services\CheckoutService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly DashboardNotificationService $dashboardNotifications,
    ) {}

    public function __invoke(CheckoutRequest $request): JsonResponse
    {
        $order = $this->checkoutService->placeOrder($request->user(), CheckoutData::fromArray($request->validated()));
        $this->dashboardNotifications->orderCreated($order);

        return $this->created([
            'message' => 'Order placed successfully.',
            'order' => new OrderResource($order),
        ]);
    }
}
