<?php

namespace App\Repositories;

use App\Contracts\NotificationRepositoryInterface;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function listMobileForUser(User $user): LengthAwarePaginator
    {
        return $user
            ->notifications()
            ->where('is_admin_visible', false)
            ->latest()
            ->paginate(20);
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->update(['is_read' => true]);
        $notification->refresh();

        return $notification;
    }

    public function markAllMobileForUser(User $user): int
    {
        return $user
            ->notifications()
            ->where('is_admin_visible', false)
            ->update(['is_read' => true]);
    }
}
