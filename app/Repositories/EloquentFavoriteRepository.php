<?php

namespace App\Repositories;

use App\Contracts\FavoriteRepositoryInterface;
use App\Models\Favorite;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentFavoriteRepository implements FavoriteRepositoryInterface
{
    public function listForUser(User $user): LengthAwarePaginator
    {
        return $user
            ->favorites()
            ->with('meal.category')
            ->latest()
            ->paginate(20);
    }

    public function firstOrCreate(User $user, Meal $meal): Favorite
    {
        return Favorite::query()->firstOrCreate([
            'user_id' => $user->id,
            'meal_id' => $meal->id,
        ]);
    }

    public function deleteForMeal(User $user, Meal $meal): int
    {
        return $user
            ->favorites()
            ->where('meal_id', $meal->id)
            ->delete();
    }
}
