<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Enums\ApplicationStatus;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyApplicationController extends Controller
{
    use ApiResponses;

    public function index(Request $request, int $jobId): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $job = JobPost::query()->where('company_id', $company->id)->where('id', $jobId)->first();

        if (! $job) {
            return $this->fail('Job not found.', null, 404);
        }

        $apps = Application::query()
            ->with([
                'user' => function ($query): void {
                    $query->select('id', 'name', 'email', 'phone')
                        ->with([
                            'jobSeekerProfile' => function ($q): void {
                                $q->with([
                                    'primaryResumeDraft' => function ($d): void {
                                        $d->select(
                                            'id',
                                            'user_id',
                                            'title',
                                            'template_id',
                                            'updated_at'
                                        );
                                    },
                                ]);
                            },
                        ]);
                },
            ])
            ->where('job_post_id', $job->id)
            ->latest('applied_at')
            ->paginate((int) $request->get('per_page', 30));

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

    public function updateStatus(Request $request, int $jobId, int $applicationId): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $job = JobPost::query()->where('company_id', $company->id)->where('id', $jobId)->first();

        if (! $job) {
            return $this->fail('Job not found.', null, 404);
        }

        $application = Application::query()
            ->where('job_post_id', $job->id)
            ->where('id', $applicationId)
            ->first();

        if (! $application) {
            return $this->fail('Application not found.', null, 404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'employer_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $application->status = $validated['status'];
        if (array_key_exists('employer_note', $validated)) {
            $application->employer_note = $validated['employer_note'];
        }
        $application->save();

        return $this->ok($application->fresh()->load('user:id,name,email,phone'), 'Application updated.');
    }
}
