<?php

use App\Models\Category;
use App\Models\Product;

test('the home page greets the visitor and links into the catalogue', function () {
    $this->get(route('home', 'fr'))
        ->assertOk()
        ->assertSee(__('shop.home.hero_title'))
        ->assertSee(route('shop.index', 'fr'), false);
});

test('featured pieces fill the signature shelf', function () {
    Product::factory()->featured()->create(['name_fr' => 'Boubou Signature']);
    Product::factory()->create(['name_fr' => 'Boubou Ordinaire']);

    $featured = $this->get(route('home', 'fr'))->assertOk()->viewData('featured');

    expect($featured)->toHaveCount(1)
        ->and($featured->first()->name_fr)->toBe('Boubou Signature');
});

test('a shop with no feature flags falls back to the newest pieces', function () {
    Product::factory()->count(3)->create();

    $featured = $this->get(route('home', 'fr'))->assertOk()->viewData('featured');
    $latest = $this->get(route('home', 'fr'))->viewData('latest');

    // Nothing is left over for the second shelf when the hero borrows it all.
    expect($featured)->toHaveCount(3)
        ->and($latest)->toBeEmpty();
});

test('the second shelf never repeats a featured piece', function () {
    $star = Product::factory()->featured()->create();

    foreach (range(1, 5) as $ignored) {
        Product::factory()->create();
    }

    $latest = $this->get(route('home', 'fr'))->assertOk()->viewData('latest');

    expect($latest->pluck('id')->all())->not->toContain($star->id)
        ->and($latest->count())->toBeLessThanOrEqual(4);
});

test('collections are listed with their published count', function () {
    $category = Category::factory()->create(['slug' => 'boubous', 'name_fr' => 'Boubous', 'name_en' => 'Boubous']);
    Product::factory()->count(2)->create(['category_id' => $category->id]);
    Product::factory()->inactive()->create(['category_id' => $category->id]);
    Category::factory()->inactive()->create(['name_fr' => 'Collection cachée']);

    $collections = $this->get(route('home', 'fr'))->assertOk()->viewData('collections');

    expect($collections)->toHaveCount(1)
        ->and($collections->first()->products_count)->toBe(2);
});

test('hidden pieces stay off the home page', function () {
    Product::factory()->inactive()->create(['name_fr' => 'Robe Cachée']);

    $this->get(route('home', 'fr'))
        ->assertOk()
        ->assertDontSee('Robe Cachée')
        ->assertSee(__('shop.home.empty'));
});

test('the home page ships its own description and canonical link', function () {
    $this->get(route('home', 'fr'))
        ->assertOk()
        ->assertSee('<meta name="description" content="'.e(__('shop.home.description')).'"', false)
        ->assertSee('<link rel="canonical" href="'.route('home', 'fr').'">', false)
        ->assertSee('<meta property="og:type" content="website">', false);
});

test('the home copy follows the address language', function () {
    $this->get(route('home', 'en'))
        ->assertOk()
        ->assertSee('Tailored to the way you move')
        ->assertDontSee('Taillé pour votre allure');
});
