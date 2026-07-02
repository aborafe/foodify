<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success([
            'notifications' => NotificationResource::collection($this->notificationService->list($request->user())),
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        Gate::authorize('markAsRead', $notification);

        $notification = $this->notificationService->markAsRead($notification);

        return $this->success([
            'message' => 'Notification marked as read.',
            'notification' => new NotificationResource($notification),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return $this->success([
            'message' => 'All notifications marked as read.',
        ]);
    }
}
