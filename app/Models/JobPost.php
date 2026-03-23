<?php

namespace App\Models;

use App\Enums\JobPostStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class JobPost extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'location',
        'employment_type',
        'experience_level',
        'industry_type',
        'salary_min',
        'salary_max',
        'currency',
        'description',
        'requirements',
        'skills',
        'status',
        'review_note',
        'published_at',
        'application_deadline_at',
        'max_applications',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'published_at' => 'datetime',
            'application_deadline_at' => 'datetime',
            'status' => JobPostStatus::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /** Public job board: published and visible. */
    public function scopeListed($query)
    {
        return $query->where('status', JobPostStatus::Published)
            ->whereNotNull('published_at');
    }

    /**
     * Auto-close published jobs past application deadline or at max applicants.
     * Call from public job routes and before applying so listings stay accurate.
     */
    public static function runAutoCloseJobs(): void
    {
        static::query()
            ->where('status', JobPostStatus::Published)
            ->whereNotNull('application_deadline_at')
            ->where('application_deadline_at', '<=', now())
            ->update(['status' => JobPostStatus::Closed->value]);

        static::query()
            ->where('status', JobPostStatus::Published)
            ->whereNotNull('max_applications')
            ->chunkById(100, function ($jobs): void {
                foreach ($jobs as $job) {
                    $count = $job->applications()->count();
                    if ($count >= $job->max_applications) {
                        static::query()->whereKey($job->id)->update([
                            'status' => JobPostStatus::Closed->value,
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }
}
