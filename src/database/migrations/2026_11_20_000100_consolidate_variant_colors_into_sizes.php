<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Clients pick a size, not a colour. The catalogue was seeded as a
 * size × colour matrix; this folds each colour pair into a single
 * size variant. The kept row inherits the summed stock and loses its
 * colour tag so the storefront label reads "M" instead of "M / Noir".
 * The retired rows stay in the table (deactivated) so order history
 * keeps pointing at a real variant.
 */
return new class extends Migration
{
    public function up(): void
    {
        $groups = DB::table('product_variants')
            ->where('color', '!=', 'Default')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($variant) => $variant->product_id.':'.$variant->size);

        foreach ($groups as $variants) {
            $keep = $variants->first();

            DB::table('product_variants')->where('id', $keep->id)->update([
                'color' => 'Default',
                'stock_qty' => $variants->sum('stock_qty'),
                'is_active' => true,
                'updated_at' => now(),
            ]);

            DB::table('product_variants')
                ->whereIn('id', $variants->slice(1)->pluck('id'))
                ->update(['is_active' => false, 'stock_qty' => 0, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Colour variants cannot be reconstructed once merged.
    }
};
