<?php

namespace App\Repositories;

use App\Contracts\CatalogRepositoryInterface;
use App\Models\Category;
use App\Models\Meal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentCatalogRepository implements CatalogRepositoryInterface
{
    public function homeCategories(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->latest()
            ->limit(8)
            ->get();
    }

    public function recommendedMeals(): Collection
    {
        return Meal::query()
            ->with('category')
            ->where('is_available', true)
            ->where('is_recommended', true)
            ->latest()
            ->limit(10)
            ->get();
    }

    public function popularMeals(): Collection
    {
        return Meal::query()
            ->with('category')
            ->where('is_available', true)
            ->orderByDesc('rating')
            ->limit(10)
            ->get();
    }

    public function activeCategories(): LengthAwarePaginator
    {
        return Category::query()
            ->where('is_active', true)
            ->latest()
            ->paginate(20);
    }

    public function availableMealsForCategory(Category $category): LengthAwarePaginator
    {
        return $category->meals()
            ->where('is_available', true)
            ->latest()
            ->paginate(20);
    }
}
