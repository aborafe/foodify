<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'categories' => Category::query()
                ->where('is_active', true)
                ->latest()
                ->paginate(20),
        ]);
    }

    public function meals(Category $category): JsonResponse
    {
        abort_unless($category->is_active, 404);

        return response()->json([
            'category' => $category,
            'meals' => $category->meals()
                ->where('is_available', true)
                ->latest()
                ->paginate(20),
        ]);
    }
}
