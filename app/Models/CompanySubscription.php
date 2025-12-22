<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CompanySubscription extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id',
        'start_date',
        'end_date',
        'status',
        'price',
        'billing_cycle',
        'next_billing_date',
        'employee_count',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'price' => 'decimal:2',
    ];

    /**
     * Get the company that owns this subscription
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the subscription plan
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if subscription is currently active
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $today = Carbon::today();
        
        // Check if subscription has started
        if ($this->start_date > $today) {
            return false;
        }

        // Check if subscription has expired
        if ($this->end_date && $this->end_date < $today) {
            return false;
        }

        return true;
    }

    /**
     * Check if subscription is about to expire
     */
    public function isExpiringSoon(int $days = 7): bool
    {
        if (!$this->end_date) {
            return false;
        }

        $expiryDate = Carbon::parse($this->end_date);
        $today = Carbon::today();
        
        return $today->diffInDays($expiryDate, false) <= $days && $today->diffInDays($expiryDate, false) >= 0;
    }

    /**
     * Check if company can add more employees
     */
    public function canAddEmployee(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $maxEmployees = $this->subscription->max_employees;
        return $this->employee_count < $maxEmployees;
    }

    /**
     * Check if company has access to a feature
     */
    public function hasFeatureAccess(string $featureKey): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->subscription->hasFeature($featureKey);
    }

    /**
     * Renew the subscription
     */
    public function renew(): void
    {
        $today = Carbon::today();
        $this->start_date = $today;
        
        // Calculate end date based on billing cycle
        if ($this->billing_cycle === 'monthly') {
            $this->end_date = $today->addMonth();
        } else { // yearly
            $this->end_date = $today->addYear();
        }
        
        $this->next_billing_date = $this->end_date;
        $this->status = 'active';
        $this->save();
    }

    /**
     * Cancel the subscription
     */
    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }
}
