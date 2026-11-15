<?php

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Product;

beforeEach(function () {
    // The sitemap is cached for an hour; each test needs a fresh render.
    Illuminate\Support\Facades\Cache::forget('sitemap.xml');
});

test('sitemap.xml lists public pages in every locale with hreflang alternates', function () {
    $product = Product::factory()->create(['is_active' => true]);
    $post = BlogPost::factory()->published()->create();
    $page = Page::factory()->create(['is_published' => true]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = $response->getContent();

    expect($xml)->toContain('<urlset')
        ->toContain('hreflang="fr"')
        ->toContain('hreflang="en"')
        ->toContain('hreflang="x-default"')
        ->toContain(route('home', 'fr'))
        ->toContain($product->url('fr'))
        ->toContain($post->url('en'))
        ->toContain($page->url('fr'));
});

test('sitemap.xml omits drafts, inactive products and unpublished pages', function () {
    Product::factory()->create(['is_active' => false, 'slug' => 'hidden-piece']);
    BlogPost::factory()->create(['slug' => 'draft-post']); // draft by default
    Page::factory()->create(['is_published' => false, 'slug' => 'secret-page']);

    $xml = $this->get('/sitemap.xml')->getContent();

    expect($xml)->not->toContain('hidden-piece')
        ->not->toContain('draft-post')
        ->not->toContain('secret-page');
});

test('robots.txt disallows private areas and points at the sitemap', function () {
    $body = $this->get('/robots.txt')->assertOk()->getContent();

    expect($body)->toContain('User-agent: *')
        ->toContain('Disallow: /admin')
        ->toContain('Disallow: /fr/cart')
        ->toContain('Disallow: /en/checkout')
        ->toContain('Disallow: /fr/orders')
        ->toContain('Sitemap: '.route('sitemap'));
});

test('product pages carry Product JSON-LD and store pages carry Organization JSON-LD', function () {
    $product = Product::factory()->create([
        'is_active' => true,
        'name_fr' => 'Boubou Test',
        'base_price' => 1500,
    ]);

    $html = $this->get($product->url('fr'))->assertOk()->getContent();

    expect($html)->toContain('"@type":"Product"')
        ->toContain('"priceCurrency":"GHS"')
        ->toContain('"@type":"Organization"');
});

test('account and auth pages ask crawlers not to index them', function () {
    $this->get('/login')->assertOk()->assertSee('noindex', false);

    $user = App\Models\User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('noindex', false);
});
