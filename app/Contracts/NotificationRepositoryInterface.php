<?php

namespace App\Contracts;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function listMobileForUser(User $user): LengthAwarePaginator;

    public function markAsRead(Notification $notification): Notification;

    public function markAllMobileForUser(User $user): int;
}
