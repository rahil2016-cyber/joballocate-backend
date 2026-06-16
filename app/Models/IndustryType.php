<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class IndustryType extends Model
{
    protected $fillable = [
        'key',
        'label',
        'sort_order',
        'is_active',
        'show_on_seeker_home',
        'seeker_home_sort_order',
        'seeker_home_icon',
        'seeker_home_search',
        'seeker_home_accent_dot',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'show_on_seeker_home' => 'boolean',
            'seeker_home_sort_order' => 'integer',
            'seeker_home_accent_dot' => 'boolean',
        ];
    }

    /** @param Builder<self> $query */
    public function scopeActiveOrdered(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label');
    }

    /**
     * @return array<int, mixed>
     */
    public static function validationRule(): array
    {
        return [
            'nullable',
            'string',
            'max:64',
        ];
    }
}
