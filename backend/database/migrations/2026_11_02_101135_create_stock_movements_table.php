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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->string('movement_type');
            // opening, purchase, sale, adjustment, transfer, return

            $table->string('direction'); // in, out

            $table->decimal('quantity', 20, 8);
            $table->decimal('conversion_factor', 20, 8);
            $table->decimal('base_quantity', 20, 8);

            $table->decimal('balance_after', 20, 8);

            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();

            $table->text('notes')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['reference_type', 'reference_id'],
                'stock_movement_reference_index'
            );

            $table->index(
                ['warehouse_id', 'product_id', 'created_at'],
                'stock_movement_product_history_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
