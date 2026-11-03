<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            // Pehle replacement index create karo
            $table->unique([
                'product_unit_id',
                'price_type',
                'minimum_quantity',
                'effective_from',
            ], 'prod_price_unit_type_qty_date_unique');

            // Phir old index remove karo
            $table->dropUnique('prod_price_unit_type_qty_unique');
        });
    }

    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            // Pehle old index restore karo
            $table->unique([
                'product_unit_id',
                'price_type',
                'minimum_quantity',
            ], 'prod_price_unit_type_qty_unique');

            // Phir new index remove karo
            $table->dropUnique('prod_price_unit_type_qty_date_unique');
        });
    }
};
