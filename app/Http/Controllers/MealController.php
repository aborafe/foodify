<?php

namespace App\Http\Controllers;

use App\Http\Resources\MealResource;
use App\Models\Meal;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class MealController extends Controller
{
    use ApiResponse;

    public function show(Meal $meal): JsonResponse
    {
        abort_if(! $meal->is_available, 404);

        return $this->success([
            'meal' => new MealResource($meal->load('category')),
        ]);
    }
}
