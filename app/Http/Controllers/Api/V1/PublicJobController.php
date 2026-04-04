<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CompanyVerificationStatus;
use App\Enums\JobPostStatus;
use App\Http\Concerns\ApiResponses;
use App\Http\Concerns\TransformsPublicJobPost;
use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Support\IndustryType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicJobController extends Controller
{
    use ApiResponses;
    use TransformsPublicJobPost;

    public function index(Request $request): JsonResponse
    {
        JobPost::runAutoCloseJobs();

        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:200'],
            'location' => ['sometimes', 'string', 'max:120'],
            'industry_type' => IndustryType::rule(),
            'company_id' => ['sometimes', 'integer', 'exists:companies,id'],
            'published_after' => ['sometimes', 'string', 'max:40'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $q = JobPost::query()
            ->with('company:id,name,slug,logo_url')
            ->withCount('applications')
            ->listed()
            ->latest('published_at');

        $fromTop = filter_var($request->query('from_top_companies', false), FILTER_VALIDATE_BOOLEAN);
        if ($fromTop) {
            $q->whereHas('company', function ($cq): void {
                $cq->where('is_top_company', true)
                    ->where('verification_status', CompanyVerificationStatus::Verified);
            });
        }

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

        if (! empty($validated['company_id'] ?? null)) {
            $q->where('company_id', (int) $validated['company_id']);
        }

        if (! empty($validated['published_after'] ?? null)) {
            try {
                $q->where('published_at', '>=', Carbon::parse($validated['published_after'])->startOfDay());
            } catch (\Throwable) {
                // ignore invalid date
            }
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

        return $this->ok($this->transformListedJob($job));
    }
}
