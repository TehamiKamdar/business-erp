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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->string('document_type', 100);
            $table->string('document_name');

            $table->string('document_number', 100)->nullable();

            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();

            $table->text('notes')->nullable();

            $table->foreignUuid('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('employee_id', 'emp_doc_profile_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
