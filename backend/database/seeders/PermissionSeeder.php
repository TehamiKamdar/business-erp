<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [];

        /*
        |--------------------------------------------------------------------------
        | Permission Generator
        |--------------------------------------------------------------------------
        */

        $add = function (
            string $module,
            string $resource,
            array $actions
        ) use (&$permissions): void {

            foreach ($actions as $action) {
                $slug = "{$resource}.{$action}";

                $actionLabel = match ($action) {
                    'view' => 'View',
                    'view_all' => 'View All',
                    'create' => 'Create',
                    'edit' => 'Edit',
                    'delete' => 'Delete',
                    'deactivate' => 'Deactivate',
                    'activate' => 'Activate',
                    'archive' => 'Archive',
                    'restore' => 'Restore',
                    'export' => 'Export',
                    'print' => 'Print',
                    'submit' => 'Submit',
                    'approve' => 'Approve',
                    'reject' => 'Reject',
                    'cancel' => 'Cancel',
                    'post' => 'Post',
                    'reverse' => 'Reverse',
                    'assign' => 'Assign',
                    'receive' => 'Receive',
                    'convert' => 'Convert',
                    'delegate' => 'Delegate',
                    'withdraw' => 'Withdraw',
                    'configure' => 'Configure',
                    'upload' => 'Upload',
                    'validate' => 'Validate',
                    'import' => 'Import',
                    'manage' => 'Manage',
                    'mark_read' => 'Mark as Read',
                    'apply_discount' => 'Apply Discount',
                    default => Str::headline($action),
                };

                $permissions[] = [
                    'name' => "{$actionLabel} " . Str::headline($resource),
                    'slug' => $slug,
                    'module' => $module,
                ];
            }
        };

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        $add('Dashboard', 'dashboard', [
            'view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Company Management
        |--------------------------------------------------------------------------
        */

        $add('Company Management', 'company', [
            'view',
            'edit',
        ]);

        $add('Branch Management', 'branches', [
            'view',
            'view_all',
            'create',
            'edit',
            'deactivate',
            'activate',
            'archive',
            'restore',
            'export',
        ]);

        $add('Warehouse Management', 'warehouses', [
            'view',
            'create',
            'edit',
            'deactivate',
            'activate',
            'archive',
            'restore',
            'export',
        ]);

        /*
        |--------------------------------------------------------------------------
        | User & Access Control
        |--------------------------------------------------------------------------
        */

        $add('User Management', 'users', [
            'view',
            'create',
            'edit',
            'deactivate',
            'activate',
            'assign',
            'export',
        ]);

        $add('User Management', 'user_invitations', [
            'view',
            'create',
            'cancel',
            'manage',
        ]);

        $add('Access Control', 'roles', [
            'view',
            'create',
            'edit',
            'deactivate',
            'activate',
            'assign',
        ]);

        $add('Access Control', 'permissions', [
            'view',
            'assign',
            'manage',
        ]);

        $add('Access Control', 'user_branches', [
            'view',
            'assign',
            'manage',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Employee Management
        |--------------------------------------------------------------------------
        */

        $employeeResources = [
            'employees',
            'departments',
            'designations',
        ];

        foreach ($employeeResources as $resource) {
            $add('Employee Management', $resource, [
                'view',
                'create',
                'edit',
                'deactivate',
                'activate',
                'export',
                'print',
            ]);
        }

        $add('Employee Management', 'employee_documents', [
            'view',
            'upload',
            'edit',
            'delete',
            'export',
        ]);

        $add('Employee Management', 'employee_invitations', [
            'view',
            'create',
            'cancel',
            'manage',
        ]);

        $add('Employee Management', 'employee_emergency_contacts', [
            'view',
            'create',
            'edit',
            'delete',
        ]);

        $add('Employee Management', 'employee_employment_histories', [
            'view',
            'create',
            'edit',
            'export',
        ]);

        $add('Employee Management', 'employee_status_histories', [
            'view',
            'export',
        ]);

        $add('Employee Management', 'employee_branch_histories', [
            'view',
            'create',
            'export',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Product Catalog
        |--------------------------------------------------------------------------
        */

        $catalogResources = [
            'categories',
            'products',
            'units',
            'brands',
        ];

        foreach ($catalogResources as $resource) {
            $add('Product Catalog', $resource, [
                'view',
                'create',
                'edit',
                'deactivate',
                'activate',
                'archive',
                'restore',
                'export',
                'print',
            ]);
        }

        $add('Product Catalog', 'product_pricing', [
            'view',
            'create',
            'edit',
            'export',
        ]);

        $add('Product Catalog', 'product_attributes', [
            'view',
            'create',
            'edit',
            'deactivate',
        ]);

        $add('Product Catalog', 'product_variants', [
            'view',
            'create',
            'edit',
            'deactivate',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Inventory Management
        |--------------------------------------------------------------------------
        */

        $add('Inventory Management', 'stock_balances', [
            'view',
            'export',
            'print',
        ]);

        $add('Inventory Management', 'stock_movements', [
            'view',
            'export',
            'print',
        ]);

        $add('Inventory Management', 'stock_adjustments', [
            'view',
            'create',
            'edit',
            'submit',
            'approve',
            'reject',
            'cancel',
            'export',
            'print',
        ]);

        $add('Inventory Management', 'stock_transfers', [
            'view',
            'create',
            'edit',
            'submit',
            'approve',
            'reject',
            'receive',
            'cancel',
            'export',
            'print',
        ]);

        $add('Inventory Management', 'stock_reservations', [
            'view',
            'create',
            'edit',
            'cancel',
        ]);

        $add('Inventory Management', 'stock_alerts', [
            'view',
            'manage',
            'export',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Suppliers & Purchasing
        |--------------------------------------------------------------------------
        */

        $add('Purchasing', 'suppliers', [
            'view',
            'create',
            'edit',
            'deactivate',
            'activate',
            'archive',
            'restore',
            'export',
            'print',
        ]);

        $purchaseDocuments = [
            'purchase_requests',
            'supplier_quotations',
            'purchase_orders',
            'goods_receipts',
            'supplier_invoices',
            'purchase_returns',
            'supplier_payments',
        ];

        foreach ($purchaseDocuments as $resource) {
            $add('Purchasing', $resource, [
                'view',
                'create',
                'edit',
                'submit',
                'approve',
                'reject',
                'cancel',
                'export',
                'print',
            ]);
        }

        $add('Purchasing', 'goods_receipts', [
            'receive',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Customers & Sales
        |--------------------------------------------------------------------------
        */

        $add('Sales', 'customers', [
            'view',
            'create',
            'edit',
            'deactivate',
            'activate',
            'archive',
            'restore',
            'export',
            'print',
        ]);

        $salesDocuments = [
            'quotations',
            'sales_orders',
            'deliveries',
            'sales_invoices',
            'customer_payments',
            'sales_returns',
        ];

        foreach ($salesDocuments as $resource) {
            $add('Sales', $resource, [
                'view',
                'create',
                'edit',
                'submit',
                'approve',
                'reject',
                'cancel',
                'export',
                'print',
            ]);
        }

        $add('Sales', 'sales_discounts', [
            'view',
            'create',
            'edit',
            'apply_discount',
            'approve',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Financial Management
        |--------------------------------------------------------------------------
        */

        $financeResources = [
            'cash_bank_accounts',
            'payment_methods',
            'expense_categories',
            'expenses',
            'cash_bank_transactions',
        ];

        foreach ($financeResources as $resource) {
            $add('Financial Management', $resource, [
                'view',
                'create',
                'edit',
                'submit',
                'approve',
                'reject',
                'cancel',
                'export',
                'print',
            ]);
        }

        $add('Financial Management', 'accounts_receivable', [
            'view',
            'export',
            'print',
        ]);

        $add('Financial Management', 'accounts_payable', [
            'view',
            'export',
            'print',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Accounting
        |--------------------------------------------------------------------------
        */

        $add('Accounting', 'chart_of_accounts', [
            'view',
            'create',
            'edit',
            'deactivate',
            'export',
            'print',
        ]);

        $add('Accounting', 'fiscal_years', [
            'view',
            'create',
            'edit',
            'activate',
            'archive',
        ]);

        $add('Accounting', 'journal_entries', [
            'view',
            'create',
            'edit',
            'submit',
            'approve',
            'reject',
            'post',
            'reverse',
            'cancel',
            'export',
            'print',
        ]);

        $add('Accounting', 'general_ledger', [
            'view',
            'export',
            'print',
        ]);

        $add('Accounting', 'trial_balance', [
            'view',
            'export',
            'print',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Approval Workflow
        |--------------------------------------------------------------------------
        */

        $add('Approval Workflow', 'approval_requests', [
            'view',
            'approve',
            'reject',
            'withdraw',
            'delegate',
            'export',
        ]);

        $add('Approval Workflow', 'approval_settings', [
            'view',
            'create',
            'edit',
            'configure',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Audit Trail
        |--------------------------------------------------------------------------
        */

        $add('Audit Trail', 'audit_logs', [
            'view',
            'export',
            'print',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reports & Analytics
        |--------------------------------------------------------------------------
        */

        $reportResources = [
            'financial_reports',
            'inventory_reports',
            'purchasing_reports',
            'sales_reports',
            'customer_reports',
            'supplier_reports',
            'branch_reports',
            'employee_reports',
        ];

        foreach ($reportResources as $resource) {
            $add('Reports & Analytics', $resource, [
                'view',
                'export',
                'print',
            ]);
        }

        $add('Reports & Analytics', 'profit_loss_report', [
            'view',
            'export',
            'print',
        ]);

        $add('Reports & Analytics', 'balance_sheet_report', [
            'view',
            'export',
            'print',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Import & Bulk Data
        |--------------------------------------------------------------------------
        */

        $add('Import & Bulk Data', 'data_imports', [
            'view',
            'upload',
            'validate',
            'import',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Company Settings
        |--------------------------------------------------------------------------
        */

        $add('Company Settings', 'company_settings', [
            'view',
            'edit',
        ]);

        $add('Company Settings', 'financial_settings', [
            'view',
            'edit',
        ]);

        $add('Company Settings', 'document_settings', [
            'view',
            'edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        $add('Notifications', 'notifications', [
            'view',
            'mark_read',
        ]);

        $add('Notifications', 'notification_preferences', [
            'view',
            'edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Save Permissions
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'module' => $permission['module'],
                ]
            );
        }
    }
}
