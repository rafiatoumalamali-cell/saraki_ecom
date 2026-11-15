<?php

use App\Enums\ContactStatus;
use App\Enums\GalleryCategory;
use App\Enums\PostStatus;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\GalleryItem;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->admin()->create(['locale' => 'en']);
});

test('an admin publishes a gallery photo without touching code', function () {
    $this->actingAs($this->admin)->post(route('admin.content.gallery.store'), [
        'title_fr' => 'Boubou brodé',
        'title_en' => 'Embroidered boubou',
        'category' => GalleryCategory::Event->value,
        'sort_order' => '5',
        'is_published' => '1',
        'image' => UploadedFile::fake()->image('boubou.jpg'),
    ])->assertRedirect(route('admin.content.gallery.index'));

    $item = GalleryItem::firstOrFail();

    expect($item->is_published)->toBeTrue()
        ->and($item->category)->toBe(GalleryCategory::Event)
        ->and($item->image_path)->toEndWith('.webp')
        ->and($item->thumb_path)->toEndWith('-thumb.webp')
        ->and(Storage::disk('public')->exists($item->image_path))->toBeTrue()
        ->and(Storage::disk('public')->exists($item->thumb_path))->toBeTrue();

    $this->get(route('gallery.index', 'en'))
        ->assertOk()
        ->assertSee('Embroidered boubou');
});

test('hiding a photo takes it off the storefront', function () {
    $item = GalleryItem::factory()->create(['title_en' => 'Gold boubou']);

    $this->actingAs($this->admin)
        ->patch(route('admin.content.gallery.toggle', $item))
        ->assertRedirect();

    expect($item->fresh()->is_published)->toBeFalse();

    $this->get(route('gallery.index', 'en'))
        ->assertOk()
        ->assertDontSee('Gold boubou');
});

test('an admin publishes an article the journal then serves', function () {
    $this->actingAs($this->admin)->post(route('admin.content.posts.store'), [
        'title_fr' => 'Le wax de Kumasi',
        'title_en' => 'The wax of Kumasi',
        'slug' => '',
        'excerpt_fr' => 'Un tissu, une histoire.',
        'excerpt_en' => 'One cloth, one story.',
        'body_fr' => 'Premier paragraphe du texte.',
        'body_en' => 'First paragraph of the text.',
        'status' => PostStatus::Published->value,
        'published_at' => '',
    ])->assertRedirect();

    $post = BlogPost::firstOrFail();

    expect($post->slug)->toBe('le-wax-de-kumasi')
        ->and($post->status)->toBe(PostStatus::Published)
        ->and($post->published_at)->not->toBeNull()
        ->and($post->author_id)->toBe($this->admin->id);

    $this->get(route('journal.index', 'en'))
        ->assertOk()
        ->assertSee('The wax of Kumasi');

    $this->get(route('journal.show', ['locale' => 'en', 'post' => $post->slug]))
        ->assertOk()
        ->assertSee('The wax of Kumasi')
        ->assertSee('First paragraph of the text.');
});

test('an uploaded cover is stored as webp with a thumbnail', function () {
    $this->actingAs($this->admin)->post(route('admin.content.posts.store'), [
        'title_fr' => 'Guide des tailles',
        'title_en' => 'Sizing guide',
        'slug' => 'guide-des-tailles',
        'excerpt_fr' => 'Comment prendre ses mesures.',
        'excerpt_en' => 'How to take your measurements.',
        'body_fr' => 'Corps.',
        'body_en' => 'Body.',
        'status' => PostStatus::Published->value,
        'published_at' => '',
        'cover' => UploadedFile::fake()->image('cover.jpg', 1200, 800),
    ])->assertRedirect();

    $post = BlogPost::firstOrFail();

    expect($post->cover_image)->toEndWith('.webp')
        ->and($post->cover_thumb)->toEndWith('-thumb.webp')
        ->and(Storage::disk('public')->exists($post->cover_image))->toBeTrue()
        ->and(Storage::disk('public')->exists($post->cover_thumb))->toBeTrue();
});

test('a draft article never reaches the journal', function () {
    $draft = BlogPost::factory()->create(['status' => PostStatus::Draft, 'published_at' => null]);

    $this->get(route('journal.show', ['locale' => 'fr', 'post' => $draft->slug]))->assertNotFound();
    $this->get(route('journal.index', 'fr'))->assertOk()->assertDontSee($draft->title_fr);
});

test('an admin edits a fixed page and the storefront follows', function () {
    $page = Page::factory()->create([
        'slug' => 'about',
        'title_fr' => 'La maison',
        'title_en' => 'The house',
        'body_fr' => 'Texte initial.',
        'body_en' => 'Initial text.',
    ]);

    $this->actingAs($this->admin)->patch(route('admin.content.pages.update', $page->id), [
        'title_fr' => 'La maison',
        'title_en' => 'The house',
        'slug' => 'about',
        'body_fr' => 'Texte initial.',
        'body_en' => 'Updated page body.',
        'is_published' => '1',
    ])->assertRedirect(route('admin.content.pages.edit', $page->id));

    $this->get(route('pages.show', ['locale' => 'en', 'page' => 'about']))
        ->assertOk()
        ->assertSee('Updated page body.');
});

test('a page the footer links to cannot be deleted', function () {
    $page = Page::factory()->create(['slug' => 'shipping']);

    $this->actingAs($this->admin)
        ->delete(route('admin.content.pages.destroy', $page->id))
        ->assertRedirect(route('admin.content.pages.index'))
        ->assertSessionHas('error');

    expect(Page::whereKey($page->id)->exists())->toBeTrue();
});

test('an admin creates a page and can delete it again', function () {
    $this->actingAs($this->admin)->post(route('admin.content.pages.store'), [
        'title_fr' => 'Guide des tailles',
        'title_en' => 'Size guide',
        'slug' => '',
        'body_fr' => 'Comment prendre ses mesures.',
        'body_en' => 'How to take your measurements.',
        'is_published' => '1',
    ])->assertRedirect();

    $page = Page::firstOrFail();

    expect($page->slug)->toBe('guide-des-tailles');

    $this->get(route('pages.show', ['locale' => 'fr', 'page' => $page->slug]))->assertOk();

    $this->actingAs($this->admin)
        ->delete(route('admin.content.pages.destroy', $page->id))
        ->assertRedirect(route('admin.content.pages.index'));

    expect(Page::whereKey($page->id)->exists())->toBeFalse();
});

test('opening an enquiry files it as read', function () {
    $message = ContactMessage::factory()->create(['name' => 'Ama Serwaa']);

    $this->actingAs($this->admin)->get(route('admin.messages.index'))
        ->assertOk()
        ->assertSee('Ama Serwaa');

    $this->actingAs($this->admin)->get(route('admin.messages.show', $message))
        ->assertOk()
        ->assertSee('Ama Serwaa');

    expect($message->fresh()->status)->toBe(ContactStatus::Read);
});

test('an admin files an enquiry as replied', function () {
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('admin.messages.status', $message), ['status' => ContactStatus::Replied->value])
        ->assertRedirect();

    expect($message->fresh()->status)->toBe(ContactStatus::Replied);
});

test('the content area opens every editor', function () {
    $this->actingAs($this->admin)->get(route('admin.content.index'))
        ->assertOk()
        ->assertSee('Gallery')
        ->assertSee('Journal')
        ->assertSee('Pages');

    $this->actingAs($this->admin)->get(route('admin.content.gallery.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.content.gallery.create'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.content.posts.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.content.posts.create'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.content.pages.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.content.pages.create'))->assertOk();
});
