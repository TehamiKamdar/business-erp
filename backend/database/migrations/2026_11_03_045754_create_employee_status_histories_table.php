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
        Schema::create('employee_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);

            $table->date('effective_date');

            $table->text('reason')->nullable();

            $table->foreignUuid('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['employee_id', 'effective_date'],
                'emp_status_profile_date_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_status_histories');
    }
};
