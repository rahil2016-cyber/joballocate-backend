<?php

namespace Database\Seeders;

use App\Models\SeekerPackage;
use Illuminate\Database\Seeder;

/**
 * Job seeker catalog: job-application plans are sold here. Resume-only and combo rows
 * stay in DB for history but are inactive — the app builds resumes for free; PDF is ₹20 on export.
 */
class SeekerPackageSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'key' => 'basic',
                'title' => 'Basic',
                'description' => 'Entry plan for applying to a few roles.',
                'kind' => 'job_applications',
                'price_inr' => 399,
                'duration_days' => 25,
                'applications_included' => 5,
                'resume_builds_included' => 0,
                'sort_order' => 10,
            ],
            [
                'key' => 'standard',
                'title' => 'Standard',
                'description' => 'Best value for active job seekers.',
                'kind' => 'job_applications',
                'price_inr' => 999,
                'duration_days' => 50,
                'applications_included' => 10,
                'resume_builds_included' => 0,
                'sort_order' => 20,
            ],
            [
                'key' => 'premium',
                'title' => 'Premium',
                'description' => 'Maximum application credits.',
                'kind' => 'job_applications',
                'price_inr' => 1499,
                'duration_days' => 65,
                'applications_included' => 16,
                'resume_builds_included' => 0,
                'sort_order' => 30,
            ],
            [
                'key' => 'resume_starter',
                'title' => 'Resume Starter',
                'description' => 'Export and polish multiple resume versions.',
                'kind' => 'resume',
                'price_inr' => 199,
                'duration_days' => 30,
                'applications_included' => 0,
                'resume_builds_included' => 3,
                'sort_order' => 40,
            ],
            [
                'key' => 'resume_pro',
                'title' => 'Resume Pro',
                'description' => 'Higher resume build allowance for power users.',
                'kind' => 'resume',
                'price_inr' => 499,
                'duration_days' => 90,
                'applications_included' => 0,
                'resume_builds_included' => 10,
                'sort_order' => 50,
            ],
            [
                'key' => 'combo_value',
                'title' => 'Jobs + Resume Value',
                'description' => 'Application credits plus resume exports in one plan.',
                'kind' => 'combo',
                'price_inr' => 1299,
                'duration_days' => 60,
                'applications_included' => 10,
                'resume_builds_included' => 5,
                'sort_order' => 60,
            ],
        ];

        foreach ($rows as $row) {
            $inactive = in_array($row['kind'], ['resume', 'combo'], true);
            SeekerPackage::query()->updateOrCreate(
                ['key' => $row['key']],
                array_merge($row, ['is_active' => ! $inactive])
            );
        }
    }
}
