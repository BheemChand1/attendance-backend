<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionFeature extends Model
{
    protected $fillable = [
        'subscription_id',
        'feature_key',
        'feature_name',
    ];

    protected $casts = [
    ];

    /**
     * Get the subscription this feature belongs to
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
