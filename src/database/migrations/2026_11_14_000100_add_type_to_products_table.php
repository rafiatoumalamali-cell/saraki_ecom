<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // ready_to_wear sells through the cart; made_to_measure is a
            // tailoring request — the client picks a model and chooses
            // SARAKI fabric or brings their own.
            $table->string('type', 20)->default('ready_to_wear')->after('category_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
