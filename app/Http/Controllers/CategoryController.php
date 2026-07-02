<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\MealResource;
use App\Models\Category;
use App\Services\CatalogService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CatalogService $catalogService) {}

    public function index(): JsonResponse
    {
        return $this->success([
            'categories' => CategoryResource::collection($this->catalogService->categories()),
        ]);
    }

    public function meals(Category $category): JsonResponse
    {
        return $this->success([
            'category' => new CategoryResource($category),
            'meals' => MealResource::collection($this->catalogService->categoryMeals($category)),
        ]);
    }
}
