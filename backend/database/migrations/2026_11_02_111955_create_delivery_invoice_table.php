
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
        | 1. Customers
        |--------------------------------------------------------------------------
        */

        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('customer_code');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('tax_number')->nullable();

            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->nullable();

            $table->unsignedInteger('payment_terms_days')->default(0);

            $table->string('status')->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['company_id', 'customer_code'],
                'customer_company_code_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Customer Addresses
        |--------------------------------------------------------------------------
        */

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->string('type')->default('billing');

            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();

            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();

            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();

            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Quotations
        |--------------------------------------------------------------------------
        */

        Schema::create('quotations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignUuid('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignUuid('warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->nullOnDelete();

            $table->string('quotation_number');

            $table->date('quotation_date');
            $table->date('valid_until')->nullable();

            $table->string('status')->default('draft');

            $table->char('currency', 3)->default('PKR');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['branch_id', 'quotation_number'],
                'quotation_branch_number_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Quotation Items
        |--------------------------------------------------------------------------
        */

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('quotation_id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->decimal('quantity', 20, 8);
            $table->decimal('conversion_factor', 20, 8)->default(1);
            $table->decimal('base_quantity', 20, 8);

            $table->decimal('unit_price', 15, 2);

            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | 5. Sales Orders
        |--------------------------------------------------------------------------
        */

        Schema::create('sales_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignUuid('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignUuid('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignUuid('quotation_id')
                ->nullable()
                ->constrained('quotations')
                ->nullOnDelete();

            $table->string('order_number');

            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();

            $table->string('status')->default('draft');

            $table->char('currency', 3)->default('PKR');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['branch_id', 'order_number'],
                'sales_order_branch_number_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 6. Sales Order Items
        |--------------------------------------------------------------------------
        */

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('sales_order_id')
                ->constrained('sales_orders')
                ->cascadeOnDelete();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->decimal('quantity', 20, 8);
            $table->decimal('conversion_factor', 20, 8)->default(1);
            $table->decimal('base_quantity', 20, 8);

            $table->decimal('delivered_quantity', 20, 8)->default(0);

            $table->decimal('unit_price', 15, 2);

            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | 7. Sales Order Status Histories
        |--------------------------------------------------------------------------
        */

        Schema::create('sales_order_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('sales_order_id')
                ->constrained('sales_orders')
                ->cascadeOnDelete();

            $table->string('old_status')->nullable();
            $table->string('new_status');

            $table->foreignUuid('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | 8. Deliveries
        |--------------------------------------------------------------------------
        */

        Schema::create('deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('sales_order_id')
                ->constrained('sales_orders')
                ->restrictOnDelete();

            $table->foreignUuid('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->string('delivery_number');
            $table->date('delivery_date');

            $table->string('status')->default('draft');

            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->string('received_by_name')->nullable();

            $table->text('notes')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['warehouse_id', 'delivery_number'],
                'delivery_warehouse_number_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 9. Delivery Items
        |--------------------------------------------------------------------------
        */

        Schema::create('delivery_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('delivery_id')
                ->constrained('deliveries')
                ->cascadeOnDelete();

            $table->foreignUuid('sales_order_item_id')
                ->constrained('sales_order_items')
                ->restrictOnDelete();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->decimal('quantity', 20, 8);
            $table->decimal('conversion_factor', 20, 8)->default(1);
            $table->decimal('base_quantity', 20, 8);

            $table->text('notes')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | 10. Delivery Status Histories
        |--------------------------------------------------------------------------
        */

        Schema::create('delivery_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('delivery_id')
                ->constrained('deliveries')
                ->cascadeOnDelete();

            $table->string('old_status')->nullable();
            $table->string('new_status');

            $table->foreignUuid('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | 11. Sales Invoices
        |--------------------------------------------------------------------------
        */

        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignUuid('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignUuid('sales_order_id')
                ->nullable()
                ->constrained('sales_orders')
                ->nullOnDelete();

            $table->string('invoice_number');

            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            $table->string('status')->default('draft');

            $table->char('currency', 3)->default('PKR');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['branch_id', 'invoice_number'],
                'sales_invoice_branch_number_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 12. Sales Invoice Items
        |--------------------------------------------------------------------------
        */

        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('sales_invoice_id')
                ->constrained('sales_invoices')
                ->cascadeOnDelete();

            $table->foreignUuid('delivery_item_id')
                ->nullable()
                ->constrained('delivery_items')
                ->nullOnDelete();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->decimal('quantity', 20, 8);
            $table->decimal('conversion_factor', 20, 8)->default(1);
            $table->decimal('base_quantity', 20, 8);

            $table->decimal('unit_price', 15, 2);

            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | 13. Delivery Invoice Pivot
        |--------------------------------------------------------------------------
        */

        Schema::create('delivery_invoice', function (Blueprint $table) {
            $table->foreignUuid('delivery_id')
                ->constrained('deliveries')
                ->cascadeOnDelete();

            $table->foreignUuid('sales_invoice_id')
                ->constrained('sales_invoices')
                ->cascadeOnDelete();

            $table->primary(['delivery_id', 'sales_invoice_id']);
        });

        /*
        |--------------------------------------------------------------------------
        | 14. Sales Returns
        |--------------------------------------------------------------------------
        */

        Schema::create('sales_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignUuid('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignUuid('sales_invoice_id')
                ->nullable()
                ->constrained('sales_invoices')
                ->nullOnDelete();

            $table->string('return_number');
            $table->date('return_date');

            $table->string('status')->default('draft');

            $table->text('reason')->nullable();

            $table->decimal('total_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['warehouse_id', 'return_number'],
                'sales_return_warehouse_number_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | 15. Sales Return Items
        |--------------------------------------------------------------------------
        */

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('sales_return_id')
                ->constrained('sales_returns')
                ->cascadeOnDelete();

            $table->foreignUuid('sales_invoice_item_id')
                ->nullable()
                ->constrained('sales_invoice_items')
                ->nullOnDelete();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignUuid('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->decimal('quantity', 20, 8);
            $table->decimal('conversion_factor', 20, 8)->default(1);
            $table->decimal('base_quantity', 20, 8);

            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->text('reason')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | 16. Customer Prices
        |--------------------------------------------------------------------------
        */

        Schema::create('customer_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignUuid('product_unit_id')
                ->constrained('product_units')
                ->cascadeOnDelete();

            $table->decimal('price', 15, 2);

            $table->decimal('min_quantity', 20, 8)->default(1);

            $table->char('currency', 3)->default('PKR');

            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->index(
                ['customer_id', 'product_unit_id', 'status'],
                'customer_price_lookup_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_prices');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('delivery_invoice');
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('delivery_status_histories');
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('sales_order_status_histories');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
