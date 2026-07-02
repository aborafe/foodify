<?php

namespace App\Contracts;

use App\Models\Favorite;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FavoriteRepositoryInterface
{
    public function listForUser(User $user): LengthAwarePaginator;

    public function firstOrCreate(User $user, Meal $meal): Favorite;

    public function deleteForMeal(User $user, Meal $meal): int;
}
