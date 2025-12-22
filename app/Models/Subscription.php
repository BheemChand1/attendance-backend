<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'max_employees',
        'max_departments',
        'storage_gb',
        'support_level',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all company subscriptions for this plan
     */
    public function companySubscriptions(): HasMany
    {
        return $this->hasMany(CompanySubscription::class);
    }

    /**
     * Get all features for this subscription
     */
    public function features(): HasMany
    {
        return $this->hasMany(SubscriptionFeature::class);
    }

    /**
     * Check if a feature is included in this subscription
     */
    public function hasFeature(string $featureKey): bool
    {
        return $this->features()
            ->where('feature_key', $featureKey)
            ->where('is_included', true)
            ->exists();
    }
}
