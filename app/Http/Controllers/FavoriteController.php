<?php

namespace App\Http\Controllers;

use App\DTOs\Favorite\StoreFavoriteData;
use App\Http\Requests\Favorite\StoreFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Models\Meal;
use App\Services\FavoriteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly FavoriteService $favoriteService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success([
            'favorites' => FavoriteResource::collection($this->favoriteService->list($request->user())),
        ]);
    }

    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        $favorite = $this->favoriteService->add($request->user(), StoreFavoriteData::fromArray($request->validated()));

        return $this->created([
            'message' => 'Meal added to favorites.',
            'favorite' => new FavoriteResource($favorite),
        ]);
    }

    public function destroy(Request $request, Meal $meal): JsonResponse
    {
        $this->favoriteService->remove($request->user(), $meal);

        return $this->success([
            'message' => 'Meal removed from favorites.',
        ]);
    }
}
