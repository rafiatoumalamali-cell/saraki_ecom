<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->index()->constrained('categories')->nullOnDelete();
            $table->string('name_fr', 120);
            $table->string('name_en', 120);
            $table->string('slug', 160)->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->index()->constrained()->restrictOnDelete();
            $table->string('name_fr', 180);
            $table->string('name_en', 180);
            $table->string('slug', 200)->unique();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->string('base_sku', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'is_featured']);
            $table->fullText(['name_fr', 'name_en', 'description_fr', 'description_en']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size', 40)->default('Default');
            $table->string('color', 60)->default('Default');
            $table->string('sku', 64)->unique();
            $table->unsignedInteger('stock_qty')->default(0);
            $table->decimal('price_delta', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'size', 'color']);
            $table->index(['product_id', 'is_active']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt_fr')->nullable();
            $table->string('alt_en')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
            $table->index(['product_id', 'is_primary']);
        });

        DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_base_price CHECK (base_price >= 0)');
        DB::statement('ALTER TABLE product_variants ADD CONSTRAINT chk_variants_stock CHECK (stock_qty >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
