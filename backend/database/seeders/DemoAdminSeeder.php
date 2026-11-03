<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAdminSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('email', 'admin@demo-tradeflow.com')
            ->firstOrFail();

        $mainBranch = $company->branches()
            ->where('is_main_branch', true)
            ->firstOrFail();

        $role = Role::where('company_id', $company->id)
            ->where('slug', 'company-super-admin')
            ->firstOrFail();

        $user = User::updateOrCreate(
            ['email' => 'admin@demo-tradeflow.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('ChangeMe@12345'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->branches()->syncWithoutDetaching([$mainBranch->id]);

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}
