<?php
namespace App\Modules\Subscription\Models;

class Plan extends BaseModel 
{
    protected $table = 'subscription_plans';
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'features',
        'trial_period',
        'setup_fee',
        'is_active',
        'sort_order',
        'currency_id',
        'tax_group_id'
    ];

    public function features()
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
} 