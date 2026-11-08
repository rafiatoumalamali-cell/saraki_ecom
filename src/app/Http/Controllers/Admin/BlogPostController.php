<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostRequest;
use App\Models\BlogPost;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = PostStatus::tryFrom((string) $request->query('status'));

        return view('admin.content.posts.index', [
            'posts' => BlogPost::query()
                ->with('author:id,name')
                ->when($status !== null, fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                    ->where('title_fr', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")))
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString(),
            'statuses' => PostStatus::cases(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.content.posts.form', [
            'post' => new BlogPost(['status' => PostStatus::Draft]),
            'statuses' => PostStatus::cases(),
        ]);
    }

    public function store(BlogPostRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $post = new BlogPost([
            ...collect($data)->except(['cover', 'status', 'slug'])->all(),
            'author_id' => $request->user()->id,
            'slug' => Slug::unique($data['slug'] ?? $data['title_fr'], 'blog_posts'),
        ]);

        // Status and publication date are guarded like every other workflow field.
        $post->forceFill([
            'status' => PostStatus::from($data['status']),
            'published_at' => $request->publishingAt(),
        ])->save();

        if ($request->hasFile('cover')) {
            $post->storeCover($request->file('cover'));
        }

        return redirect()
            ->route('admin.content.posts.edit', $post->id)
            ->with('success', __('admin.posts.created', ['title' => $post->title_fr]));
    }

    public function edit(BlogPost $post): View
    {
        return view('admin.content.posts.form', [
            'post' => $post,
            'statuses' => PostStatus::cases(),
        ]);
    }

    public function update(BlogPostRequest $request, BlogPost $post): RedirectResponse
    {
        $data = $request->validated();

        $post->update([
            ...collect($data)->except(['cover', 'status', 'slug'])->all(),
            'slug' => Slug::unique($data['slug'] ?? $post->slug, 'blog_posts', $post->id),
        ]);

        $post->forceFill([
            'status' => PostStatus::from($data['status']),
            'published_at' => $request->publishingAt() ?? $post->published_at,
        ])->save();

        if ($request->hasFile('cover')) {
            $post->storeCover($request->file('cover'));
        }

        return redirect()
            ->route('admin.content.posts.edit', $post->id)
            ->with('success', __('admin.posts.updated', ['title' => $post->title_fr]));
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $title = $post->title_fr;

        $post->delete();
        $post->deleteCover();

        return redirect()
            ->route('admin.content.posts.index')
            ->with('success', __('admin.posts.deleted', ['title' => $title]));
    }
}
