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
        Schema::create('supplier_product_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('supplier_id')
                ->constrained('suppliers')
                ->cascadeOnDelete();

            $table->foreignUuid('product_unit_id')
                ->constrained('product_units')
                ->cascadeOnDelete();

            $table->decimal('purchase_price', 15, 2);
            $table->string('currency', 3)->default('PKR');

            $table->decimal('min_quantity', 20, 8)->default(1);

            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->index(
                ['supplier_id', 'product_unit_id', 'status'],
                'supplier_product_price_lookup_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_product_prices');
    }
};
