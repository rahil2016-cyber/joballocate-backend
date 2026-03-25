<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanySubscriptionPackage extends Model
{
    protected $fillable = [
        'title',
        'monthly_price_inr',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price_inr' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function coupons(): HasMany
    {
        return $this->hasMany(CompanyCoupon::class, 'company_subscription_package_id');
    }

}

