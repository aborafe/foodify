<?php

namespace App\Services;

use App\Contracts\NotificationRepositoryInterface;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function __construct(private readonly NotificationRepositoryInterface $notifications) {}

    public function list(User $user): LengthAwarePaginator
    {
        return $this->notifications->listMobileForUser($user);
    }

    public function markAsRead(Notification $notification): Notification
    {
        return $this->notifications->markAsRead($notification);
    }

    public function markAllAsRead(User $user): void
    {
        $this->notifications->markAllMobileForUser($user);
    }
}
