<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $locale, Page $page): View
    {
        abort_unless($page->isPublished(), 404);

        return view('shop.page', [
            'page' => $page,
            'seo' => [
                'title' => $page->translate('title'),
                'description' => str($page->translate('body'))->stripTags()->limit(155)->toString(),
                'canonical' => $page->url($locale),
                'alternates' => collect(config('saraki.supported_locales'))
                    ->mapWithKeys(fn (string $target) => [$target => $page->url($target)])
                    ->all(),
            ],
        ]);
    }
}
