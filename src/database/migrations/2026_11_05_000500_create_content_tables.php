<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('title_fr', 180);
            $table->string('title_en', 180);
            $table->enum('category', ['collection', 'event', 'completed_design'])->default('collection');
            $table->string('image_path');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_published', 'sort_order']);
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->index()->constrained('users')->restrictOnDelete();
            $table->string('title_fr', 200);
            $table->string('title_en', 200);
            $table->string('slug', 220)->unique();
            $table->string('excerpt_fr', 500)->nullable();
            $table->string('excerpt_en', 500)->nullable();
            $table->longText('body_fr');
            $table->longText('body_en');
            $table->string('cover_image')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 160)->unique();
            $table->string('title_fr', 180);
            $table->string('title_en', 180);
            $table->longText('body_fr');
            $table->longText('body_en');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 191)->index();
            $table->string('phone', 30)->nullable();
            $table->string('subject', 180)->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'read', 'replied', 'archived'])->default('new');
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->default('general')->index();
            $table->string('key', 100)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('gallery_items');
    }
};
