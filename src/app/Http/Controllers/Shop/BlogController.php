<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\View\View;

class BlogController extends Controller
{
    private const PER_PAGE = 9;

    public function index(string $locale): View
    {
        return view('shop.journal.index', [
            'posts' => BlogPost::query()
                ->published()
                ->with('author:id,name')
                ->paginate(self::PER_PAGE),
            'seo' => [
                'title' => __('shop.journal.title'),
                'description' => __('shop.journal.description'),
                'canonical' => route('journal.index', $locale),
                'alternates' => collect(config('saraki.supported_locales'))
                    ->mapWithKeys(fn (string $target) => [$target => route('journal.index', $target)])
                    ->all(),
            ],
        ]);
    }

    public function show(string $locale, BlogPost $post): View
    {
        // A draft is visible to the atelier only; the public URL must 404 so an
        // unfinished article is never indexed.
        abort_unless($post->isPublished(), 404);

        $post->load('author:id,name');

        return view('shop.journal.show', [
            'post' => $post,
            'related' => BlogPost::query()
                ->published()
                ->whereKeyNot($post->id)
                ->with('author:id,name')
                ->limit(3)
                ->get(),
            'seo' => [
                'title' => $post->translate('title'),
                'description' => $post->translate('excerpt') ?? str($post->translate('body'))->stripTags()->limit(155)->toString(),
                'canonical' => $post->url($locale),
                'image' => $post->coverUrl(),
                'type' => 'article',
                'alternates' => collect(config('saraki.supported_locales'))
                    ->mapWithKeys(fn (string $target) => [$target => $post->url($target)])
                    ->all(),
            ],
        ]);
    }
}
