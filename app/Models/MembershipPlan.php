<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    protected $fillable = [
        'name',
        'interval',
		'user_type',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'double',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the features for this membership plan
     */
    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id');
    }

    /**
     * Get all features through plan_features
     */
    public function features()
    {
        return $this->hasManyThrough(
            Feature::class,
            PlanFeature::class,
            'plan_id',
            'id',
            'id',
            'feature_id'
        );
    }
}
