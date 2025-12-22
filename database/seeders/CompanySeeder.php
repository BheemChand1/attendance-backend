<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'name' => 'Tech Solutions India',
            'email' => 'info@techsolutions.com',
            'phone' => '9876543210',
            'address' => '123 Tech Park, Mumbai, India',
            'company_size' => 100,
            'location' => 'Mumbai',
            'is_active' => true,
        ]);

        Company::create([
            'name' => 'Digital Innovations',
            'email' => 'contact@digitalinnovations.com',
            'phone' => '9123456789',
            'address' => '456 Innovation Hub, Delhi, India',
            'company_size' => 50,
            'location' => 'Delhi',
            'is_active' => true,
        ]);

        Company::create([
            'name' => 'Cloud Systems Ltd',
            'email' => 'support@cloudsystems.com',
            'phone' => '9988776655',
            'address' => '789 Cloud Center, Bangalore, India',
            'company_size' => 200,
            'location' => 'Bangalore',
            'is_active' => true,
        ]);
    }
}
