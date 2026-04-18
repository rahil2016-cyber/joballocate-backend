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
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
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
            Rule::exists('industry_types', 'key')->where('is_active', true),
        ];
    }
}
