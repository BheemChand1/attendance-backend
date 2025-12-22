<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company_size',
        'location',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function employeeProfiles()
    {
        return $this->hasMany(EmployeeProfile::class);
    }

    /**
     * Get the current active subscription for this company
     */
    public function currentSubscription()
    {
        return $this->hasOne(CompanySubscription::class)
            ->where('status', 'active')
            ->latest('start_date');
    }

    /**
     * Get all subscriptions for this company
     */
    public function subscriptions()
    {
        return $this->hasMany(CompanySubscription::class);
    }

    /**
     * Check if company has a specific feature access
     */
    public function hasFeatureAccess(string $featureKey): bool
    {
        $activeSubscription = $this->currentSubscription()->first();
        
        if (!$activeSubscription) {
            return false;
        }

        return $activeSubscription->hasFeatureAccess($featureKey);
    }

    /**
     * Get the maximum allowed employees for current subscription
     */
    public function getMaxEmployees(): int
    {
        $activeSubscription = $this->currentSubscription()->first();
        
        if (!$activeSubscription) {
            return 0;
        }

        return $activeSubscription->subscription->max_employees;
    }

    /**
     * Check if company can add more employees
     */
    public function canAddEmployee(): bool
    {
        $activeSubscription = $this->currentSubscription()->first();
        
        if (!$activeSubscription) {
            return false;
        }

        return $activeSubscription->canAddEmployee();
    }
}
