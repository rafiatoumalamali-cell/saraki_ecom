<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;

test('the product page shows the piece, its reference and its stock', function () {
    $product = Product::factory()->create([
        'name_fr' => 'Boubou Royal',
        'name_en' => 'Royal Boubou',
        'base_price' => 1200,
        'base_sku' => 'SRK-BOU-100',
        'description_fr' => 'Un boubou ample en bazin.',
        'description_en' => 'A flowing bazin boubou.',
    ]);

    // The factory already gives the product a single line; it becomes the M/Noir one.
    $product->variants()->first()->update([
        'size' => 'M',
        'color' => 'Noir',
        'sku' => 'SRK-BOU-100-M-NOIR',
        'stock_qty' => 7,
    ]);

    $this->get($product->url('fr'))
        ->assertOk()
        ->assertSee('Boubou Royal')
        ->assertSee('SRK-BOU-100-M-NOIR')
        ->assertSee(__('shop.product.stock_in', ['count' => 7]))
        ->assertSee('M / Noir');
});

test('the description is written in the language of the address', function () {
    $product = Product::factory()->create([
        'name_fr' => 'Boubou Royal',
        'name_en' => 'Royal Boubou',
        'description_fr' => 'Bazin brodé à la main.',
        'description_en' => 'Hand-embroidered bazin.',
    ]);

    $this->get($product->url('en'))
        ->assertOk()
        ->assertSee('Royal Boubou')
        ->assertSee('Hand-embroidered bazin.');
});

test('an unpublished piece is not reachable from the shop', function () {
    $product = Product::factory()->inactive()->create();

    $this->get($product->url('fr'))->assertNotFound();
});

test('an unknown slug is a dead end', function () {
    $this->get(route('shop.show', ['locale' => 'fr', 'product' => 'piece-inexistante']))->assertNotFound();
});

test('the chosen variant is preselected from the address', function () {
    $product = Product::factory()->create();
    $first = ProductVariant::factory()->for($product)->create(['size' => 'M', 'color' => 'Or', 'sku' => 'SRK-VAR-M-OR', 'stock_qty' => 4]);
    $second = ProductVariant::factory()->for($product)->create(['size' => 'L', 'color' => 'Or', 'sku' => 'SRK-VAR-L-OR', 'stock_qty' => 0]);

    $this->get($product->url('fr').'?variant='.$second->id)
        ->assertOk()
        ->assertSee('SRK-VAR-L-OR')
        ->assertSee(__('shop.product.stock_out'));

    $this->get($product->url('fr').'?variant='.$first->id)
        ->assertOk()
        ->assertSee('SRK-VAR-M-OR');
});

test('an in-stock variant is picked by default over a sold-out one', function () {
    $product = Product::factory()->create();

    ProductVariant::factory()->for($product)->create(['size' => 'A', 'color' => 'Or', 'sku' => 'SRK-VAR-A', 'stock_qty' => 0]);
    ProductVariant::factory()->for($product)->create(['size' => 'B', 'color' => 'Or', 'sku' => 'SRK-VAR-B', 'stock_qty' => 3]);

    $this->get($product->url('fr'))
        ->assertOk()
        ->assertSee('SRK-VAR-B');
});

test('a fully sold-out piece carries the badge', function () {
    $product = Product::factory()->create();
    $product->variants()->update(['stock_qty' => 0]);

    $this->get($product->url('fr'))
        ->assertOk()
        ->assertSee(__('shop.badge.sold_out'));
});

test('other pieces from the same collection are suggested', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'name_fr' => 'Boubou Royal']);
    Product::factory()->create(['category_id' => $category->id, 'name_fr' => 'Boubou Frère']);

    $this->get($product->url('fr'))
        ->assertOk()
        ->assertSee(__('shop.product.related'))
        ->assertSee('Boubou Frère');
});

test('the product page carries product-level Open Graph data', function () {
    $product = Product::factory()->create(['name_fr' => 'Boubou Royal']);
    ProductImage::factory()->for($product)->primary()->create(['path' => 'products/boubou.jpg']);

    $this->get($product->url('fr'))
        ->assertOk()
        ->assertSee('<meta property="og:type" content="product">', false)
        ->assertSee('<link rel="canonical" href="'.$product->url('fr').'">', false)
        ->assertSee('og:image', false)
        ->assertSee('hreflang="en"', false);
});

test('a piece without a photo still renders', function () {
    $product = Product::factory()->create(['name_fr' => 'Boubou Sans Photo']);

    $this->get($product->url('fr'))
        ->assertOk()
        ->assertSee('Boubou Sans Photo')
        ->assertSee(__('shop.card.no_image'));
});
