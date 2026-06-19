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
            'role_category' => $job->role_category,
            'functional_area' => $job->functional_area,
            'education' => $job->education,
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
            'assets_required' => $job->assets_required,
            'languages' => $job->languages,
            'incentive_detail' => $job->incentive_detail,
            'job_timings' => $job->job_timings,
            'working_days' => $job->working_days,
            'age_min' => $job->age_min,
            'age_max' => $job->age_max,
            'gender_preference' => $job->gender_preference,
            'contact_preference' => $job->contact_preference,
            'contact_person' => $job->contact_person,
            'contact_phone' => $job->contact_phone,
            'contact_email' => $job->contact_email,
            'department' => $job->department,
            'role' => $job->role,
            'security_deposit' => $job->security_deposit,
            'security_deposit_amount' => $job->security_deposit_amount,
            'interview_timings' => $job->interview_timings,
        ];
    }
}
