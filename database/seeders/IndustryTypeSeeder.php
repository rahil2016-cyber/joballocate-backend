<?php

namespace Database\Seeders;

use App\Models\IndustryType;
use Illuminate\Database\Seeder;

/**
 * Seeds canonical industry keys used by companies, job posts, and seeker profiles.
 * Keys must stay stable (snake_case); labels are editable in admin.
 */
class IndustryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['software_engineering_it', 'Software engineering & IT', 10],
            ['data_science_analytics', 'Data science & analytics', 20],
            ['design_ux_creative', 'Design, UX & creative', 30],
            ['product_management', 'Product management', 40],
            ['sales_business_development', 'Sales & business development', 50],
            ['marketing_digital_growth', 'Marketing & digital growth', 60],
            ['finance_accounting', 'Finance & accounting', 70],
            ['human_resources', 'Human resources', 80],
            ['operations_logistics', 'Operations & logistics', 90],
            ['healthcare_medical', 'Healthcare & medical', 100],
            ['education_training', 'Education & training', 110],
            ['legal_compliance', 'Legal & compliance', 120],
            ['customer_success_support', 'Customer success & support', 130],
            ['manufacturing_engineering', 'Manufacturing & engineering', 140],
            ['other_general', 'Other / general', 900],
            // Previously mobile-only keys (keep for existing rows).
            ['banking_finance', 'Banking & finance', 145],
            ['accountants', 'Accountants', 150],
            ['bpo_telecaller', 'BPO & telecaller', 155],
            // Requested additions.
            ['bpo', 'BPO', 160],
            ['telecaller', 'Telecaller', 170],
            ['banking', 'Banking', 180],
            ['finance', 'Finance', 190],
        ];

        foreach ($rows as [$key, $label, $sort]) {
            IndustryType::query()->updateOrCreate(
                ['key' => $key],
                ['label' => $label, 'sort_order' => $sort, 'is_active' => true]
            );
        }
    }
}
