<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Sur-mesure pricing is a breakdown, not a single price:
            // fabric (per meter or per pagne) + the tailor's making fee.
            $table->decimal('fabric_price', 10, 2)->nullable()->after('base_price');
            $table->string('fabric_unit', 10)->nullable()->after('fabric_price'); // meter | pagne
            $table->decimal('confection_price', 10, 2)->nullable()->after('fabric_unit');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['fabric_price', 'fabric_unit', 'confection_price']);
        });
    }
};
