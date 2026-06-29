<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'categories' => Category::query()
                ->where('is_active', true)
                ->latest()
                ->limit(8)
                ->get(),
            'recommended_meals' => Meal::query()
                ->with('category')
                ->where('is_available', true)
                ->where('is_recommended', true)
                ->latest()
                ->limit(10)
                ->get(),
            'popular_meals' => Meal::query()
                ->with('category')
                ->where('is_available', true)
                ->orderByDesc('rating')
                ->limit(10)
                ->get(),
        ]);
    }
}
