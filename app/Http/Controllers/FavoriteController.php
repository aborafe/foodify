<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'favorites' => $request->user()
                ->favorites()
                ->with('meal.category')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'meal_id' => ['required', 'integer', 'exists:meals,id'],
        ]);

        $meal = Meal::query()
            ->where('id', $data['meal_id'])
            ->where('is_available', true)
            ->firstOrFail();

        $favorite = Favorite::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'meal_id' => $meal->id,
        ]);

        return response()->json([
            'message' => 'Meal added to favorites.',
            'favorite' => $favorite->load('meal.category'),
        ], 201);
    }

    public function destroy(Request $request, Meal $meal): JsonResponse
    {
        $request->user()
            ->favorites()
            ->where('meal_id', $meal->id)
            ->delete();

        return response()->json([
            'message' => 'Meal removed from favorites.',
        ]);
    }
}
