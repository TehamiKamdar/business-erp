<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->firstOrFail();

        $accounts = [
            // ASSETS
            ['1000', 'Assets', 'asset', null, 'debit', null, false, false],
            ['1100', 'Current Assets', 'asset', 'current_asset', 'debit', '1000', false, false],
            ['1110', 'Cash and Bank', 'asset', 'current_asset', 'debit', '1100', false, false],
            ['1120', 'Cash and Bank Control', 'asset', 'current_asset', 'debit', '1110', true, false],
            ['1130', 'Accounts Receivable Control', 'asset', 'current_asset', 'debit', '1100', true, false],
            ['1140', 'Inventory Control', 'asset', 'current_asset', 'debit', '1100', true, false],
            ['1150', 'Input Tax Recoverable', 'asset', 'current_asset', 'debit', '1100', false, true],

            ['1200', 'Fixed Assets', 'asset', 'fixed_asset', 'debit', '1000', false, false],
            ['1210', 'Property, Plant and Equipment', 'asset', 'fixed_asset', 'debit', '1200', false, true],
            ['1220', 'Accumulated Depreciation', 'asset', 'contra_asset', 'credit', '1200', false, true],

            // LIABILITIES
            ['2000', 'Liabilities', 'liability', null, 'credit', null, false, false],
            ['2100', 'Current Liabilities', 'liability', 'current_liability', 'credit', '2000', false, false],
            ['2110', 'Accounts Payable Control', 'liability', 'current_liability', 'credit', '2100', true, false],
            ['2120', 'Output Tax Payable', 'liability', 'current_liability', 'credit', '2100', false, true],
            ['2130', 'Accrued Expenses', 'liability', 'current_liability', 'credit', '2100', false, true],

            // EQUITY
            ['3000', 'Equity', 'equity', null, 'credit', null, false, false],
            ['3100', 'Owner Capital', 'equity', 'capital', 'credit', '3000', false, true],
            ['3200', 'Retained Earnings', 'equity', 'retained_earnings', 'credit', '3000', false, false],
            ['3300', 'Opening Balance Equity', 'equity', 'opening_balance_equity', 'credit', '3000', false, false],

            // INCOME
            ['4000', 'Income', 'income', null, 'credit', null, false, false],
            ['4100', 'Sales Revenue', 'income', 'revenue', 'credit', '4000', false, true],
            ['4200', 'Sales Returns and Allowances', 'income', 'contra_revenue', 'debit', '4000', false, true],
            ['4300', 'Other Income', 'income', 'other_income', 'credit', '4000', false, true],

            // EXPENSES
            ['5000', 'Expenses', 'expense', null, 'debit', null, false, false],
            ['5100', 'Cost of Goods Sold', 'expense', 'cost_of_goods_sold', 'debit', '5000', false, true],
            ['5200', 'Operating Expenses', 'expense', 'operating_expense', 'debit', '5000', false, false],
            ['5210', 'Salaries Expense', 'expense', 'operating_expense', 'debit', '5200', false, true],
            ['5220', 'Rent Expense', 'expense', 'operating_expense', 'debit', '5200', false, true],
            ['5230', 'Utilities Expense', 'expense', 'operating_expense', 'debit', '5200', false, true],
            ['5240', 'Depreciation Expense', 'expense', 'operating_expense', 'debit', '5200', false, true],
            ['5300', 'Other Expenses', 'expense', 'other_expense', 'debit', '5000', false, true],
        ];

        DB::transaction(function () use ($accounts, $company) {

            $accountIds = [];

            foreach ($accounts as $account) {
                [
                    $code,
                    $name,
                    $type,
                    $subtype,
                    $normalBalance,
                    $parentCode,
                    $isControl,
                    $allowManual
                ] = $account;

                $parentId = $parentCode
                    ? ($accountIds[$parentCode] ?? null)
                    : null;

                if ($parentCode && !$parentId) {
                    throw new \RuntimeException(
                        "Parent account {$parentCode} not found."
                    );
                }

                $chartAccount = ChartOfAccount::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'account_code' => $code,
                    ],
                    [
                        'parent_id' => $parentId,
                        'name' => $name,
                        'account_type' => $type,
                        'account_subtype' => $subtype,
                        'normal_balance' => $normalBalance,
                        'is_control_account' => $isControl,
                        'allow_manual_entries' => $allowManual,
                        'status' => true,
                    ]
                );

                $accountIds[$code] = $chartAccount->id;
            }
        });
    }
}
