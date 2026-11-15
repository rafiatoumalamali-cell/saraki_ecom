<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the faq page renders in both locales', function (string $locale, string $expected) {
    $this->get(route('faq.index', $locale))
        ->assertOk()
        ->assertSee($expected)
        ->assertSee('FAQPage', false);
})->with([
    'french' => ['fr', 'Questions fréquentes'],
    'english' => ['en', 'Frequently asked questions'],
]);

test('the footer links to the faq page', function () {
    $this->get(route('home', 'fr'))
        ->assertOk()
        ->assertSee(route('faq.index', 'fr'), false);
});
