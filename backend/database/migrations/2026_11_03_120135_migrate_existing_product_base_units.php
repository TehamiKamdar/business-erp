<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('products')
            ->whereNotNull('base_unit_id')
            ->orderBy('id')
            ->chunk(100, function ($products) {
                foreach ($products as $product) {
                    DB::table('product_units')->updateOrInsert(
                        [
                            'product_id' => $product->id,
                            'unit_id' => $product->base_unit_id,
                        ],
                        [
                            'id' => (string) Str::uuid7(),
                            'conversion_factor' => 1,
                            'is_base_unit' => true,
                            'is_purchase_unit' => false,
                            'is_sale_unit' => true,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });
    }

    public function down(): void
    {
        DB::table('product_units')
            ->where('is_base_unit', true)
            ->delete();
    }
};
