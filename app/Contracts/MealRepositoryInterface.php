<?php

namespace App\Contracts;

use App\Models\Meal;

interface MealRepositoryInterface
{
    public function findAvailableOrFail(int $mealId): Meal;
}
