<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySubscriptionPayment extends Model
{
    protected $fillable = [
        'company_id',
        'company_subscription_package_id',
        'cycle_number',
        'coupon_code_used',
        'amount_inr',
        'is_free',
        'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

