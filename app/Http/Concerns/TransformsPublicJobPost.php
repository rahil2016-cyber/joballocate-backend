<?php

namespace App\Http\Concerns;

use App\Models\JobPost;

trait TransformsPublicJobPost
{
    /**
     * @return array<string, mixed>
     */
    protected function transformListedJob(JobPost $job): array
    {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'slug' => $job->slug,
            'location' => $job->location,
            'employment_type' => $job->employment_type,
            'experience_level' => $job->experience_level,
            'industry_type' => $job->industry_type,
            'salary_min' => $job->salary_min,
            'salary_max' => $job->salary_max,
            'currency' => $job->currency,
            'description' => $job->description,
            'requirements' => $job->requirements,
            'skills' => $job->skills,
            'published_at' => $job->published_at?->toIso8601String(),
            'application_deadline_at' => $job->application_deadline_at?->toIso8601String(),
            'max_applications' => $job->max_applications,
            'applications_count' => $job->applications_count,
            'company' => $job->company,
        ];
    }
}
