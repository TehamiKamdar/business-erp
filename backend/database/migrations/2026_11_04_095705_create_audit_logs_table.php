<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('company_id')->nullable();
            $table->uuid('branch_id')->nullable();

            $table->nullableUuidMorphs('actor');
            $table->nullableUuidMorphs('auditable');

            $table->string('action', 100);

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->text('description')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['company_id', 'created_at'],
                'audit_company_created_index'
            );

            $table->index(
                ['branch_id', 'created_at'],
                'audit_branch_created_index'
            );

            $table->index(
                ['action', 'created_at'],
                'audit_action_created_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
