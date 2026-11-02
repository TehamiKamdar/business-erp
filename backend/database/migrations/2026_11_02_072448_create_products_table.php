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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignUuid('base_unit_id')->constrained('units')->restrictOnDelete();

            $table->string('name');
            $table->string('sku');
            $table->string('barcode')->nullable();

            $table->text('description')->nullable();

            $table->decimal('reorder_level', 15, 2)->default(0);

            $table->string('status', 50)->default('active');

            $table->timestamps();

            $table->unique(['company_id', 'sku']);
            $table->unique(['company_id', 'barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
