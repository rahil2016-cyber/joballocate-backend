<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CompanyVerificationStatus;
use App\Enums\JobPostStatus;
use App\Http\Concerns\ApiResponses;
use App\Http\Concerns\TransformsPublicJobPost;
use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\IndustryType;
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
            'industry_type' => IndustryType::validationRule(),
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

    public function similar(int $id, Request $request): JsonResponse
    {
        JobPost::runAutoCloseJobs();

        $target = JobPost::query()
            ->where('id', $id)
            ->where('status', JobPostStatus::Published)
            ->whereNotNull('published_at')
            ->first();

        if (! $target) {
            return $this->fail('Job not found.', null, 404);
        }

        // Get all other published jobs
        $candidates = JobPost::query()
            ->with('company:id,name,slug,logo_url')
            ->withCount('applications')
            ->listed()
            ->where('id', '!=', $id)
            ->get();

        $targetSkills = is_array($target->skills) ? array_map('strtolower', $target->skills) : [];

        // Helper to extract keywords from title
        $tokenize = function ($title) {
            $cleaned = preg_replace('/[^a-z0-9\s]/', '', strtolower($title));
            $words = preg_split('/\s+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);
            // filter out common short words or stop words
            $stopWords = ['and', 'for', 'the', 'with', 'req', 'hiring', 'required', 'job', 'developer', 'engineer', 'manager', 'lead', 'senior', 'junior'];
            return array_values(array_filter($words, fn($w) => strlen($w) > 2 && !in_array($w, $stopWords)));
        };

        $targetTitleWords = $tokenize($target->title);

        $scoredCandidates = $candidates->map(function ($candidate) use ($target, $targetSkills, $targetTitleWords, $tokenize) {
            $score = 0.0;

            // 1. Industry Type (30%)
            if ($candidate->industry_type === $target->industry_type) {
                $score += 30.0;
            }

            // 2. Functional Area (30%)
            if ($candidate->functional_area === $target->functional_area) {
                $score += 30.0;
            }

            // 3. Skills (20%)
            $candSkills = is_array($candidate->skills) ? array_map('strtolower', $candidate->skills) : [];
            if (empty($targetSkills) && empty($candSkills)) {
                $score += 20.0;
            } elseif (!empty($targetSkills) && !empty($candSkills)) {
                $intersection = array_intersect($targetSkills, $candSkills);
                $skillsMatchPercent = count($intersection) / count($targetSkills);
                $score += $skillsMatchPercent * 20.0;
            }

            // 4. Title words (20%)
            $candTitleWords = $tokenize($candidate->title);
            if (empty($targetTitleWords) && empty($candTitleWords)) {
                $score += 20.0;
            } elseif (!empty($targetTitleWords) && !empty($candTitleWords)) {
                $intersection = array_intersect($targetTitleWords, $candTitleWords);
                $titleMatchPercent = count($intersection) / count($targetTitleWords);
                $score += $titleMatchPercent * 20.0;
            }

            return [
                'job' => $candidate,
                'score' => $score,
            ];
        });

        // Filter for at least 40% match, or top ones if none are >= 40%
        $filtered = $scoredCandidates->filter(fn($item) => $item['score'] >= 40.0)
            ->sortByDesc('score')
            ->values();

        if ($filtered->isEmpty() && !$scoredCandidates->isEmpty()) {
            $filtered = $scoredCandidates->sortByDesc('score')
                ->take(5)
                ->values();
        }

        $results = $filtered->map(fn($item) => $this->transformListedJob($item['job']))->all();

        return $this->ok($results);
    }
}
