<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeekerPackage extends Model
{
    protected $fillable = [
        'key',
        'title',
        'description',
        'kind',
        'price_inr',
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
}
