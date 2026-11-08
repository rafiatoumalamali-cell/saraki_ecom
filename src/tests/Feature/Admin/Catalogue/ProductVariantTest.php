<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['locale' => 'en']);
    $this->product = Product::factory()->create(['base_sku' => 'SRK-BOU-700']);
});

test('the variant grid renders every row', function () {
    ProductVariant::factory()->for($this->product)->create(['size' => 'M', 'color' => 'Noir', 'sku' => 'SRK-BOU-700-M-NOIR']);

    $this->actingAs($this->admin)
        ->get(route('admin.catalogue.products.variants.index', $this->product))
        ->assertOk()
        ->assertSee('SRK-BOU-700-M-NOIR')
        ->assertSee(__('admin.variants.generate_title'));
});

test('a variant is added with a generated reference', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.variants.store', $this->product), [
            'size' => 'M',
            'color' => 'Noir',
            'sku' => '',
            'stock_qty' => 12,
            'price_delta' => '25',
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $variant = $this->product->variants()->where('size', 'M')->sole();

    expect($variant->sku)->toBe('SRK-BOU-700-M-NOIR')
        ->and($variant->stock_qty)->toBe(12)
        ->and((float) $variant->price_delta)->toBe(25.0)
        ->and($variant->is_active)->toBeTrue();
});

test('a product keeps one single-variant line, not two', function () {
    $other = Product::factory()->create(['base_sku' => 'SRK-ACC-700']);

    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.variants.store', $other), [
            'stock_qty' => 5,
            'price_delta' => '0',
        ])
        ->assertSessionHasErrors('size');

    expect($other->variants()->count())->toBe(1);
});

test('the same size and colour cannot be added twice, whatever the case', function () {
    $this->actingAs($this->admin)->post(route('admin.catalogue.products.variants.store', $this->product), [
        'size' => 'M',
        'color' => 'Noir',
        'stock_qty' => 3,
        'price_delta' => '0',
    ])->assertSessionHasNoErrors();

    $this->actingAs($this->admin)->post(route('admin.catalogue.products.variants.store', $this->product), [
        'size' => 'm',
        'color' => 'noir',
        'stock_qty' => 9,
        'price_delta' => '0',
    ])->assertSessionHasErrors('size');

    expect($this->product->variants()->where('size', 'M')->count())->toBe(1);
});

test('stock is taken through the whole grid in one submit', function () {
    $medium = ProductVariant::factory()->for($this->product)->create(['size' => 'M', 'color' => 'Noir', 'sku' => 'SRK-BOU-700-M-NOIR', 'stock_qty' => 4]);
    $large = ProductVariant::factory()->for($this->product)->create(['size' => 'L', 'color' => 'Noir', 'sku' => 'SRK-BOU-700-L-NOIR', 'stock_qty' => 6]);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.products.variants.batch', $this->product), [
            'variants' => [
                $medium->id => ['size' => 'M', 'color' => 'Noir', 'sku' => 'SRK-BOU-700-M-NOIR', 'stock_qty' => 20, 'price_delta' => '0', 'is_active' => '1'],
                $large->id => ['size' => 'L', 'color' => 'Noir', 'sku' => 'SRK-BOU-700-L-NOIR', 'stock_qty' => 0, 'price_delta' => '15.50'],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($medium->refresh()->stock_qty)->toBe(20)
        ->and($large->refresh()->stock_qty)->toBe(0)
        ->and((float) $large->price_delta)->toBe(15.5)
        // An unticked availability box hides the line without deleting it.
        ->and($large->is_active)->toBeFalse()
        ->and($medium->is_active)->toBeTrue();
});

test('rows flagged for removal are deleted on save', function () {
    $keep = ProductVariant::factory()->for($this->product)->create(['sku' => 'SRK-BOU-700-M-NOIR']);
    $drop = ProductVariant::factory()->for($this->product)->create(['sku' => 'SRK-BOU-700-L-NOIR']);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.products.variants.batch', $this->product), [
            'variants' => [
                $keep->id => ['size' => 'M', 'color' => 'Noir', 'sku' => 'SRK-BOU-700-M-NOIR', 'stock_qty' => 1, 'price_delta' => '0', 'is_active' => '1'],
                $drop->id => ['size' => 'L', 'color' => 'Noir', 'sku' => 'SRK-BOU-700-L-NOIR', 'stock_qty' => 1, 'price_delta' => '0', 'is_active' => '1'],
            ],
            'removed' => [$drop->id],
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('product_variants', ['id' => $drop->id]);
    $this->assertDatabaseHas('product_variants', ['id' => $keep->id]);
});

test('the grid refuses two rows claiming the same combination', function () {
    $first = ProductVariant::factory()->for($this->product)->create(['size' => 'M', 'color' => 'Noir', 'sku' => 'SRK-BOU-700-M-NOIR']);
    $second = ProductVariant::factory()->for($this->product)->create(['size' => 'L', 'color' => 'Ivoire', 'sku' => 'SRK-BOU-700-L-IVOIRE']);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.products.variants.batch', $this->product), [
            'variants' => [
                $first->id => ['size' => 'M', 'color' => 'Noir', 'sku' => 'SRK-BOU-700-M-NOIR', 'stock_qty' => 1, 'price_delta' => '0', 'is_active' => '1'],
                $second->id => ['size' => 'M', 'color' => 'noir', 'sku' => 'SRK-BOU-700-L-IVOIRE', 'stock_qty' => 1, 'price_delta' => '0', 'is_active' => '1'],
            ],
        ])
        ->assertSessionHasErrors('variants');
});

test('a variant belonging to another product cannot be written through this grid', function () {
    $other = Product::factory()->create();
    $foreign = ProductVariant::factory()->for($other)->create(['sku' => 'SRK-OTHER-1']);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.products.variants.batch', $this->product), [
            'variants' => [
                $foreign->id => ['size' => 'M', 'color' => 'Noir', 'sku' => 'SRK-OTHER-1', 'stock_qty' => 99, 'price_delta' => '0', 'is_active' => '1'],
            ],
        ])
        ->assertNotFound();

    expect($foreign->refresh()->stock_qty)->not->toBe(99);
});

test('the size and colour grid is generated, and running it again adds nothing', function () {
    $payload = [
        'sizes' => 'S, M',
        'colors' => 'Noir; Or',
        'stock_qty' => 8,
        'price_delta' => '0',
    ];

    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.variants.generate', $this->product), $payload)
        ->assertSessionHasNoErrors();

    expect($this->product->variants()->count())->toBe(5);

    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.variants.generate', $this->product), $payload)
        ->assertSessionHasNoErrors();

    expect($this->product->variants()->count())->toBe(5);
});

test('a variant is deleted on its own', function () {
    $variant = ProductVariant::factory()->for($this->product)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.catalogue.products.variants.destroy', [$this->product, $variant]))
        ->assertRedirect();

    $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
});
