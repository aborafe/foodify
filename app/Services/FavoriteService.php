<?php

namespace App\Services;

use App\Contracts\FavoriteRepositoryInterface;
use App\Contracts\MealRepositoryInterface;
use App\DTOs\Favorite\StoreFavoriteData;
use App\Models\Favorite;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FavoriteService
{
    public function __construct(
        private readonly FavoriteRepositoryInterface $favorites,
        private readonly MealRepositoryInterface $meals,
    ) {}

    public function list(User $user): LengthAwarePaginator
    {
        return $this->favorites->listForUser($user);
    }

    public function add(User $user, StoreFavoriteData $data): Favorite
    {
        $meal = $this->meals->findAvailableOrFail($data->mealId);

        return $this->favorites->firstOrCreate($user, $meal)->load('meal.category');
    }

    public function remove(User $user, Meal $meal): void
    {
        $this->favorites->deleteForMeal($user, $meal);
    }
}
