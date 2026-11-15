<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('thumb_path')->nullable()->after('path');
        });

        Schema::table('gallery_items', function (Blueprint $table) {
            $table->string('thumb_path')->nullable()->after('image_path');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('cover_thumb')->nullable()->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('thumb_path');
        });

        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropColumn('thumb_path');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('cover_thumb');
        });
    }
};
