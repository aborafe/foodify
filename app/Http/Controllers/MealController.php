<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\JsonResponse;

class MealController extends Controller
{
    public function show(Meal $meal): JsonResponse
    {
        abort_unless($meal->is_available, 404);

        return response()->json([
            'meal' => $meal->load('category'),
        ]);
    }
}
