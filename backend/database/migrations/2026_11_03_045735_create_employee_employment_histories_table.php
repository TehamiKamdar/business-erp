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
        Schema::create('employee_employment_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->foreignUuid('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignUuid('designation_id')
                ->nullable()
                ->constrained('designations')
                ->nullOnDelete();

            $table->string('employment_type', 50)->nullable();

            $table->date('effective_from');
            $table->date('effective_until')->nullable();

            $table->string('change_type', 50);
            // joining, promotion, transfer, department_change, designation_change

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->foreignUuid('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['employee_id', 'effective_from'],
                'emp_history_profile_date_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_employment_histories');
    }
};
