<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'name' => 'Acme Pvt Ltd',
            'email' => 'info@acme.com',
            'phone' => '9876543210',
            'address' => 'Mumbai, India',
            'is_active' => true,
        ]);

        Company::create([
            'name' => 'Globex Corp',
            'email' => 'contact@globex.com',
            'phone' => '9123456789',
            'address' => 'Delhi, India',
            'is_active' => true,
        ]);
    }
}
