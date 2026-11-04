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
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreignUuid('canceled_by')
                ->nullable()
                ->after('rejection_reason')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('canceled_at')
                ->nullable()
                ->after('canceled_by');

            $table->text('cancellation_reason')
                ->nullable()
                ->after('canceled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['canceled_by']);

            $table->dropColumn([
                'canceled_by',
                'canceled_at',
                'cancellation_reason',
            ]);
        });
    }
};
