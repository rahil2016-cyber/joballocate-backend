<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\JobPostStatus;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Support\IndustryType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicJobController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        JobPost::runAutoCloseJobs();

        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:200'],
            'location' => ['sometimes', 'string', 'max:120'],
            'industry_type' => IndustryType::rule(),
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $q = JobPost::query()
            ->with('company:id,name,slug,logo_url')
            ->withCount('applications')
            ->listed()
            ->latest('published_at');

        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term) {
                $query->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if (! empty($validated['location'] ?? null)) {
            $q->where('location', 'like', '%'.$validated['location'].'%');
        }

        if (! empty($validated['industry_type'] ?? null)) {
            $q->where('industry_type', $validated['industry_type']);
        }

        $perPage = $validated['per_page'] ?? 15;

        $paginator = $q->paginate($perPage);

        return $this->ok(
            $paginator->items(),
            'OK',
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function show(int $id): JsonResponse
    {
        JobPost::runAutoCloseJobs();

        $job = JobPost::query()
            ->with('company:id,name,slug,logo_url,description,website')
            ->withCount('applications')
            ->where('id', $id)
            ->where('status', JobPostStatus::Published)
            ->whereNotNull('published_at')
            ->first();

        if (! $job) {
            return $this->fail('Job not found.', null, 404);
        }

        return $this->ok($this->transformJob($job));
    }

    private function transformJob(JobPost $job): array
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
