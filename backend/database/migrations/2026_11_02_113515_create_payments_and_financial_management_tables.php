
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Cash & Bank Accounts
        |--------------------------------------------------------------------------
        */

        Schema::create('cash_bank_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignUuid('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            $table->string('account_name');
            $table->string('account_type'); // cash, bank

            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_title')->nullable();
            $table->string('iban')->nullable();

            $table->char('currency', 3)->default('PKR');

            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);

            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(
                ['company_id', 'branch_id', 'status'],
                'cash_bank_account_lookup_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Payment Methods
        |--------------------------------------------------------------------------
        */

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('type'); // cash, bank, cheque, online

            $table->boolean('requires_reference')->default(false);
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(
                ['company_id', 'name'],
                'payment_method_company_name_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Customer Payments
        |--------------------------------------------------------------------------
        */

        Schema::create('customer_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignUuid('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignUuid('cash_bank_account_id')
                ->nullable()
                ->constrained('cash_bank_accounts')
                ->nullOnDelete();

            $table->foreignUuid('payment_method_id')
                ->nullable()
                ->constrained('payment_methods')
                ->nullOnDelete();

            $table->string('receipt_number');

            $table->date('payment_date');

            $table->decimal('amount', 15, 2);

            $table->char('currency', 3)->default('PKR');

            $table->string('reference_number')->nullable();

            $table->string('status')->default('draft');
            // draft, posted, cancelled

            $table->text('notes')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['branch_id', 'receipt_number'],
                'customer_payment_branch_receipt_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Customer Payment Allocations
        |--------------------------------------------------------------------------
        */

        Schema::create('customer_payment_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_payment_id')
                ->constrained('customer_payments')
                ->cascadeOnDelete();

            $table->foreignUuid('sales_invoice_id')
                ->constrained('sales_invoices')
                ->restrictOnDelete();

            $table->decimal('allocated_amount', 15, 2);

            $table->timestamps();

            $table->unique(
                ['customer_payment_id', 'sales_invoice_id'],
                'customer_payment_invoice_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 5. Supplier Payments
        |--------------------------------------------------------------------------
        */

        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->foreignUuid('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignUuid('cash_bank_account_id')
                ->nullable()
                ->constrained('cash_bank_accounts')
                ->nullOnDelete();

            $table->foreignUuid('payment_method_id')
                ->nullable()
                ->constrained('payment_methods')
                ->nullOnDelete();

            $table->string('payment_number');

            $table->date('payment_date');

            // Actual cash/bank amount paid
            $table->decimal('cash_amount', 15, 2)->default(0);

            // Value of goods/assets given to supplier
            $table->decimal('trade_in_amount', 15, 2)->default(0);

            // Total supplier settlement
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->char('currency', 3)->default('PKR');

            $table->string('reference_number')->nullable();

            $table->string('status')->default('draft');
            // draft, posted, cancelled

            $table->text('notes')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['branch_id', 'payment_number'],
                'supplier_payment_branch_number_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 6. Supplier Payment Allocations
        |--------------------------------------------------------------------------
        */

        Schema::create('supplier_payment_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('supplier_payment_id')
                ->constrained('supplier_payments')
                ->cascadeOnDelete();

            $table->foreignUuid('supplier_invoice_id')
                ->constrained('supplier_invoices')
                ->restrictOnDelete();

            $table->decimal('allocated_amount', 15, 2);

            $table->timestamps();

            $table->unique(
                ['supplier_payment_id', 'supplier_invoice_id'],
                'supplier_payment_invoice_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 7. Supplier Payment Trade-ins
        |--------------------------------------------------------------------------
        */

        Schema::create('supplier_payment_trade_ins', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('supplier_payment_id')
                ->constrained('supplier_payments')
                ->cascadeOnDelete();

            $table->foreignUuid('product_id')
                ->nullable()
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignUuid('warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->nullable()
                ->constrained('units')
                ->restrictOnDelete();

            $table->string('item_type')->default('stock');
            // stock, asset

            $table->string('description')->nullable();

            $table->decimal('quantity', 20, 8)->default(0);
            $table->decimal('conversion_factor', 20, 8)->default(1);
            $table->decimal('base_quantity', 20, 8)->default(0);

            // Existing book cost per entered unit
            $table->decimal('book_unit_cost', 15, 2)->default(0);

            // Value agreed with supplier per entered unit
            $table->decimal('agreed_unit_value', 15, 2)->default(0);

            $table->decimal('total_book_value', 15, 2)->default(0);
            $table->decimal('total_agreed_value', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | 8. Expense Categories
        |--------------------------------------------------------------------------
        */

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('description')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(
                ['company_id', 'name'],
                'expense_category_company_name_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 9. Expenses
        |--------------------------------------------------------------------------
        */

        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignUuid('expense_category_id')
                ->constrained('expense_categories')
                ->restrictOnDelete();

            $table->foreignUuid('cash_bank_account_id')
                ->nullable()
                ->constrained('cash_bank_accounts')
                ->nullOnDelete();

            $table->foreignUuid('payment_method_id')
                ->nullable()
                ->constrained('payment_methods')
                ->nullOnDelete();

            $table->string('expense_number');

            $table->date('expense_date');

            $table->string('title');

            $table->decimal('amount', 15, 2);

            $table->char('currency', 3)->default('PKR');

            $table->string('reference_number')->nullable();

            $table->string('status')->default('draft');
            // draft, posted, cancelled

            $table->text('description')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['branch_id', 'expense_number'],
                'expense_branch_number_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 10. Cash & Bank Transactions
        |--------------------------------------------------------------------------
        */

        Schema::create('cash_bank_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('cash_bank_account_id')
                ->constrained('cash_bank_accounts')
                ->restrictOnDelete();

            $table->foreignUuid('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            $table->date('transaction_date');

            $table->string('transaction_type');
            // customer_receipt, supplier_payment, expense,
            // opening_balance, adjustment

            $table->string('direction');
            // in, out

            $table->decimal('amount', 15, 2);

            $table->char('currency', 3)->default('PKR');

            // Polymorphic source document
            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();

            $table->string('reference_number')->nullable();

            $table->text('description')->nullable();

            $table->decimal('balance_after', 15, 2)->default(0);

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')->nullable();

            $table->timestamps();

            $table->index(
                ['reference_type', 'reference_id'],
                'cash_bank_reference_index'
            );

            $table->index(
                ['cash_bank_account_id', 'transaction_date'],
                'cash_bank_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_bank_transactions');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('supplier_payment_trade_ins');
        Schema::dropIfExists('supplier_payment_allocations');
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('customer_payment_allocations');
        Schema::dropIfExists('customer_payments');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('cash_bank_accounts');
    }
};
