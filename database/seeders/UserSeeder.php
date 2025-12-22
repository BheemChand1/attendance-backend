<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\CompanySubscription;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superadminRole = Role::where('slug', 'superadmin')->first();
        $companyAdminRole = Role::where('slug', 'company_admin')->first();
        $hrRole = Role::where('slug', 'hr')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        // Get subscription plans
        $basicSubscription = Subscription::where('name', 'Basic')->first();
        $professionalSubscription = Subscription::where('name', 'Professional')->first();
        $enterpriseSubscription = Subscription::where('name', 'Enterprise')->first();

        // 🔑 Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@system.com',
            'password' => Hash::make('password'),
            'role_id' => $superadminRole->id,
            'company_id' => null,
        ]);

        // Loop companies
        Company::all()->each(function ($company, $index) use ($companyAdminRole, $hrRole, $employeeRole, $basicSubscription, $professionalSubscription, $enterpriseSubscription) {

            // Assign subscription based on company index
            $subscription = match ($index) {
                0 => $basicSubscription,
                1 => $professionalSubscription,
                default => $enterpriseSubscription,
            };

            // Create company subscription
            CompanySubscription::create([
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today()->addMonths(12),
                'status' => 'active',
                'price' => $subscription->price,
                'billing_cycle' => 'monthly',
                'next_billing_date' => Carbon::today()->addMonth(),
                'employee_count' => 6, // 1 admin + 1 hr + 4 employees
            ]);

            // 🧑‍💼 Company Admin
            $companyAdmin = User::create([
                'name' => $company->name . ' Admin',
                'email' => strtolower(str_replace(' ', '', $company->name)) . '@admin.com',
                'password' => Hash::make('password'),
                'role_id' => $companyAdminRole->id,
                'company_id' => $company->id,
            ]);

            // 👩‍💼 HR
            $hr = User::create([
                'name' => $company->name . ' HR',
                'email' => strtolower(str_replace(' ', '', $company->name)) . '@hr.com',
                'password' => Hash::make('password'),
                'role_id' => $hrRole->id,
                'company_id' => $company->id,
            ]);

            // 👷 Employees
            for ($i = 1; $i <= 4; $i++) {
                User::create([
                    'name' => "Employee {$i}",
                    'email' => "emp{$i}@" . strtolower(str_replace(' ', '', $company->name)) . ".com",
                    'password' => Hash::make('password'),
                    'role_id' => $employeeRole->id,
                    'company_id' => $company->id,
                    'employee_code' => "EMP{$company->id}0{$i}",
                    'phone' => '9' . rand(100000000, 999999999),
                ]);
            }
        });
    }
}

