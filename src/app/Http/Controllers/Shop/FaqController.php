<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(string $locale): View
    {
        $canonical = route('faq.index', $locale);

        return view('shop.faq', [
            'seo' => [
                'title' => __('faq.title'),
                'description' => __('faq.description'),
                'canonical' => $canonical,
                'alternates' => collect(config('saraki.supported_locales'))
                    ->mapWithKeys(fn (string $target) => [$target => route('faq.index', $target)])
                    ->all(),
            ],
            'jsonLd' => $this->jsonLd($canonical),
        ]);
    }

    /**
     * FAQPage structured data lets crawlers surface the answers directly in
     * search results; every question/answer pair is emitted as a mainEntity.
     */
    private function jsonLd(string $canonical): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'name' => __('faq.jsonld.name'),
            'url' => $canonical,
            'mainEntity' => collect(__('faq.items'))
                ->flatten(1)
                ->map(fn (array $item) => [
                    '@type' => 'Question',
                    'name' => $item['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['a'],
                    ],
                ])
                ->values()
                ->all(),
        ];
    }
}
