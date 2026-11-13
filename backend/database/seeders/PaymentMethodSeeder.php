<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Cash',
                'type' => 'cash',
                'requires_reference' => false,
            ],
            [
                'name' => 'Bank Transfer',
                'type' => 'bank',
                'requires_reference' => true,
            ],
            [
                'name' => 'Cheque',
                'type' => 'cheque',
                'requires_reference' => true,
            ],
            [
                'name' => 'Online Payment',
                'type' => 'online',
                'requires_reference' => true,
            ],
        ];

        Company::query()->each(function ($company) use ($paymentMethods) {

            foreach ($paymentMethods as $method) {

                PaymentMethod::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $method['name'],
                    ],
                    [
                        'type' => $method['type'],
                        'requires_reference' => $method['requires_reference'],
                        'status' => true,
                    ]
                );
            }
        });
    }
}
