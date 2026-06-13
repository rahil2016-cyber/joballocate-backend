<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Enums\ApplicationStatus;
use App\Enums\JobPostStatus;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPost;
use App\Models\JobSeekerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeekerApplicationController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $apps = Application::query()
            ->with(['jobPost' => fn ($q) => $q->with('company:id,name,slug')])
            ->where('user_id', $request->user()->id)
            ->latest('applied_at')
            ->paginate((int) $request->get('per_page', 15));

        return $this->ok(
            $apps->items(),
            'OK',
            [
                'current_page' => $apps->currentPage(),
                'last_page' => $apps->lastPage(),
                'per_page' => $apps->perPage(),
                'total' => $apps->total(),
            ]
        );
    }

    public function store(Request $request, int $jobId): JsonResponse
    {
        $validated = $request->validate([
            'cover_letter' => ['nullable', 'string', 'max:5000'],
        ]);

        return DB::transaction(function () use ($request, $jobId, $validated): JsonResponse {
            JobPost::runAutoCloseJobs();

            $profile = JobSeekerProfile::query()
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if (! $profile) {
                return $this->fail('Seeker profile not found.', null, 404);
            }

            $job = JobPost::query()->where('id', $jobId)->lockForUpdate()->first();

            if (! $job || $job->status !== JobPostStatus::Published || $job->published_at === null) {
                return $this->fail('This job is not accepting applications.', null, 404);
            }

            if ($job->application_deadline_at !== null && now()->isAfter($job->application_deadline_at)) {
                $job->status = JobPostStatus::Closed;
                $job->save();

                return $this->fail('The application deadline has passed.', null, 422);
            }

            $currentCount = Application::query()->where('job_post_id', $job->id)->count();
            if ($job->max_applications !== null && $currentCount >= $job->max_applications) {
                $job->status = JobPostStatus::Closed;
                $job->save();

                return $this->fail('This job has reached the maximum number of applicants.', null, 422);
            }

            $exists = Application::query()
                ->where('job_post_id', $job->id)
                ->where('user_id', $request->user()->id)
                ->exists();

            if ($exists) {
                return $this->fail('You have already applied to this job.', null, 409);
            }

            $application = Application::create([
                'job_post_id' => $job->id,
                'user_id' => $request->user()->id,
                'status' => ApplicationStatus::Applied,
                'cover_letter' => $validated['cover_letter'] ?? null,
                'applied_at' => now(),
            ]);

            $newCount = $currentCount + 1;
            if ($job->max_applications !== null && $newCount >= $job->max_applications) {
                $job->status = JobPostStatus::Closed;
                $job->save();
            }



            $application->load(['jobPost.company:id,name,slug']);

            return $this->ok($application, 'Application submitted.', null, 201);
        });
    }

    public function destroy(Request $request, int $applicationId): JsonResponse
    {
        $application = Application::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $applicationId)
            ->first();

        if (! $application) {
            return $this->fail('Application not found.', null, 404);
        }

        $withdrawable = [
            ApplicationStatus::Applied,
            ApplicationStatus::Shortlisted,
        ];

        if (! in_array($application->status, $withdrawable, true)) {
            return $this->fail('You can only withdraw applications that are still under initial review.', null, 422);
        }

        return DB::transaction(function () use ($application, $request): JsonResponse {
            $application->delete();

            $profile = JobSeekerProfile::query()
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();



            return $this->ok(null, 'Application withdrawn.');
        });
    }
}
