<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function markAsRead(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id && ! $notification->is_admin_visible;
    }
}
