<?php

use App\Models\Category;
use App\Models\Product;

test('the catalogue lists every published piece', function () {
    Product::factory()->create(['name_fr' => 'Boubou Royal', 'name_en' => 'Royal Boubou']);
    Product::factory()->inactive()->create(['name_fr' => 'Robe Cachée', 'name_en' => 'Hidden Dress']);

    $this->get(route('shop.index', 'fr'))
        ->assertOk()
        ->assertSee('Boubou Royal')
        ->assertDontSee('Robe Cachée');
});

test('the catalogue speaks the language of the address', function () {
    Product::factory()->create(['name_fr' => 'Boubou Royal', 'name_en' => 'Royal Boubou']);

    $this->get(route('shop.index', 'en'))
        ->assertOk()
        ->assertSee('Royal Boubou')
        ->assertDontSee('Boubou Royal');
});

test('a collection filters the grid and names itself in the heading', function () {
    $boubous = Category::factory()->create(['slug' => 'boubous', 'name_fr' => 'Boubous', 'name_en' => 'Boubous']);
    $caftans = Category::factory()->create(['slug' => 'caftans', 'name_fr' => 'Caftans', 'name_en' => 'Caftans']);

    Product::factory()->create(['category_id' => $boubous->id, 'name_fr' => 'Boubou Royal']);
    Product::factory()->create(['category_id' => $caftans->id, 'name_fr' => 'Caftan Nuit']);

    $this->get(route('shop.index', ['locale' => 'fr', 'category' => 'caftans']))
        ->assertOk()
        ->assertSee('Caftan Nuit')
        ->assertDontSee('Boubou Royal');
});

test('an unknown or hidden collection is a dead end', function () {
    Category::factory()->inactive()->create(['slug' => 'cachee', 'name_fr' => 'Cachée', 'name_en' => 'Hidden']);

    $this->get(route('shop.index', ['locale' => 'fr', 'category' => 'inconnue']))->assertNotFound();
    $this->get(route('shop.index', ['locale' => 'fr', 'category' => 'cachee']))->assertNotFound();
});

test('search narrows the grid down to the matching pieces', function () {
    Product::factory()->create(['name_fr' => 'Boubou Royal', 'name_en' => 'Royal Boubou']);
    Product::factory()->create(['name_fr' => 'Caftan Nuit', 'name_en' => 'Night Caftan']);

    $this->get(route('shop.index', ['locale' => 'fr', 'q' => 'boubou']))
        ->assertOk()
        ->assertSee('Boubou Royal')
        ->assertDontSee('Caftan Nuit');
});

test('a multi-word search narrows rather than widens', function () {
    Product::factory()->create(['name_fr' => 'Boubou Royal', 'name_en' => 'Royal Boubou', 'description_fr' => null, 'description_en' => null]);
    Product::factory()->create(['name_fr' => 'Boubou Simple', 'name_en' => 'Plain Boubou', 'description_fr' => null, 'description_en' => null]);

    $found = $this->get(route('shop.index', ['locale' => 'fr', 'q' => 'boubou royal']))
        ->assertOk()
        ->assertSee('Boubou Royal')
        ->assertDontSee('Boubou Simple')
        ->viewData('products');

    expect($found->total())->toBe(1);
});

test('a search result page asks not to be indexed', function () {
    Product::factory()->create();

    $this->get(route('shop.index', ['locale' => 'fr', 'q' => 'boubou']))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,follow">', false);

    // A plain collection stays indexable.
    $this->get(route('shop.index', 'fr'))
        ->assertOk()
        ->assertDontSee('<meta name="robots"', false);
});

test('a search with no match shows the empty state', function () {
    Product::factory()->create(['name_fr' => 'Boubou Royal']);

    $this->get(route('shop.index', ['locale' => 'fr', 'q' => 'zzzzz']))
        ->assertOk()
        ->assertSee(__('shop.catalogue.empty'));
});

test('the grid can be sorted by price in both directions', function () {
    Product::factory()->create(['name_fr' => 'Cher', 'base_price' => 900]);
    Product::factory()->create(['name_fr' => 'Bon marché', 'base_price' => 150]);

    $ascending = $this->get(route('shop.index', ['locale' => 'fr', 'sort' => 'price_asc']))
        ->viewData('products')
        ->pluck('name_fr')
        ->all();

    $descending = $this->get(route('shop.index', ['locale' => 'fr', 'sort' => 'price_desc']))
        ->viewData('products')
        ->pluck('name_fr')
        ->all();

    expect($ascending)->toBe(['Bon marché', 'Cher'])
        ->and($descending)->toBe(['Cher', 'Bon marché']);
});

test('an unknown sort key falls back to the newest first', function () {
    Product::factory()->create();

    $this->get(route('shop.index', ['locale' => 'fr', 'sort' => 'n-importe-quoi']))->assertOk();
});

test('the grid is paged twelve at a time', function () {
    Product::factory()->count(13)->create();

    $first = $this->get(route('shop.index', 'fr'))->assertOk()->viewData('products');
    $second = $this->get(route('shop.index', ['locale' => 'fr', 'page' => 2]))->assertOk()->viewData('products');

    expect($first->total())->toBe(13)
        ->and($first->count())->toBe(12)
        ->and($second->count())->toBe(1);
});

test('the catalogue carries its canonical link and both languages', function () {
    $this->get(route('shop.index', 'fr'))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.route('shop.index', 'fr').'">', false)
        ->assertSee('hreflang="fr"', false)
        ->assertSee('hreflang="en"', false)
        ->assertSee('hreflang="x-default"', false);
});
