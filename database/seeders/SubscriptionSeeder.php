<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\SubscriptionFeature;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Basic Plan
        $basicPlan = Subscription::create([
            'name' => 'Basic',
            'description' => 'Perfect for startups and small teams',
            'price' => 99.00,
            'max_employees' => 50,
            'max_departments' => 3,
            'storage_gb' => 10,
            'support_level' => 1,
            'is_active' => true,
        ]);

        // Professional Plan
        $professionalPlan = Subscription::create([
            'name' => 'Professional',
            'description' => 'For growing businesses with advanced features',
            'price' => 299.00,
            'max_employees' => 500,
            'max_departments' => 10,
            'storage_gb' => 100,
            'support_level' => 2,
            'is_active' => true,
        ]);

        // Enterprise Plan
        $enterprisePlan = Subscription::create([
            'name' => 'Enterprise',
            'description' => 'Complete solution with all features for large organizations',
            'price' => 999.00,
            'max_employees' => 999999,
            'max_departments' => 999999,
            'storage_gb' => 1000,
            'support_level' => 3,
            'is_active' => true,
        ]);

        // Add features for each subscription
        $this->addBasicFeatures($basicPlan);
        $this->addProfessionalFeatures($professionalPlan);
        $this->addEnterpriseFeatures($enterprisePlan);
    }

    private function addBasicFeatures(Subscription $plan): void
    {
        $features = [
            ['key' => 'attendance', 'name' => 'Attendance Tracking'],
        ];

        foreach ($features as $feature) {
            SubscriptionFeature::create([
                'subscription_id' => $plan->id,
                'feature_key' => $feature['key'],
                'feature_name' => $feature['name'],
            ]);
        }
    }

    private function addProfessionalFeatures(Subscription $plan): void
    {
        $features = [
            ['key' => 'attendance', 'name' => 'Attendance Tracking'],
            ['key' => 'payroll', 'name' => 'Payroll Management'],
            ['key' => 'leave_management', 'name' => 'Leave Management'],
        ];

        foreach ($features as $feature) {
            SubscriptionFeature::create([
                'subscription_id' => $plan->id,
                'feature_key' => $feature['key'],
                'feature_name' => $feature['name'],
            ]);
        }
    }

    private function addEnterpriseFeatures(Subscription $plan): void
    {
        $features = [
            ['key' => 'attendance', 'name' => 'Attendance Tracking'],
            ['key' => 'payroll', 'name' => 'Payroll Management'],
            ['key' => 'leave_management', 'name' => 'Leave Management'],
            ['key' => 'performance', 'name' => 'Performance Tracking'],
            ['key' => 'reports', 'name' => 'Advanced Reports'],
            ['key' => 'api_access', 'name' => 'API Access'],
        ];

        foreach ($features as $feature) {
            SubscriptionFeature::create([
                'subscription_id' => $plan->id,
                'feature_key' => $feature['key'],
                'feature_name' => $feature['name'],
            ]);
        }
    }
}
