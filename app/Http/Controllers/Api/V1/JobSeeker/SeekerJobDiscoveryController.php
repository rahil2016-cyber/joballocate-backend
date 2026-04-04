<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Concerns\TransformsPublicJobPost;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPost;
use App\Models\JobSeekerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SeekerJobDiscoveryController extends Controller
{
    use ApiResponses;
    use TransformsPublicJobPost;

    /** Personalized: industry → skill overlap → latest (excludes jobs already applied to). */
    public function recommended(Request $request): JsonResponse
    {
        JobPost::runAutoCloseJobs();

        $perPage = min(50, max(1, (int) $request->get('per_page', 15)));
        $user = $request->user();
        $profile = JobSeekerProfile::query()->where('user_id', $user->id)->first();
        $appliedIds = Application::query()->where('user_id', $user->id)->pluck('job_post_id')->all();

        $base = fn () => JobPost::query()
            ->with('company:id,name,slug,logo_url')
            ->withCount('applications')
            ->listed()
            ->whereNotIn('id', $appliedIds);

        /** @var array<int, true> $seen */
        $seen = [];
        $ordered = collect();

        $pushUnique = function (Collection $batch) use (&$seen, &$ordered, $perPage): void {
            foreach ($batch as $job) {
                if (count($seen) >= $perPage) {
                    return;
                }
                if (isset($seen[$job->id])) {
                    continue;
                }
                $seen[$job->id] = true;
                $ordered->push($job);
            }
        };

        if ($profile && filled($profile->industry_type)) {
            $pushUnique($base()->where('industry_type', $profile->industry_type)->latest('published_at')->limit($perPage)->get());
        }

        if ($ordered->count() < $perPage && $profile) {
            $skills = array_values(array_filter(
                array_slice($profile->skills ?? [], 0, 20),
                fn ($s) => is_string($s) && $s !== ''
            ));
            foreach ($skills as $skill) {
                if ($ordered->count() >= $perPage) {
                    break;
                }
                $remaining = $perPage - $ordered->count();
                $ids = array_keys($seen);
                $batch = $base()
                    ->when($ids !== [], fn ($q) => $q->whereNotIn('id', $ids))
                    ->whereJsonContains('skills', $skill)
                    ->latest('published_at')
                    ->limit($remaining)
                    ->get();
                $pushUnique($batch);
            }
        }

        if ($ordered->count() < $perPage) {
            $remaining = $perPage - $ordered->count();
            $ids = array_keys($seen);
            $batch = $base()
                ->when($ids !== [], fn ($q) => $q->whereNotIn('id', $ids))
                ->latest('published_at')
                ->limit($remaining)
                ->get();
            $pushUnique($batch);
        }

        $payload = $ordered->map(fn (JobPost $j) => $this->transformListedJob($j))->values()->all();

        return $this->ok($payload, 'OK');
    }

    /** Jobs at companies / industries you already applied to (excluding those applications). */
    public function relatedFromApplications(Request $request): JsonResponse
    {
        JobPost::runAutoCloseJobs();

        $perPage = min(50, max(1, (int) $request->get('per_page', 15)));
        $user = $request->user();
        $appliedIds = Application::query()->where('user_id', $user->id)->pluck('job_post_id')->all();

        if ($appliedIds === []) {
            return $this->ok([], 'OK');
        }

        $appliedPosts = JobPost::query()
            ->whereIn('id', $appliedIds)
            ->get(['id', 'company_id', 'industry_type']);

        $companyIds = $appliedPosts->pluck('company_id')->unique()->filter()->values()->all();
        $industries = $appliedPosts->pluck('industry_type')->unique()->filter()->values()->all();

        $items = JobPost::query()
            ->with('company:id,name,slug,logo_url')
            ->withCount('applications')
            ->listed()
            ->whereNotIn('id', $appliedIds)
            ->where(function ($query) use ($companyIds, $industries): void {
                $query->whereIn('company_id', $companyIds);
                if ($industries !== []) {
                    $query->orWhereIn('industry_type', $industries);
                }
            })
            ->latest('published_at')
            ->limit($perPage)
            ->get();

        $payload = $items->map(fn (JobPost $j) => $this->transformListedJob($j))->values()->all();

        return $this->ok($payload, 'OK');
    }
}
