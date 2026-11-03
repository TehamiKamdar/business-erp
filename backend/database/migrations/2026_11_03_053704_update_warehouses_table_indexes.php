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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->unique(
                ['branch_id', 'code'],
                'warehouses_branch_code_unique'
            );

            $table->unique(
                ['branch_id', 'name'],
                'warehouses_branch_name_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropUnique('warehouses_branch_code_unique');
            $table->dropUnique('warehouses_branch_name_unique');
        });
    }
};
