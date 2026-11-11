<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use RuntimeException;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->first();

        if (!$company) {
            throw new RuntimeException(
                'No company found. Please seed a company first.'
            );
        }

        $customers = [
            [
                'customer_code' => 'CUS-001',
                'name' => 'Al Madina Traders',
                'email' => 'madina@example.com',
                'phone' => '03001234567',
                'tax_number' => null,
                'opening_balance' => 0,
                'credit_limit' => 500000,
                'payment_terms_days' => 30,
                'status' => 'active',
                'notes' => 'Regular wholesale customer.',
            ],
            [
                'customer_code' => 'CUS-002',
                'name' => 'Pakistan General Store',
                'email' => 'pgs@example.com',
                'phone' => '03011234567',
                'tax_number' => null,
                'opening_balance' => 0,
                'credit_limit' => 200000,
                'payment_terms_days' => 15,
                'status' => 'active',
                'notes' => 'Retail and wholesale customer.',
            ],
            [
                'customer_code' => 'CUS-003',
                'name' => 'City Distribution',
                'email' => 'citydist@example.com',
                'phone' => '03021234567',
                'tax_number' => null,
                'opening_balance' => 0,
                'credit_limit' => 1000000,
                'payment_terms_days' => 45,
                'status' => 'active',
                'notes' => 'Large distribution customer.',
            ],
            [
                'customer_code' => 'CUS-004',
                'name' => 'Royal Enterprises',
                'email' => 'royal@example.com',
                'phone' => '03031234567',
                'tax_number' => null,
                'opening_balance' => 0,
                'credit_limit' => 300000,
                'payment_terms_days' => 30,
                'status' => 'active',
                'notes' => 'Regular business customer.',
            ],
            [
                'customer_code' => 'CUS-005',
                'name' => 'New Star Traders',
                'email' => 'newstar@example.com',
                'phone' => '03041234567',
                'tax_number' => null,
                'opening_balance' => 0,
                'credit_limit' => 150000,
                'payment_terms_days' => 0,
                'status' => 'active',
                'notes' => 'New customer.',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'customer_code' => $customer['customer_code'],
                ],
                $customer
            );
        }
    }
}
