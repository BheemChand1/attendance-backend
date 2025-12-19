<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superadminRole = Role::where('slug', 'superadmin')->first();
        $companyAdminRole = Role::where('slug', 'company_admin')->first();
        $hrRole = Role::where('slug', 'hr')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        // 🔑 Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@system.com',
            'password' => Hash::make('password'),
            'role_id' => $superadminRole->id,
            'company_id' => null,
        ]);

        // Loop companies
        Company::all()->each(function ($company) use ($companyAdminRole, $hrRole, $employeeRole) {

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
            for ($i = 1; $i <= 5; $i++) {
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
