<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $company = Company::updateOrCreate(
                [
                    'email' => 'admin@demo-tradeflow.com',
                ],
                [
                    'name' => 'Demo TradeFlow',
                    'legal_name' => 'Demo TradeFlow Private Limited',
                    'phone' => '+923001234567',
                    'address' => 'Karachi, Pakistan',
                    'currency' => 'PKR',
                    'status' => 'active',
                ]
            );

            $branch = Branch::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'code' => 'MAIN',
                ],
                [
                    'name' => 'Main Branch',
                    'address' => 'Karachi, Pakistan',
                    'phone' => '+923001234567',
                    'status' => 'active',
                    'is_main_branch' => true,
                ]
            );

            Warehouse::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'code' => 'WH-001',
                ],
                [
                    'name' => 'Main Warehouse',
                    'address' => 'Karachi, Pakistan',
                    'status' => 'active',
                ]
            );

        });
    }
}
