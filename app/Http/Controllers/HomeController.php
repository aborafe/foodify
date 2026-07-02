<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\MealResource;
use App\Services\CatalogService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CatalogService $catalogService) {}

    public function __invoke(): JsonResponse
    {
        $home = $this->catalogService->home();

        return $this->success([
            'categories' => CategoryResource::collection($home['categories']),
            'recommended_meals' => MealResource::collection($home['recommended_meals']),
            'popular_meals' => MealResource::collection($home['popular_meals']),
        ]);
    }
}
