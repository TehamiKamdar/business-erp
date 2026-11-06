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
        Schema::create('stock_transfer_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('stock_transfer_id')
                ->constrained('stock_transfers')
                ->restrictOnDelete();

            $table->string('receipt_number');

            $table->timestamp('received_at');

            $table->uuid('received_by')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['stock_transfer_id', 'receipt_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_receipts');
    }
};
