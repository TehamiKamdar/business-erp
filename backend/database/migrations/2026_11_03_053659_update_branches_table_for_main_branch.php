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
        Schema::table('branches', function (Blueprint $table) {
            $table->boolean('is_main_branch')
                ->default(false)
                ->after('code');

            $table->unique(
                ['company_id', 'code'],
                'branches_company_code_unique'
            );

            $table->unique(
                ['company_id', 'name'],
                'branches_company_name_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique('branches_company_code_unique');
            $table->dropUnique('branches_company_name_unique');

            $table->dropColumn('is_main_branch');
        });
    }
};
