<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Services\JobPublishingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminJobPostController extends Controller
{
    use ApiResponses;

    public function __construct(
        private readonly JobPublishingService $publishing
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['draft', 'pending_review', 'published', 'rejected', 'closed'])],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $q = JobPost::query()
            ->with('company:id,name,slug,verification_status')
            ->withCount('applications')
            ->latest('id');

        if (! empty($validated['status'] ?? null)) {
            $q->where('status', $validated['status']);
        }
        if (! empty($validated['company_id'] ?? null)) {
            $q->where('company_id', (int) $validated['company_id']);
        }
        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term): void {
                $query->where('title', 'like', $term)
                    ->orWhere('location', 'like', $term)
                    ->orWhereHas('company', function ($cQ) use ($term): void {
                        $cQ->where('name', 'like', $term);
                    });
            });
        }

        $perPage = (int) ($validated['per_page'] ?? 20);
        $rows = $q->paginate($perPage);

        return $this->ok(
            $rows->items(),
            'OK',
            [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ]
        );
    }

    public function show(int $jobId): JsonResponse
    {
        $job = JobPost::query()
            ->with('company:id,name,slug,verification_status,website,industry_type')
            ->withCount('applications')
            ->find($jobId);

        if (! $job) {
            return $this->fail('Job not found.', null, 404);
        }

        return $this->ok($job);
    }

    public function moderate(Request $request, int $jobId): JsonResponse
    {
        $job = JobPost::query()->find($jobId);

        if (! $job) {
            return $this->fail('Job not found.', null, 404);
        }

        $validated = $request->validate([
            'action' => ['required', Rule::in(['publish', 'reject', 'unpublish'])],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['action'] === 'publish') {
            $this->publishing->publish($job);
            if ($validated['review_note'] ?? null) {
                $job->update(['review_note' => $validated['review_note']]);
            }

            return $this->ok($job->fresh(), 'Job published.');
        }

        if ($validated['action'] === 'unpublish') {
            $this->publishing->unpublish($job, $validated['review_note'] ?? null);

            return $this->ok($job->fresh(), 'Job unpublished.');
        }

        $this->publishing->reject($job, $validated['review_note'] ?? null);

        return $this->ok($job->fresh(), 'Job rejected.');
    }
}
