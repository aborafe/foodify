<?php

namespace App\Repositories;

use App\Contracts\ProfileRepositoryInterface;
use App\DTOs\Profile\UpdateProfileData;
use App\Models\User;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
    public function update(User $user, UpdateProfileData $data): User
    {
        $user->update($data->attributes);
        $user->refresh();

        return $user;
    }
}
