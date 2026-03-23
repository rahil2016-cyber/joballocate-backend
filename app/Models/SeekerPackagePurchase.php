<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeekerPackagePurchase extends Model
{
    protected $fillable = [
        'user_id',
        'seeker_package_id',
        'package_key',
        'title',
        'kind',
        'price_inr',
        'duration_days',
        'applications_granted',
        'resume_builds_granted',
        'activated_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seekerPackage(): BelongsTo
    {
        return $this->belongsTo(SeekerPackage::class);
    }
}
