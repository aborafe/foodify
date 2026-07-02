<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount('meals')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->toString().'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.categories', [
            'categories' => $categories,
            'categoryStats' => [
                'total' => Category::query()->count(),
                'active' => Category::query()->where('is_active', true)->count(),
                'inactive' => Category::query()->where('is_active', false)->count(),
                'withMeals' => Category::query()->has('meals')->count(),
            ],
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.categories');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::query()->create($this->preparedData($request->validated(), $request));

        return redirect()
            ->route('admin.categories')
            ->with('status', 'Category created successfully.');
    }

    public function show(Category $category): RedirectResponse
    {
        return redirect()->route('admin.categories');
    }

    public function edit(Category $category): RedirectResponse
    {
        return redirect()->route('admin.categories');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($this->preparedData($request->validated(), $request, $category));

        return redirect()
            ->route('admin.categories')
            ->with('status', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->meals()->exists()) {
            return redirect()
                ->route('admin.categories')
                ->withErrors(['category' => 'Cannot delete a category that has meals.']);
        }

        if ($category->image !== null) {
            $this->deleteStoredCategoryImage($category->image);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories')
            ->with('status', 'Category deleted successfully.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparedData(array $data, Request $request, ?Category $category = null): array
    {
        unset($data['image']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');

            if ($category?->image !== null) {
                $this->deleteStoredCategoryImage($category->image);
            }

            $data['image'] = Storage::url($path);
        } elseif ($category !== null) {
            $data['image'] = $category->image;
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }

    private function deleteStoredCategoryImage(string $image): void
    {
        $storagePrefix = '/storage/';

        if (! str_starts_with($image, $storagePrefix)) {
            return;
        }

        $path = substr($image, strlen($storagePrefix));

        if (str_starts_with($path, 'categories/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
