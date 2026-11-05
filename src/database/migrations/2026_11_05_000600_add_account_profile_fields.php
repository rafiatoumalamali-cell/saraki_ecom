<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path', 255)->nullable()->after('phone');
        });

        Schema::create('customer_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->decimal('height_cm', 5, 2)->nullable();

            foreach (['chest_cm', 'underbust_cm', 'waist_cm', 'hips_cm', 'shoulder_cm',
                'back_width_cm', 'neck_cm', 'arm_length_cm', 'sleeve_length_cm', 'bicep_cm'] as $column) {
                $table->decimal($column, 5, 2)->nullable();
            }

            foreach (['inseam_cm', 'thigh_cm', 'knee_cm', 'calf_cm'] as $column) {
                $table->decimal($column, 5, 2)->nullable();
            }

            $table->decimal('dress_length_cm', 5, 2)->nullable();

            $table->enum('fit_preference', ['slim', 'regular', 'relaxed'])->nullable();
            $table->date('taken_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_measurements');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('avatar_path'));
    }
};
