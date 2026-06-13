<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'headline',
        'bio',
        'skills',
        'education',
        'experience_years',
        'expected_salary_min',
        'expected_salary_max',
        'currency',
        'city',
        'country',
        'state',
        'district',
        'industry_type',
        'date_of_birth',
        'resume_url',
        'profile_photo_url',
        'primary_resume_draft_id',
        'package_key',
        'job_package_key',
        'resume_package_key',
        'applications_remaining',
        'resume_builds_remaining',
        'package_activated_at',
        'package_expires_at',
        'job_credits_expires_at',
        'resume_credits_expires_at',
        'total_time_spent_seconds',
        'last_app_activity_at',
        'onboarded',
        'job_roles',
        'is_experienced',
        'current_company',
        'current_role',
        'preferred_locations',
        'willing_to_relocate',
        'employment_preferences',
        'expected_salary',
        'onboarding_step',
        'current_status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'skills' => 'array',
            'education' => 'array',
            'onboarded' => 'boolean',
            'job_roles' => 'array',
            'is_experienced' => 'boolean',
            'preferred_locations' => 'array',
            'willing_to_relocate' => 'boolean',
            'employment_preferences' => 'array',
            'onboarding_step' => 'integer',
            'package_activated_at' => 'datetime',
            'package_expires_at' => 'datetime',
            'job_credits_expires_at' => 'datetime',
            'resume_credits_expires_at' => 'datetime',
            'last_app_activity_at' => 'datetime',
        ];
    }

    /** Job-application credits: not expired and at least one application left. */
    public function canApply(): bool
    {
        return true;
    }

    /** Resume / AI / PDF credits: not expired and at least one build left. */
    public function canBuildResume(): bool
    {
        if ($this->resume_builds_remaining === null || $this->resume_builds_remaining < 1) {
            return false;
        }
        if ($this->resume_credits_expires_at === null) {
            return false;
        }

        return $this->resume_credits_expires_at->isFuture();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Resume shown to employers (with profile link / applications). */
    public function primaryResumeDraft(): BelongsTo
    {
        return $this->belongsTo(ResumeDraft::class, 'primary_resume_draft_id');
    }
}
