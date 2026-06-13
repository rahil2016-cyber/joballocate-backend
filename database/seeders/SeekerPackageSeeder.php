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
                'key' => 'basic_resume',
                'title' => 'Basic Resume Package',
                'description' => "Access to 4 Professional Resume Templates\nEasy Resume Editing\nPDF Download",
                'kind' => 'resume',
                'price_inr' => 99,
                'duration_days' => 30,
                'applications_included' => 0,
                'resume_builds_included' => 4, // 4 downloads/builds limit or template access handled on client
                'sort_order' => 10,
            ],
            [
                'key' => 'premium_resume',
                'title' => 'Premium Resume Package',
                'description' => "Access to 8 Professional Resume Templates\nUnlimited Resume Editing\nPDF Download\nCover Letter Template Included",
                'kind' => 'resume',
                'price_inr' => 299,
                'duration_days' => 90,
                'applications_included' => 0,
                'resume_builds_included' => 8,
                'sort_order' => 20,
            ],
            [
                'key' => 'professional_resume',
                'title' => 'Professional Resume Package',
                'description' => "Access to All 12 Premium Resume Templates\nUnlimited Resume Editing\nUnlimited PDF Downloads\nPremium Cover Letter Templates\nPriority Customer Support",
                'kind' => 'resume',
                'price_inr' => 499,
                'duration_days' => 180,
                'applications_included' => 0,
                'resume_builds_included' => 99999, // unlimited
                'sort_order' => 30,
            ],
        ];

        foreach ($rows as $row) {
            SeekerPackage::query()->updateOrCreate(
                ['key' => $row['key']],
                array_merge($row, ['is_active' => true])
            );
        }

        // Deactivate any old application-only packages
        SeekerPackage::query()
            ->whereNotIn('key', ['basic_resume', 'premium_resume', 'professional_resume'])
            ->update(['is_active' => false]);
    }
}
