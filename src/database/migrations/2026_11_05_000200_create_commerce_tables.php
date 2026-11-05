<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ORDER_STATUSES = ['pending', 'confirmed', 'in_production', 'ready', 'delivered', 'cancelled'];

    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('session_id', 100)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->index()->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();

            $table->unique(['cart_id', 'variant_id']);
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->index()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->restrictOnDelete();
            $table->string('order_number', 32)->unique();
            $table->enum('status', self::ORDER_STATUSES)->default('pending')->index();
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'failed', 'refunded', 'partially_refunded'])->default('unpaid')->index();
            $table->enum('payment_method', ['paystack', 'cod', 'transfer'])->default('paystack')->index();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->char('currency', 3)->default('GHS');
            $table->string('delivery_name', 120);
            $table->string('delivery_phone', 30);
            $table->string('delivery_email', 191)->nullable();
            $table->string('delivery_address');
            $table->string('delivery_city', 120);
            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->index()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name', 180);
            $table->string('variant_label', 120);
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('status', self::ORDER_STATUSES);
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['order_id', 'created_at']);
        });

        DB::statement('ALTER TABLE cart_items ADD CONSTRAINT chk_cart_items_qty CHECK (quantity >= 1)');
        DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_amounts CHECK (subtotal >= 0 AND shipping_fee >= 0 AND total >= 0)');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_values CHECK (quantity >= 1 AND unit_price >= 0 AND line_total >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
