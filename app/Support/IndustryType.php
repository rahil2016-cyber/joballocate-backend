<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/**
 * Shared industry / job-family taxonomy for seekers, companies, and job posts.
 */
final class IndustryType
{
    /** @var list<string> */
    public const KEYS = [
        'software_engineering_it',
        'data_science_analytics',
        'design_ux_creative',
        'product_management',
        'sales_business_development',
        'marketing_digital_growth',
        'finance_accounting',
        'human_resources',
        'operations_logistics',
        'healthcare_medical',
        'education_training',
        'legal_compliance',
        'customer_success_support',
        'manufacturing_engineering',
        'other_general',
    ];

    public static function rule(): array
    {
        return ['nullable', 'string', 'max:64', Rule::in(self::KEYS)];
    }
}
