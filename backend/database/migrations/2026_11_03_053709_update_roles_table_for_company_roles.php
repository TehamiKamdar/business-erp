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
        Schema::table('roles', function (Blueprint $table) {
            // Remove existing foreign key
            $table->dropForeign(['company_id']);

            // Remove old unique indexes
            $table->dropUnique('roles_company_id_unique');
            $table->dropUnique('roles_slug_unique');

            // Add company-specific unique slug
            $table->unique(
                ['company_id', 'slug'],
                'roles_company_slug_unique'
            );

            // Restore foreign key
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['company_id']);

            $table->dropUnique('roles_company_slug_unique');

            $table->unique('company_id', 'roles_company_id_unique');
            $table->unique('slug', 'roles_slug_unique');

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();
        });
    }
};
