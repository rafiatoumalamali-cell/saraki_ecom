<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['locale' => 'en']);
});

test('an admin sees every category with its product count', function () {
    $category = Category::factory()->create(['name_fr' => 'Boubous', 'name_en' => 'Boubous']);
    Product::factory()->count(2)->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)->get(route('admin.catalogue.categories.index'))
        ->assertOk()
        ->assertSee('Boubous')
        ->assertSee('2');
});

test('a category is created with a generated slug and the next position', function () {
    $first = Category::factory()->create(['sort_order' => 4]);

    $this->actingAs($this->admin)->post(route('admin.catalogue.categories.store'), [
        'name_fr' => 'Robes de soirée',
        'name_en' => 'Evening dresses',
        'slug' => '',
        'is_active' => '1',
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.catalogue.categories.index'));

    $created = Category::query()->where('name_fr', 'Robes de soirée')->sole();

    expect($created->slug)->toBe('robes-de-soiree')
        ->and($created->sort_order)->toBe(5)
        ->and($created->is_active)->toBeTrue()
        ->and($first->id)->not->toBe($created->id);
});

test('both names are required', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.catalogue.categories.store'), ['name_fr' => 'Boubous'])
        ->assertSessionHasErrors('name_en');

    expect(Category::query()->count())->toBe(0);
});

test('a category cannot be nested inside itself or its own children', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.categories.update', $parent), [
            'name_fr' => $parent->name_fr,
            'name_en' => $parent->name_en,
            'parent_id' => $parent->id,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('parent_id');

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.categories.update', $parent), [
            'name_fr' => $parent->name_fr,
            'name_en' => $parent->name_en,
            'parent_id' => $child->id,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('parent_id');
});

test('the move arrows swap a category with its neighbour', function () {
    $first = Category::factory()->create(['sort_order' => 1]);
    $second = Category::factory()->create(['sort_order' => 2]);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.categories.move', [$second, 'up']))
        ->assertRedirect();

    expect($second->refresh()->sort_order)->toBe(1)
        ->and($first->refresh()->sort_order)->toBe(2);
});

test('the first category cannot be moved up any further', function () {
    $only = Category::factory()->create(['sort_order' => 1]);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.categories.move', [$only, 'up']))
        ->assertRedirect();

    expect($only->refresh()->sort_order)->toBe(1);
});

test('the toggle hides and shows a category', function () {
    $category = Category::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.categories.toggle', $category))
        ->assertRedirect();

    expect($category->refresh()->is_active)->toBeFalse();

    $this->actingAs($this->admin)
        ->patch(route('admin.catalogue.categories.toggle', $category))
        ->assertRedirect();

    expect($category->refresh()->is_active)->toBeTrue();
});

test('a category still holding products cannot be deleted', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.catalogue.categories.destroy', $category))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('an empty category is deleted', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.catalogue.categories.destroy', $category))
        ->assertRedirect(route('admin.catalogue.categories.index'));

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});
