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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignUuid('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('employee_code', 50);

            $table->foreignUuid('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignUuid('designation_id')
                ->nullable()
                ->constrained('designations')
                ->nullOnDelete();

            $table->string('first_name');
            $table->string('last_name')->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('gender', 30)->nullable();
            $table->date('date_of_birth')->nullable();

            $table->string('nationality', 100)->nullable();
            $table->string('national_id', 100)->nullable();

            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 30)->nullable();
            $table->string('country', 100)->nullable();

            $table->string('profile_photo')->nullable();

            $table->date('joining_date')->nullable();

            $table->string('employment_type', 50)
                ->default('full_time');

            $table->string('employment_status', 50)
                ->default('active');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['company_id', 'employee_code'],
                'emp_company_code_uq'
            );
            
            $table->index('department_id', 'emp_profile_dept_idx');
            $table->index('designation_id', 'emp_profile_desg_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
