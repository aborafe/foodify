<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMealRequest;
use App\Http\Requests\Admin\UpdateMealRequest;
use App\Models\Category;
use App\Models\Meal;
use App\Services\Admin\DashboardNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MealController extends Controller
{
    public function __construct(private readonly DashboardNotificationService $dashboardNotifications) {}

    public function index(Request $request): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $meals = Meal::query()
            ->with('category:id,name')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = (string) $request->string('search');

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('availability'), fn ($query) => $query->where('is_available', (string) $request->string('availability') === 'available'))
            ->when($request->filled('recommendation'), fn ($query) => $query->where('is_recommended', (string) $request->string('recommendation') === 'recommended'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.products', [
            'meals' => $meals,
            'categories' => $categories,
            'mealStats' => [
                'total' => Meal::query()->count(),
                'categories' => Category::query()->where('is_active', true)->count(),
                'unavailable' => Meal::query()->where('is_available', false)->count(),
                'catalogValue' => Meal::query()->sum('price'),
                'recommended' => Meal::query()->where('is_recommended', true)->count(),
                'averageRating' => Meal::query()->avg('rating') ?? 0,
            ],
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.products');
    }

    public function store(StoreMealRequest $request): RedirectResponse
    {
        $meal = Meal::query()->create($this->preparedData($request->validated(), $request));

        $this->dashboardNotifications->mealCreated($meal);

        return redirect()
            ->route('admin.products')
            ->with('status', 'Meal created successfully.');
    }

    public function show(Meal $meal): RedirectResponse
    {
        return redirect()->route('admin.products');
    }

    public function edit(Meal $meal): RedirectResponse
    {
        return redirect()->route('admin.products');
    }

    public function update(UpdateMealRequest $request, Meal $meal): RedirectResponse
    {
        $meal->update($this->preparedData($request->validated(), $request, $meal));

        $this->dashboardNotifications->mealUpdated($meal->fresh());

        return redirect()
            ->route('admin.products')
            ->with('status', 'Meal updated successfully.');
    }

    public function destroy(Meal $meal): RedirectResponse
    {
        $notificationMeal = $meal->replicate();
        $notificationMeal->name = $meal->name;

        $meal->delete();

        $this->dashboardNotifications->mealDeleted($notificationMeal);

        return redirect()
            ->route('admin.products')
            ->with('status', 'Meal deleted successfully.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparedData(array $data, Request $request, ?Meal $meal = null): array
    {
        unset($data['image']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('meals', 'public');

            if ($meal?->image !== null) {
                $this->deleteStoredMealImage($meal->image);
            }

            $data['image'] = Storage::url($path);
        } elseif ($meal !== null) {
            $data['image'] = $meal->image;
        }

        $data['nutrition'] = filled($data['nutrition'] ?? null) ? json_decode($data['nutrition'], true) : null;
        $data['ingredients'] = filled($data['ingredients'] ?? null) ? json_decode($data['ingredients'], true) : null;
        $data['is_recommended'] = (bool) ($data['is_recommended'] ?? false);
        $data['is_available'] = (bool) ($data['is_available'] ?? false);
        $data['rating'] = $data['rating'] ?? 0;

        return $data;
    }

    private function deleteStoredMealImage(string $image): void
    {
        $storagePrefix = '/storage/';

        if (! str_starts_with($image, $storagePrefix)) {
            return;
        }

        $path = substr($image, strlen($storagePrefix));

        if (str_starts_with($path, 'meals/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
