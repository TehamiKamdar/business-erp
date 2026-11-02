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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('source_warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignUuid('destination_warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->string('transfer_number');

            $table->string('status')->default('draft');
            // draft, dispatched, partially_received, received, cancelled

            $table->text('notes')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('dispatched_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['source_warehouse_id', 'transfer_number'],
                'stock_transfer_number_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
