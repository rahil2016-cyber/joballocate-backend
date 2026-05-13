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

        $this->seedSeekerHomePopularTiles();
    }

    /**
     * Dashboard “Popular categories” — editable label/icon in admin; inactive keys
     * are allowed when the tile uses {@see IndustryType::$seeker_home_search} only.
     */
    private function seedSeekerHomePopularTiles(): void
    {
        $byIndustryKey = [
            'banking_finance' => [
                'show_on_seeker_home' => true,
                'seeker_home_sort_order' => 10,
                'seeker_home_icon' => 'account_balance_rounded',
                'seeker_home_search' => null,
                'seeker_home_accent_dot' => false,
            ],
            'software_engineering_it' => [
                'show_on_seeker_home' => true,
                'seeker_home_sort_order' => 20,
                'seeker_home_icon' => 'computer_rounded',
                'seeker_home_search' => null,
                'seeker_home_accent_dot' => false,
            ],
            'bpo_telecaller' => [
                'show_on_seeker_home' => true,
                'seeker_home_sort_order' => 30,
                'seeker_home_icon' => 'phone_in_talk_rounded',
                'seeker_home_search' => null,
                'seeker_home_accent_dot' => true,
            ],
            'sales_business_development' => [
                'show_on_seeker_home' => true,
                'seeker_home_sort_order' => 40,
                'seeker_home_icon' => 'show_chart_rounded',
                'seeker_home_search' => null,
                'seeker_home_accent_dot' => false,
            ],
        ];

        foreach ($byIndustryKey as $key => $home) {
            IndustryType::query()->where('key', $key)->update($home);
        }

        IndustryType::query()->updateOrCreate(
            ['key' => 'private_jobs_home'],
            [
                'label' => 'Private Jobs',
                'sort_order' => 995,
                'is_active' => false,
                'show_on_seeker_home' => true,
                'seeker_home_sort_order' => 50,
                'seeker_home_icon' => 'work_outline_rounded',
                'seeker_home_search' => 'private',
                'seeker_home_accent_dot' => false,
            ]
        );

        IndustryType::query()->updateOrCreate(
            ['key' => 'work_from_home_home'],
            [
                'label' => 'Work From Home',
                'sort_order' => 996,
                'is_active' => false,
                'show_on_seeker_home' => true,
                'seeker_home_sort_order' => 60,
                'seeker_home_icon' => 'home_work_rounded',
                'seeker_home_search' => 'work from home',
                'seeker_home_accent_dot' => false,
            ]
        );
    }
}
