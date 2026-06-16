<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Enums\JobPostStatus;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Services\JobPublishingService;
use App\Models\IndustryType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyJobPostController extends Controller
{
    use ApiResponses;

    public function __construct(
        private readonly JobPublishingService $publishing
    ) {}

    public function index(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $jobs = JobPost::query()
            ->where('company_id', $company->id)
            ->withCount('applications')
            ->latest()
            ->paginate((int) $request->get('per_page', 15));

        return $this->ok(
            $jobs->items(),
            'OK',
            [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:200'],
            'employment_type' => ['nullable', 'string', 'max:64'],
            'experience_level' => ['nullable', 'string', 'max:64'],
            'industry_type' => IndustryType::validationRule(),
            'role_category' => ['nullable', 'string', 'max:200'],
            'functional_area' => ['nullable', 'string', 'max:200'],
            'education' => ['nullable', 'string', 'max:20000'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'description' => ['required', 'string', 'max:20000'],
            'requirements' => ['nullable', 'string', 'max:20000'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:80'],
            'application_deadline_at' => ['nullable', 'date'],
            'max_applications' => ['nullable', 'integer', 'min:1', 'max:500000'],
            'assets_required' => ['nullable', 'string', 'max:500'],
            'languages' => ['nullable', 'string', 'max:500'],
            'incentive_detail' => ['nullable', 'string', 'max:500'],
            'job_timings' => ['nullable', 'string', 'max:500'],
            'working_days' => ['nullable', 'string', 'max:500'],
            'age_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_max' => ['nullable', 'integer', 'min:0', 'max:120'],
            'gender_preference' => ['nullable', 'string', 'max:64'],
            'contact_preference' => ['nullable', 'string', Rule::in(['whatsapp', 'email', 'phone_call'])],
            'contact_person' => ['nullable', 'string', 'max:200'],
            'contact_phone' => ['nullable', 'string', 'max:64'],
            'contact_email' => ['nullable', 'string', 'email', 'max:200'],
            'department' => ['nullable', 'string', 'max:200'],
            'role' => ['nullable', 'string', 'max:200'],
            'security_deposit' => ['nullable', 'boolean'],
            'interview_timings' => ['nullable', 'string', 'max:500'],
        ]);

        $slugBase = Str::slug($validated['title']);
        $slug = $this->uniqueJobSlug($company->id, $slugBase !== '' ? $slugBase : 'job');

        $initial = $this->publishing->initialStatusForNewJob($company);

        $job = JobPost::create([
            'company_id' => $company->id,
            'title' => $validated['title'],
            'slug' => $slug,
            'location' => $validated['location'] ?? null,
            'employment_type' => $validated['employment_type'] ?? null,
            'experience_level' => $validated['experience_level'] ?? null,
            'industry_type' => $validated['industry_type'] ?? null,
            'role_category' => $validated['role_category'] ?? null,
            'functional_area' => $validated['functional_area'] ?? null,
            'education' => $validated['education'] ?? null,
            'salary_min' => $validated['salary_min'] ?? null,
            'salary_max' => $validated['salary_max'] ?? null,
            'currency' => $validated['currency'] ?? 'INR',
            'description' => $validated['description'],
            'requirements' => $validated['requirements'] ?? null,
            'skills' => $validated['skills'] ?? null,
            'application_deadline_at' => $validated['application_deadline_at'] ?? null,
            'max_applications' => $validated['max_applications'] ?? null,
            'status' => $initial['status'],
            'published_at' => $initial['published_at'],
            'assets_required' => $validated['assets_required'] ?? null,
            'languages' => $validated['languages'] ?? null,
            'incentive_detail' => $validated['incentive_detail'] ?? null,
            'job_timings' => $validated['job_timings'] ?? null,
            'working_days' => $validated['working_days'] ?? null,
            'age_min' => $validated['age_min'] ?? null,
            'age_max' => $validated['age_max'] ?? null,
            'gender_preference' => $validated['gender_preference'] ?? null,
            'contact_preference' => $validated['contact_preference'] ?? 'phone_call',
            'contact_person' => $validated['contact_person'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'department' => $validated['department'] ?? null,
            'role' => $validated['role'] ?? null,
            'security_deposit' => $validated['security_deposit'] ?? false,
            'interview_timings' => $validated['interview_timings'] ?? null,
        ]);

        $message = $job->status === JobPostStatus::Published
            ? 'Job published.'
            : 'Job submitted for admin review.';

        return $this->ok($job, $message, null, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $job = JobPost::query()->where('company_id', $company->id)->where('id', $id)->first();

        if (! $job) {
            return $this->fail('Job not found.', null, 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:200'],
            'employment_type' => ['nullable', 'string', 'max:64'],
            'experience_level' => ['nullable', 'string', 'max:64'],
            'industry_type' => IndustryType::validationRule(),
            'role_category' => ['nullable', 'string', 'max:200'],
            'functional_area' => ['nullable', 'string', 'max:200'],
            'education' => ['nullable', 'string', 'max:20000'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'description' => ['sometimes', 'string', 'max:20000'],
            'requirements' => ['nullable', 'string', 'max:20000'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:80'],
            'status' => ['sometimes', 'string', Rule::in(['closed'])],
            'application_deadline_at' => ['nullable', 'date'],
            'max_applications' => ['nullable', 'integer', 'min:1', 'max:500000'],
            'assets_required' => ['nullable', 'string', 'max:500'],
            'languages' => ['nullable', 'string', 'max:500'],
            'incentive_detail' => ['nullable', 'string', 'max:500'],
            'job_timings' => ['nullable', 'string', 'max:500'],
            'working_days' => ['nullable', 'string', 'max:500'],
            'age_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_max' => ['nullable', 'integer', 'min:0', 'max:120'],
            'gender_preference' => ['nullable', 'string', 'max:64'],
            'contact_preference' => ['nullable', 'string', Rule::in(['whatsapp', 'email', 'phone_call'])],
            'contact_person' => ['nullable', 'string', 'max:200'],
            'contact_phone' => ['nullable', 'string', 'max:64'],
            'contact_email' => ['nullable', 'string', 'email', 'max:200'],
            'department' => ['nullable', 'string', 'max:200'],
            'role' => ['nullable', 'string', 'max:200'],
            'security_deposit' => ['nullable', 'boolean'],
            'interview_timings' => ['nullable', 'string', 'max:500'],
        ]);

        $closing = isset($validated['status']) && $validated['status'] === 'closed';
        if ($closing) {
            unset($validated['status']);
            if (! in_array($job->status, [JobPostStatus::Published, JobPostStatus::PendingReview, JobPostStatus::Draft], true)) {
                return $this->fail('This job cannot be closed.', null, 422);
            }
            $job->status = JobPostStatus::Closed;
            $job->save();

            return $this->ok($job->fresh(), 'Job closed.');
        }

        if (in_array($job->status, [JobPostStatus::Closed, JobPostStatus::Rejected], true)) {
            return $this->fail('This job cannot be edited.', null, 422);
        }

        if (array_key_exists('max_applications', $validated) && $validated['max_applications'] !== null) {
            $currentApps = $job->applications()->count();
            if ($validated['max_applications'] < $currentApps) {
                return $this->fail(
                    "Max applications cannot be less than current applications ($currentApps).",
                    null,
                    422
                );
            }
        }

        if (isset($validated['title'])) {
            $slugBase = Str::slug($validated['title']);
            $job->slug = $this->uniqueJobSlug($company->id, $slugBase !== '' ? $slugBase : 'job', $job->id);
        }

        $job->fill($validated);
        $job->save();

        return $this->ok($job->fresh(), 'Job updated.');
    }

    private function uniqueJobSlug(int $companyId, string $base, ?int $exceptId = null): string
    {
        $candidate = $base;
        $i = 0;

        $query = JobPost::query()->where('company_id', $companyId)->where('slug', $candidate);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        while ($query->exists()) {
            $candidate = $base.'-'.(++$i);
            $query = JobPost::query()->where('company_id', $companyId)->where('slug', $candidate);
            if ($exceptId) {
                $query->where('id', '!=', $exceptId);
            }
        }

        return $candidate;
    }
}
