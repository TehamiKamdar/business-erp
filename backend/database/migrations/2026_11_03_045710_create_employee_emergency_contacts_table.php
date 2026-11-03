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
        Schema::create('employee_emergency_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('relationship', 100);
            $table->string('phone', 30);

            $table->string('email')->nullable();
            $table->text('address')->nullable();

            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->index('employee_id', 'emp_emergency_profile_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_emergency_contacts');
    }
};
