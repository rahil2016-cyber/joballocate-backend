<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCoupon extends Model
{
    protected $fillable = [
        'code',
        'target_type',
        'target_value',
        'discount_percent',
        'free_first_month',
        'is_active',
        'company_subscription_package_id',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
        'free_first_month' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function companySubscriptionPackage()
    {
        return $this->belongsTo(CompanySubscriptionPackage::class, 'company_subscription_package_id');
    }
}

