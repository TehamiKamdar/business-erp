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
        Schema::create('product_units', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete();

            $table->decimal('conversion_factor', 15, 2);

            $table->boolean('is_purchase_unit')->default(false);
            $table->boolean('is_sale_unit')->default(false);

            $table->string('status', 50)->default('active');

            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
