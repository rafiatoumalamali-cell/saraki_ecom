<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.content.pages.index', [
            'pages' => Page::query()->orderBy('slug')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.content.pages.form', [
            'page' => new Page(['is_published' => true]),
        ]);
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $page = Page::create([
            ...$data,
            'slug' => Slug::unique($data['slug'] ?? $data['title_fr'], 'pages'),
        ]);

        return redirect()
            ->route('admin.content.pages.edit', $page->id)
            ->with('success', __('admin.pages.created', ['title' => $page->title_fr]));
    }

    public function edit(Page $page): View
    {
        return view('admin.content.pages.form', ['page' => $page]);
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $data = $request->validated();

        $page->update([
            ...$data,
            'slug' => Slug::unique($data['slug'] ?? $page->slug, 'pages', $page->id),
        ]);

        return redirect()
            ->route('admin.content.pages.edit', $page->id)
            ->with('success', __('admin.pages.updated', ['title' => $page->title_fr]));
    }

    public function destroy(Page $page): RedirectResponse
    {
        // The storefront links to these slugs, so they are editable but never
        // removable — the button is hidden too, this only guards a crafted POST.
        if (in_array($page->slug, Page::FIXED_SLUGS, true)) {
            return redirect()
                ->route('admin.content.pages.index')
                ->with('error', __('admin.pages.fixed_notice'));
        }

        $title = $page->title_fr;

        $page->delete();

        return redirect()
            ->route('admin.content.pages.index')
            ->with('success', __('admin.pages.deleted', ['title' => $title]));
    }
}
