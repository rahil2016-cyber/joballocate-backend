<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Enums\JobPostStatus;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\SavedJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeekerSavedJobController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        JobPost::runAutoCloseJobs();

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 50);

        $paginator = SavedJob::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'jobPost' => fn ($q) => $q->with('company:id,name,slug,logo_url')->withCount('applications'),
            ])
            ->latest('id')
            ->paginate($perPage);

        $items = collect($paginator->items())->map(function (SavedJob $row) {
            $job = $row->jobPost;
            if (! $job) {
                return null;
            }

            return $this->transformSavedJob($job);
        })->filter()->values()->all();

        return $this->ok(
            $items,
            'OK',
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function store(Request $request, int $jobId): JsonResponse
    {
        JobPost::runAutoCloseJobs();

        $job = JobPost::query()
            ->where('id', $jobId)
            ->where('status', JobPostStatus::Published)
            ->whereNotNull('published_at')
            ->first();

        if (! $job) {
            return $this->fail('Job not found or not available to save.', null, 404);
        }

        if ($job->application_deadline_at !== null && now()->isAfter($job->application_deadline_at)) {
            return $this->fail('This job is no longer accepting applications.', null, 422);
        }

        SavedJob::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'job_post_id' => $job->id,
        ]);

        $job->refresh();
        $job->load(['company:id,name,slug,logo_url']);
        $job->loadCount('applications');

        return $this->ok($this->transformSavedJob($job), 'Job saved.');
    }

    public function destroy(Request $request, int $jobId): JsonResponse
    {
        SavedJob::query()
            ->where('user_id', $request->user()->id)
            ->where('job_post_id', $jobId)
            ->delete();

        return $this->ok(null, 'Job removed from saved.');
    }

    private function transformSavedJob(JobPost $job): array
    {
        $expired = $this->jobIsExpiredForSeeker($job);

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
            'status' => $job->status->value,
            'published_at' => $job->published_at?->toIso8601String(),
            'application_deadline_at' => $job->application_deadline_at?->toIso8601String(),
            'max_applications' => $job->max_applications,
            'applications_count' => $job->applications_count ?? $job->applications()->count(),
            'company' => $job->company,
            'is_expired' => $expired,
        ];
    }

    private function jobIsExpiredForSeeker(JobPost $job): bool
    {
        if ($job->status !== JobPostStatus::Published || $job->published_at === null) {
            return true;
        }

        if ($job->application_deadline_at !== null && now()->isAfter($job->application_deadline_at)) {
            return true;
        }

        if ($job->max_applications !== null) {
            $count = $job->applications_count ?? $job->applications()->count();
            if ($count >= $job->max_applications) {
                return true;
            }
        }

        return false;
    }
}
