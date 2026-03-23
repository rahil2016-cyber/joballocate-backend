<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeekerPackage extends Model
{
    protected $fillable = [
        'key',
        'title',
        'description',
        'kind',
        'price_inr',
        'list_price_inr',
        'duration_days',
        'applications_included',
        'resume_builds_included',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(SeekerPackagePurchase::class, 'seeker_package_id');
    }
}
