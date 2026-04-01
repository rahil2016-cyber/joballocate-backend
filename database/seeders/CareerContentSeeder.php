<?php

namespace Database\Seeders;

use App\Models\CareerContent;
use Illuminate\Database\Seeder;

class CareerContentSeeder extends Seeder
{
    public function run(): void
    {
        if (CareerContent::query()->exists()) {
            return;
        }

        CareerContent::query()->create([
            'content_type' => CareerContent::TYPE_CAREER_GUIDANCE,
            'title' => 'Build a profile employers notice',
            'subtitle' => 'Career tip',
            'body' => 'Keep your headline specific to your target role, quantify achievements in your summary, '
                .'and refresh skills to match the jobs you want. Use JobAllocate’s resume builder and tailor '
                .'your primary resume before each application.',
            'sort_order' => 10,
            'is_published' => true,
            'published_at' => now(),
        ]);

        CareerContent::query()->create([
            'content_type' => CareerContent::TYPE_INTERVIEW_EXPERIENCE,
            'title' => 'Sample — Product company technical loop',
            'subtitle' => 'Shared by hiring team (illustrative)',
            'body' => 'Typical flow: 30-minute recruiter screen, 45-minute coding pair session, then two rounds '
                .'mixing system design and behavioural questions. They cared most about clear communication '
                .'while problem-solving and honest trade-offs in design.',
            'rating_hint' => 4.5,
            'sort_order' => 10,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $qa = [
            ['HR & Behavioral', 'Tell me about yourself.', 'Open with your current role, one strong result, then why this company fits your direction. Keep it under 90 seconds.'],
            ['HR & Behavioral', 'Why should we hire you?', 'Map two skills to their job description and give a short example for each.'],
            ['Technical Fundamentals', 'What is idempotency?', 'An operation is idempotent if doing it multiple times has the same effect as once — important for APIs and payments.'],
        ];
        $order = 10;
        foreach ($qa as [$cat, $q, $a]) {
            CareerContent::query()->create([
                'content_type' => CareerContent::TYPE_INTERVIEW_QA,
                'category' => $cat,
                'question' => $q,
                'answer' => $a,
                'sort_order' => $order,
                'is_published' => true,
                'published_at' => now(),
            ]);
            $order += 10;
        }
    }
}
