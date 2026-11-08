<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\GalleryCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryItemRequest;
use App\Models\GalleryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $category = GalleryCategory::tryFrom((string) $request->query('category'));

        return view('admin.content.gallery.index', [
            'items' => GalleryItem::query()
                ->when($category !== null, fn ($query) => $query->where('category', $category))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(24)
                ->withQueryString(),
            'category' => $category,
            'categories' => GalleryCategory::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.content.gallery.form', [
            'item' => new GalleryItem(['is_published' => true, 'category' => GalleryCategory::Collection]),
            'categories' => GalleryCategory::cases(),
        ]);
    }

    public function store(GalleryItemRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $item = GalleryItem::create([
            ...collect($data)->except('image')->all(),
            'image_path' => $request->file('image')->store('content/gallery', 'public'),
        ]);

        return redirect()
            ->route('admin.content.gallery.index')
            ->with('success', __('admin.gallery.created', ['name' => $item->title_fr]));
    }

    public function edit(GalleryItem $gallery): View
    {
        return view('admin.content.gallery.form', [
            'item' => $gallery,
            'categories' => GalleryCategory::cases(),
        ]);
    }

    public function update(GalleryItemRequest $request, GalleryItem $gallery): RedirectResponse
    {
        $gallery->update(collect($request->validated())->except('image')->all());

        if ($request->hasFile('image')) {
            $gallery->storeImage($request->file('image'));
        }

        return redirect()
            ->route('admin.content.gallery.edit', $gallery)
            ->with('success', __('admin.gallery.updated', ['name' => $gallery->title_fr]));
    }

    /**
     * Hiding a piece is the common case, so it does not require opening the form.
     */
    public function toggle(GalleryItem $gallery): RedirectResponse
    {
        $gallery->update(['is_published' => ! $gallery->is_published]);

        return back()->with('success', __(
            $gallery->is_published ? 'admin.gallery.activated' : 'admin.gallery.deactivated',
            ['name' => $gallery->title_fr],
        ));
    }

    public function destroy(GalleryItem $gallery): RedirectResponse
    {
        $name = $gallery->title_fr;

        // The row goes first: if the file cannot be removed the database still
        // holds no reference to an image nobody can see.
        $gallery->delete();
        $gallery->deleteFile();

        return redirect()
            ->route('admin.content.gallery.index')
            ->with('success', __('admin.gallery.deleted', ['name' => $name]));
    }
}
