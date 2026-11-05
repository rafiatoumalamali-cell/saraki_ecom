<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->string('name_fr', 120);
            $table->string('name_en', 120);
            $table->unsignedSmallInteger('duration_min');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week')->unique();
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->id();
            $table->date('blocked_date')->unique();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->index()->constrained('appointment_services')->restrictOnDelete();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'approved', 'rescheduled', 'cancelled', 'completed'])->default('pending')->index();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['appointment_date', 'start_time']);
            $table->index(['appointment_date', 'status']);
        });

        DB::statement('ALTER TABLE appointment_services ADD CONSTRAINT chk_services_duration CHECK (duration_min > 0)');
        DB::statement('ALTER TABLE working_hours ADD CONSTRAINT chk_working_hours_day CHECK (day_of_week BETWEEN 0 AND 6)');
        DB::statement('ALTER TABLE working_hours ADD CONSTRAINT chk_working_hours_range CHECK (is_closed = 1 OR (open_time IS NOT NULL AND close_time IS NOT NULL AND close_time > open_time))');
        DB::statement('ALTER TABLE appointments ADD CONSTRAINT chk_appointments_range CHECK (end_time > start_time)');
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('blocked_dates');
        Schema::dropIfExists('working_hours');
        Schema::dropIfExists('appointment_services');
    }
};
