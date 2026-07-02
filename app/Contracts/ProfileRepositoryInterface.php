<?php

namespace App\Contracts;

use App\DTOs\Profile\UpdateProfileData;
use App\Models\User;

interface ProfileRepositoryInterface
{
    public function update(User $user, UpdateProfileData $data): User;
}
