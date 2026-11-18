<?php

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->admin()->create(['locale' => 'en']);
    $this->product = Product::factory()->create(['name_fr' => 'Boubou Royal', 'name_en' => 'Royal Boubou']);
});

test('the photo manager renders the gallery', function () {
    ProductImage::factory()->for($this->product)->primary()->create(['path' => 'products/front.jpg']);

    $this->actingAs($this->admin)
        ->get(route('admin.catalogue.products.images.index', $this->product))
        ->assertOk()
        ->assertSee('/storage/products/front.jpg')
        ->assertSee(__('admin.images.primary'));
});

test('several photos can be uploaded in one go', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.images.store', $this->product), [
            'images' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('back.png'),
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $images = $this->product->images()->orderBy('sort_order')->get();

    expect($images)->toHaveCount(2)
        ->and($images[0]->is_primary)->toBeTrue()
        ->and($images[1]->is_primary)->toBeFalse()
        ->and($images[0]->alt_fr)->toBe('Boubou Royal')
        ->and($images[0]->alt_en)->toBe('Royal Boubou');

    Storage::disk('public')->assertExists($images[0]->path);
});

test('uploads are converted to webp with a card-sized thumbnail', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.images.store', $this->product), [
            'images' => [UploadedFile::fake()->image('front.jpg', 1200, 800)],
        ])
        ->assertSessionHasNoErrors();

    $image = $this->product->images()->firstOrFail();

    expect($image->path)->toEndWith('.webp')
        ->and($image->thumb_path)->toEndWith('-thumb.webp')
        ->and($image->thumbUrl())->toContain('-thumb.webp');

    Storage::disk('public')->assertExists($image->path);
    Storage::disk('public')->assertExists($image->thumb_path);
});

test('images uploaded before thumbnails existed still serve the full file', function () {
    $image = ProductImage::factory()->for($this->product)->create([
        'path' => 'products/legacy.jpg',
        'thumb_path' => null,
    ]);

    expect($image->thumbUrl())->toContain('legacy.jpg')
        ->not->toContain('-thumb');
});

test('an uploaded file that is not an image is refused', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.images.store', $this->product), [
            'images' => [UploadedFile::fake()->create('catalogue.pdf', 120, 'application/pdf')],
        ])
        ->assertSessionHasErrors('images.0');

    expect($this->product->images()->count())->toBe(0);
});

test('an oversized photo is refused', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.images.store', $this->product), [
            'images' => [UploadedFile::fake()->image('huge.jpg')->size(6000)],
        ])
        ->assertSessionHasErrors('images.0');

    expect($this->product->images()->count())->toBe(0);
});

test('the main photo can be handed to another upload', function () {
    $first = ProductImage::factory()->for($this->product)->primary()->create();
    $second = ProductImage::factory()->for($this->product)->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.products.images.primary', [$this->product, $second]))
        ->assertRedirect();

    expect($second->refresh()->is_primary)->toBeTrue()
        ->and($first->refresh()->is_primary)->toBeFalse();
});

test('alt text and position are saved from the row form', function () {
    $image = ProductImage::factory()->for($this->product)->create(['sort_order' => 1]);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.products.images.update', [$this->product, $image]), [
            'alt_fr' => 'Boubou vu de face',
            'alt_en' => 'Boubou seen from the front',
            'sort_order' => 7,
        ])
        ->assertSessionHasNoErrors();

    $image->refresh();

    expect($image->alt_fr)->toBe('Boubou vu de face')
        ->and($image->alt_en)->toBe('Boubou seen from the front')
        ->and($image->sort_order)->toBe(7);
});

test('a photo can be tagged with a variant colour', function () {
    $image = ProductImage::factory()->for($this->product)->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.products.images.update', [$this->product, $image]), [
            'color' => 'Noir',
        ])
        ->assertSessionHasNoErrors();

    expect($image->refresh()->color)->toBe('Noir');
});

test('the colour is applied to photos uploaded in one batch', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.products.images.store', $this->product), [
            'images' => [UploadedFile::fake()->image('noir.jpg')],
            'color' => 'Noir',
        ])
        ->assertSessionHasNoErrors();

    expect($this->product->images()->firstOrFail()->color)->toBe('Noir');
});

test('the whole gallery can be reordered at once', function () {
    $first = ProductImage::factory()->for($this->product)->create(['sort_order' => 1]);
    $second = ProductImage::factory()->for($this->product)->create(['sort_order' => 2]);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.products.images.order', $this->product), [
            'order' => [$first->id => 2, $second->id => 1],
        ])
        ->assertRedirect();

    expect($first->refresh()->sort_order)->toBe(2)
        ->and($second->refresh()->sort_order)->toBe(1);
});

test('removing the main photo promotes the next one and deletes the file', function () {
    $primary = ProductImage::factory()->for($this->product)->primary()->create(['path' => 'products/front.jpg', 'sort_order' => 1]);
    $next = ProductImage::factory()->for($this->product)->create(['path' => 'products/back.jpg', 'sort_order' => 2]);

    Storage::disk('public')->put('products/front.jpg', 'binary');

    $this->actingAs($this->admin)
        ->delete(route('admin.catalogue.products.images.destroy', [$this->product, $primary]))
        ->assertRedirect();

    $this->assertDatabaseMissing('product_images', ['id' => $primary->id]);
    Storage::disk('public')->assertMissing('products/front.jpg');

    expect($next->refresh()->is_primary)->toBeTrue();
});

test('a photo belonging to another product cannot be touched through this product', function () {
    $other = Product::factory()->create();
    $image = ProductImage::factory()->for($other)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.catalogue.products.images.destroy', [$this->product, $image]))
        ->assertNotFound();

    $this->assertDatabaseHas('product_images', ['id' => $image->id]);
});
