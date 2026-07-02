<?php

namespace App\Contracts;

use App\Models\Category;
use App\Models\Meal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CatalogRepositoryInterface
{
    /**
     * @return Collection<int, Category>
     */
    public function homeCategories(): Collection;

    /**
     * @return Collection<int, Meal>
     */
    public function recommendedMeals(): Collection;

    /**
     * @return Collection<int, Meal>
     */
    public function popularMeals(): Collection;

    public function activeCategories(): LengthAwarePaginator;

    public function availableMealsForCategory(Category $category): LengthAwarePaginator;
}
