<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index()->constrained()->restrictOnDelete();
            $table->enum('provider', ['paystack', 'offline'])->default('paystack');
            $table->string('reference', 100)->unique();
            $table->string('provider_reference', 100)->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('GHS');
            $table->enum('channel', ['card', 'mobile_money', 'bank_transfer', 'ussd', 'cash', 'transfer'])->nullable();
            $table->enum('status', ['initialized', 'pending', 'success', 'failed', 'abandoned', 'refunded', 'partially_refunded'])->default('initialized')->index();
            $table->timestamp('paid_at')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('provider', 30)->default('paystack');
            $table->string('event_id', 100)->unique();
            $table->string('event_type', 100);
            $table->string('reference', 100)->index();
            $table->json('payload');
            $table->boolean('signature_valid')->default(false);
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->index()->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('reason')->nullable();
            $table->string('provider_reference', 100)->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending')->index();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_at');
        });

        DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payments_amount CHECK (amount >= 0)');
        DB::statement('ALTER TABLE refunds ADD CONSTRAINT chk_refunds_amount CHECK (amount > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
    }
};
