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
        Schema::create('stock_transfer_receipt_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('stock_transfer_receipt_id')
                ->constrained('stock_transfer_receipts')
                ->restrictOnDelete();

            $table->foreignUuid('stock_transfer_item_id')
                ->constrained('stock_transfer_items')
                ->restrictOnDelete();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->decimal('received_quantity', 20, 8);

            $table->decimal('conversion_factor', 20, 8);

            $table->decimal('base_quantity', 20, 8);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique([
                'stock_transfer_receipt_id',
                'stock_transfer_item_id'
            ],'transfer_receipt_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_receipt_items');
    }
};
