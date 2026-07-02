<?php

namespace App\Services\Admin;

use App\Contracts\AdminSearchable;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Meal;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    /**
     * @return array<int, class-string<Model&AdminSearchable>>
     */
    private function models(): array
    {
        return [User::class, Order::class, Meal::class, Category::class, Notification::class, Employee::class];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function search(string $term, int $limitPerModel = 5): Collection
    {
        $term = trim($term);

        if ($term === '') {
            return collect();
        }

        return collect($this->models())->map(function (string $model) use ($term, $limitPerModel): array {
            $query = $this->matchingQuery($model, $term);
            $count = (clone $query)->count();
            $titleColumn = $model::adminSearchTitleColumn();

            $items = $query->limit($limitPerModel)->get()->map(function (Model $item) use ($model, $titleColumn): array {
                return [
                    'id' => $item->getKey(),
                    'title' => (string) $item->getAttribute($titleColumn),
                    'url' => $this->urlFor($model, $item),
                ];
            });

            return [
                'label' => class_basename($model),
                'count' => $count,
                'items' => $items,
            ];
        })->filter(fn (array $group): bool => $group['count'] > 0)->values();
    }

    /**
     * @param  class-string<Model&AdminSearchable>  $model
     * @return Builder<Model>
     */
    private function matchingQuery(string $model, string $term): Builder
    {
        return $model::query()
            ->where(function (Builder $query) use ($model, $term): void {
                foreach ($model::adminSearchableColumns() as $column) {
                    $query->orWhere($column, 'like', "%{$term}%");
                }
            })
            ->latest();
    }

    /**
     * @param  class-string<Model&AdminSearchable>  $model
     */
    private function urlFor(string $model, Model $item): string
    {
        if ($model === User::class) {
            return route('admin.customers.show', $item);
        }

        if ($model === Order::class) {
            return route('admin.orders', ['search' => $item->getAttribute('order_number')]);
        }

        return route($model::adminSearchRouteName(), ['search' => $item->getAttribute($model::adminSearchTitleColumn())]);
    }
}
