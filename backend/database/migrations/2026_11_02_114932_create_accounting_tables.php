
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fiscal Years
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->string('status')->default('open'); // open, closed

            $table->timestamps();

            $table->unique(['company_id', 'name'], 'fy_comp_name_uq');
            $table->index(['company_id', 'status'], 'fy_comp_status_idx');
        });

        // 2. Accounting Periods
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('fiscal_year_id')
                ->constrained('fiscal_years')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('period_number');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('open'); // open, closed, locked

            $table->timestamps();

            $table->unique(
                ['fiscal_year_id', 'period_number'],
                'ap_fy_period_uq'
            );

            $table->index(['fiscal_year_id', 'status'], 'ap_fy_status_idx');
        });

        // 3. Chart of Accounts
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignUuid('parent_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->nullOnDelete();

            $table->string('account_code', 30);
            $table->string('name');

            // asset, liability, equity, income, expense
            $table->string('account_type');

            // Example: current_asset, fixed_asset, revenue, operating_expense
            $table->string('account_subtype')->nullable();

            // debit or credit
            $table->string('normal_balance');

            $table->boolean('is_control_account')->default(false);
            $table->boolean('allow_manual_entries')->default(true);
            $table->boolean('status')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(
                ['company_id', 'account_code'],
                'coa_comp_code_uq'
            );

            $table->index(['company_id', 'account_type'], 'coa_comp_type_idx');
            $table->index('parent_id', 'coa_parent_idx');
        });

        // 4. Journal Entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignUuid('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            $table->foreignUuid('fiscal_year_id')
                ->constrained('fiscal_years')
                ->restrictOnDelete();

            $table->foreignUuid('accounting_period_id')
                ->constrained('accounting_periods')
                ->restrictOnDelete();

            $table->string('entry_number');
            $table->date('entry_date');

            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();

            // Source document, e.g. invoice, payment, expense
            $table->string('source_type')->nullable();
            $table->uuid('source_id')->nullable();

            // draft, posted, reversed
            $table->string('status')->default('draft');

            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('posted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['company_id', 'entry_number'],
                'je_comp_number_uq'
            );

            $table->index(['company_id', 'entry_date'], 'je_comp_date_idx');
            $table->index(['company_id', 'status'], 'je_comp_status_idx');
            $table->index(['source_type', 'source_id'], 'je_source_idx');
        });

        // 5. Journal Entry Lines
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('journal_entry_id')
                ->constrained('journal_entries')
                ->cascadeOnDelete();

            $table->foreignUuid('chart_of_account_id')
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();

            $table->unsignedInteger('line_number')->default(1);

            $table->string('description')->nullable();

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(
                ['journal_entry_id', 'line_number'],
                'jel_entry_line_uq'
            );

            $table->index('chart_of_account_id', 'jel_account_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('accounting_periods');
        Schema::dropIfExists('fiscal_years');
    }
};
