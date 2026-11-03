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
        Schema::create('employee_branch_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->foreignUuid('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->string('action', 30);
            // assigned, removed

            $table->date('effective_from');
            $table->date('effective_until')->nullable();

            $table->foreignUuid('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(
                ['employee_id', 'branch_id'],
                'emp_branch_history_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_branch_histories');
    }
};
