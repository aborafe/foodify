<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function success(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json($this->resolveResources($data), $status);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function created(array $data = []): JsonResponse
    {
        return $this->success($data, 201);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function error(string $message, int $status, array $data = []): JsonResponse
    {
        return $this->success(array_merge(['message' => $message], $data), $status);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveResources(array $data): array
    {
        return collect($data)
            ->map(fn (mixed $value): mixed => $this->resolveResourceValue($value))
            ->all();
    }

    private function resolveResourceValue(mixed $value): mixed
    {
        if ($value instanceof ResourceCollection) {
            if ($value->resource instanceof Paginator) {
                return $value->response()->getData(true);
            }

            return $value->resolve(request());
        }

        if ($value instanceof JsonResource) {
            return $value->resolve(request());
        }

        return $value;
    }
}
