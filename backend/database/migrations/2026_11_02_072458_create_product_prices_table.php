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
        Schema::create('product_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('product_unit_id')->constrained('product_units')->cascadeOnDelete();
            
            $table->string('price_type', 50);
            $table->decimal('price', 15, 2);

            $table->decimal('minimum_quantity', 15, 2);

            $table->date('effective_from');

            $table->string('status', 50)->default('active');

            $table->timestamps();

            $table->unique([
                'product_unit_id',
                'price_type',
                'minimum_quantity'
            ],'prod_price_unit_type_qty_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
