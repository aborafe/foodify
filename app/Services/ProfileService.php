<?php

namespace App\Services;

use App\Contracts\ProfileRepositoryInterface;
use App\DTOs\Profile\UpdateProfileData;
use App\Models\User;

class ProfileService
{
    public function __construct(private readonly ProfileRepositoryInterface $profiles) {}

    public function update(User $user, UpdateProfileData $data): User
    {
        return $this->profiles->update($user, $data);
    }
}
