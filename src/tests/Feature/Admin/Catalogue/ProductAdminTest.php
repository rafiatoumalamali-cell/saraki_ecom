<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['locale' => 'en']);
    $this->category = Category::factory()->create(['name_fr' => 'Boubous', 'name_en' => 'Boubous']);
});

test('the product screens are closed to guests and customers', function (string $path) {
    $this->get($path)->assertRedirect('/login');

    $this->actingAs(User::factory()->create())->get($path)->assertForbidden();
})->with([
    '/admin/catalogue/products',
    '/admin/catalogue/products/create',
    '/admin/catalogue/categories',
]);

test('the product form opens for a new and an existing piece', function () {
    $product = Product::factory()->create(['name_fr' => 'Boubou Royal']);

    $this->actingAs($this->admin)->get(route('admin.catalogue.products.create'))
        ->assertOk()
        ->assertSee(__('admin.products.create'))
        ->assertSee('Boubous');

    $this->actingAs($this->admin)->get(route('admin.catalogue.products.edit', $product))
        ->assertOk()
        ->assertSee('Boubou Royal')
        ->assertSee(route('admin.catalogue.products.images.index', $product), false)
        ->assertSee(route('admin.catalogue.products.variants.index', $product), false);
});

test('an admin can list, search and filter products', function () {
    Product::factory()->create(['name_fr' => 'Boubou Royal', 'name_en' => 'Royal Boubou', 'base_sku' => 'SRK-BOU-500']);
    Product::factory()->create(['name_fr' => 'Caftan Nuit', 'name_en' => 'Night Caftan', 'base_sku' => 'SRK-CAF-500']);
    Product::factory()->inactive()->create(['name_fr' => 'Robe Cachée', 'name_en' => 'Hidden Dress']);

    $this->actingAs($this->admin)->get(route('admin.catalogue.products.index'))
        ->assertOk()
        ->assertSee('Boubou Royal')
        ->assertSee('Caftan Nuit');

    // Operators look pieces up by reference, not only by name.
    $this->actingAs($this->admin)->get(route('admin.catalogue.products.index', ['q' => 'SRK-CAF-500']))
        ->assertOk()
        ->assertSee('Caftan Nuit')
        ->assertDontSee('Boubou Royal');

    $this->actingAs($this->admin)->get(route('admin.catalogue.products.index', ['status' => 'inactive']))
        ->assertOk()
        ->assertSee('Robe Cachée')
        ->assertDontSee('Boubou Royal');
});

test('a product can be published from the admin form', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.store'), [
            'name_fr' => 'Boubou Royal',
            'name_en' => 'Royal Boubou',
            'slug' => '',
            'description_fr' => 'Un boubou ample en bazin.',
            'description_en' => 'A flowing bazin boubou.',
            'category_id' => $this->category->id,
            'base_price' => '1 250,50',
            'base_sku' => 'srk-bou-900',
            'is_active' => '1',
            'is_featured' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $product = Product::query()->where('base_sku', 'SRK-BOU-900')->sole();

    expect($product->slug)->toBe('boubou-royal')
        ->and((float) $product->base_price)->toBe(1250.5)
        ->and($product->category_id)->toBe($this->category->id)
        ->and($product->is_active)->toBeTrue()
        ->and($product->is_featured)->toBeTrue();
});

test('the slug is made unique instead of letting the insert fail', function () {
    Product::factory()->create(['slug' => 'boubou-royal']);

    $this->actingAs($this->admin)->post(route('admin.catalogue.products.store'), [
        'name_fr' => 'Boubou Royal',
        'name_en' => 'Royal Boubou',
        'category_id' => $this->category->id,
        'base_price' => '900',
        'base_sku' => 'SRK-BOU-901',
        'is_active' => '1',
    ])->assertSessionHasNoErrors();

    expect(Product::query()->where('base_sku', 'SRK-BOU-901')->sole()->slug)->toBe('boubou-royal-2');
});

test('the product form rejects an invalid price, reference and missing names', function (array $payload, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.store'), $payload)
        ->assertSessionHasErrors($field);

    expect(Product::query()->count())->toBe(0);
})->with([
    'missing french name' => [['name_en' => 'Royal Boubou'], 'name_fr'],
    'price is not a number' => [['name_fr' => 'Boubou Royal', 'name_en' => 'Royal Boubou', 'base_price' => 'cher'], 'base_price'],
    'sku has a forbidden character' => [['name_fr' => 'Boubou Royal', 'name_en' => 'Royal Boubou', 'base_price' => '900', 'base_sku' => 'SRK BOU 901'], 'base_sku'],
    'unknown category' => [['name_fr' => 'Boubou Royal', 'name_en' => 'Royal Boubou', 'base_price' => '900', 'base_sku' => 'SRK-BOU-902', 'category_id' => 99999], 'category_id'],
]);

test('a duplicate base reference is refused', function () {
    Product::factory()->create(['base_sku' => 'SRK-BOU-903']);

    $this->actingAs($this->admin)->post(route('admin.catalogue.products.store'), [
        'name_fr' => 'Boubou Royal',
        'name_en' => 'Royal Boubou',
        'category_id' => $this->category->id,
        'base_price' => '900',
        'base_sku' => 'SRK-BOU-903',
    ])->assertSessionHasErrors('base_sku');
});

test('a product can be edited without tripping over its own reference', function () {
    $product = Product::factory()->create(['base_sku' => 'SRK-BOU-904']);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.products.update', $product), [
            'name_fr' => 'Boubou Royal II',
            'name_en' => 'Royal Boubou II',
            'category_id' => $this->category->id,
            'base_price' => '1499',
            'base_sku' => 'SRK-BOU-904',
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors();

    $product->refresh();

    expect($product->name_fr)->toBe('Boubou Royal II')
        ->and((float) $product->base_price)->toBe(1499.0)
        ->and($product->is_active)->toBeTrue();
});

test('an unticked visibility box hides the product', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.catalogue.products.update', $product), [
        'name_fr' => $product->name_fr,
        'name_en' => $product->name_en,
        'category_id' => $product->category_id,
        'base_price' => $product->base_price,
        'base_sku' => $product->base_sku,
    ])->assertSessionHasNoErrors();

    expect($product->refresh()->is_active)->toBeFalse();
});

test('deleting a product takes its variants with it', function () {
    $product = Product::factory()->create();

    expect($product->variants()->count())->toBe(1);

    $this->actingAs($this->admin)
        ->delete(route('admin.catalogue.products.destroy', $product))
        ->assertRedirect(route('admin.catalogue.products.index'));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    $this->assertDatabaseMissing('product_variants', ['product_id' => $product->id]);
});
