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
        $keys = [
            'software_engineering_it',
            'data_science_analytics',
            'design_ux_creative',
            'product_management',
            'sales_business_development',
            'marketing_digital_growth',
            'banking_finance',
            'accountants',
            'human_resources',
            'operations_logistics',
            'healthcare_medical',
            'education_training',
            'legal_compliance',
            'customer_success_support',
            'manufacturing_engineering',
            'bpo_telecaller',
            'other_general',
            'bpo',
            'telecaller',
            'banking',
            'finance',
        ];

        // Fall back to predefined keys if industry_types table is empty
        if (self::query()->count() === 0) {
            return [
                'nullable',
                'string',
                'max:64',
                Rule::in($keys),
            ];
        }

        return [
            'nullable',
            'string',
            'max:64',
            Rule::exists('industry_types', 'key')->where('is_active', true),
        ];
    }
}
