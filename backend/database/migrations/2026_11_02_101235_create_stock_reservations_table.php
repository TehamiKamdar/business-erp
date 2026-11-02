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
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->string('reference_type');
            $table->uuid('reference_id');

            $table->decimal('quantity', 20, 8);

            $table->string('status')->default('active');
            // active, released, fulfilled, cancelled

            $table->text('notes')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['reference_type', 'reference_id'],
                'stock_reservation_reference_index'
            );

            $table->index(
                ['warehouse_id', 'product_id', 'status'],
                'stock_reservation_product_status_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
