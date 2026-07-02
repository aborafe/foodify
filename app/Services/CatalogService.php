<?php

namespace App\Services;

use App\Contracts\CatalogRepositoryInterface;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CatalogService
{
    public function __construct(private readonly CatalogRepositoryInterface $catalog) {}

    /**
     * @return array{categories: Collection, recommended_meals: Collection, popular_meals: Collection}
     */
    public function home(): array
    {
        return [
            'categories' => $this->catalog->homeCategories(),
            'recommended_meals' => $this->catalog->recommendedMeals(),
            'popular_meals' => $this->catalog->popularMeals(),
        ];
    }

    public function categories(): LengthAwarePaginator
    {
        return $this->catalog->activeCategories();
    }

    public function categoryMeals(Category $category): LengthAwarePaginator
    {
        abort_if(! $category->is_active, 404);

        return $this->catalog->availableMealsForCategory($category);
    }
}
