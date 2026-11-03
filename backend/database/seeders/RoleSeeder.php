<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleDefinitions = [

            [
                'name' => 'Company Super Admin',
                'slug' => 'company-super-admin',
                'description' => 'Full access to all company modules and permissions.',
                'modules' => [],
                'report_prefixes' => [],
                'all_permissions' => true,
            ],

            [
                'name' => 'Accountant',
                'slug' => 'accountant',
                'description' => 'Manages accounting, financial transactions and financial reports.',
                'modules' => [
                    'Accounting',
                    'Financial Management',
                ],
                'report_prefixes' => [
                    'financial_reports',
                    'profit_loss_report',
                    'balance_sheet_report',
                ],
                'all_permissions' => false,
            ],

            [
                'name' => 'Sales Manager',
                'slug' => 'sales-manager',
                'description' => 'Manages customers, sales operations and sales reports.',
                'modules' => [
                    'Sales',
                ],
                'report_prefixes' => [
                    'sales_reports',
                    'customer_reports',
                ],
                'all_permissions' => false,
            ],

            [
                'name' => 'Purchase Manager',
                'slug' => 'purchase-manager',
                'description' => 'Manages suppliers, purchasing operations and purchase reports.',
                'modules' => [
                    'Purchasing',
                ],
                'report_prefixes' => [
                    'purchasing_reports',
                    'supplier_reports',
                ],
                'all_permissions' => false,
            ],

            [
                'name' => 'Inventory Manager',
                'slug' => 'inventory-manager',
                'description' => 'Manages inventory, stock operations and product catalog.',
                'modules' => [
                    'Inventory Management',
                    'Product Catalog',
                ],
                'report_prefixes' => [
                    'inventory_reports',
                ],
                'all_permissions' => false,
            ],
        ];

        Company::query()->chunkById(100, function ($companies) use ($roleDefinitions) {

            foreach ($companies as $company) {

                DB::transaction(function () use ($company, $roleDefinitions) {

                    foreach ($roleDefinitions as $definition) {

                        $role = Role::updateOrCreate(
                            [
                                'company_id' => $company->id,
                                'slug' => $definition['slug'],
                            ],
                            [
                                'name' => $definition['name'],
                                'description' => $definition['description'],
                                'status' => 'active',
                            ]
                        );

                        if ($definition['all_permissions']) {
                            $permissionIds = Permission::query()
                                ->pluck('id');
                        } else {
                            $query = Permission::query();

                            if (!empty($definition['modules'])) {
                                $query->whereIn(
                                    'module',
                                    $definition['modules']
                                );
                            } else {
                                $query->whereRaw('1 = 0');
                            }

                            if (!empty($definition['report_prefixes'])) {
                                $query->orWhere(function ($subQuery) use ($definition) {

                                    foreach ($definition['report_prefixes'] as $prefix) {
                                        $subQuery->orWhere(
                                            'slug',
                                            'like',
                                            $prefix . '.%'
                                        );
                                    }

                                });
                            }

                            $permissionIds = $query->pluck('id');
                        }

                        $role->permissions()->sync($permissionIds);
                    }
                });
            }

        }, 'id');
    }
}
