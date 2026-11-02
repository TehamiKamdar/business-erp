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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignUuid('warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->nullOnDelete();

            $table->string('request_number');
            $table->date('request_date');
            $table->date('required_date')->nullable();

            $table->string('priority')->default('normal');
            $table->string('status')->default('draft');
            // draft, pending, approved, rejected, converted, cancelled

            $table->text('notes')->nullable();

            $table->foreignUuid('requested_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['branch_id', 'request_number'],
                'purchase_request_number_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
