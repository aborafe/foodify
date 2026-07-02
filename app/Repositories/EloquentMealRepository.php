<?php

namespace App\Repositories;

use App\Contracts\MealRepositoryInterface;
use App\Models\Meal;

class EloquentMealRepository implements MealRepositoryInterface
{
    public function findAvailableOrFail(int $mealId): Meal
    {
        return Meal::query()
            ->where('id', $mealId)
            ->where('is_available', true)
            ->firstOrFail();
    }
}
