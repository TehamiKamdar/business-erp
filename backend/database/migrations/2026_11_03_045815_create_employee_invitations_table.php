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
        Schema::create('employee_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('email');

            $table->foreignUuid('invited_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignUuid('accepted_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('token_hash', 255)->unique();

            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->unsignedInteger('resend_count')->default(0);

            $table->string('status', 30)->default('pending');
            // pending, accepted, expired, revoked

            $table->timestamps();

            $table->index(['company_id', 'email'], 'emp_invite_company_email_idx');
            $table->index(['company_id', 'status'], 'emp_invite_company_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_invitations');
    }
};
