<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();

        foreach ($branches as $branch) {

            $warehouses = [
                [
                    'code' => 'WH-001',
                    'name' => 'Main Warehouse',
                ],
                [
                    'code' => 'WH-002',
                    'name' => 'Secondary Warehouse',
                ],
            ];

            foreach ($warehouses as $warehouse) {

                Warehouse::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'code' => $warehouse['code'],
                    ],
                    [
                        'name' => $warehouse['name'],
                        'address' => $branch->address,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}