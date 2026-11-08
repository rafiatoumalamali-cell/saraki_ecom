<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.catalogue.categories.index', [
            'categories' => Category::query()
                ->with('parent')
                ->withCount('products')
                ->ordered()
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.catalogue.categories.form', [
            'category' => new Category(['is_active' => true]),
            'parents' => $this->parentOptions(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $category = Category::create([
            ...$data,
            'slug' => Slug::unique($data['slug'] ?? $data['name_fr'], 'categories'),
            'sort_order' => $data['sort_order'] ?? $this->nextSortOrder(),
        ]);

        return redirect()
            ->route('admin.catalogue.categories.index')
            ->with('success', __('admin.categories.created', ['name' => $category->name_fr]));
    }

    public function edit(Category $category): View
    {
        return view('admin.catalogue.categories.form', [
            'category' => $category,
            'parents' => $this->parentOptions($category),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        $category->update([
            ...$data,
            'slug' => Slug::unique($data['slug'] ?? $category->slug, 'categories', $category->id),
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
        ]);

        return redirect()
            ->route('admin.catalogue.categories.index')
            ->with('success', __('admin.categories.updated', ['name' => $category->name_fr]));
    }

    /**
     * Categories stay in the database while products still point at them, so the
     * admin is told to move or delete the products first.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', __('admin.categories.delete_blocked', ['name' => $category->name_fr]));
        }

        $category->delete();

        return redirect()
            ->route('admin.catalogue.categories.index')
            ->with('success', __('admin.categories.deleted', ['name' => $category->name_fr]));
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', $category->is_active
            ? __('admin.categories.activated', ['name' => $category->name_fr])
            : __('admin.categories.deactivated', ['name' => $category->name_fr]));
    }

    /**
     * Swaps the category with its neighbour. The whole list is renumbered each time
     * so the order stays contiguous even when rows were edited by hand.
     */
    public function move(Category $category, string $direction): RedirectResponse
    {
        $order = Category::query()->ordered()->pluck('id')->all();
        $position = array_search($category->id, $order, true);
        $target = $position === false ? false : $position + ($direction === 'up' ? -1 : 1);

        if ($position === false || $target < 0 || $target >= count($order)) {
            return back();
        }

        [$order[$position], $order[$target]] = [$order[$target], $order[$position]];

        DB::transaction(function () use ($order) {
            foreach ($order as $index => $id) {
                Category::query()->whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });

        return back();
    }

    /**
     * @return Collection<int, Category>
     */
    private function parentOptions(?Category $category = null): Collection
    {
        $excluded = $category ? [$category->id, ...$category->descendantIds()] : [];

        return Category::query()
            ->when($excluded !== [], fn ($query) => $query->whereNotIn('id', $excluded))
            ->ordered()
            ->get();
    }

    private function nextSortOrder(): int
    {
        return (int) Category::query()->max('sort_order') + 1;
    }
}
