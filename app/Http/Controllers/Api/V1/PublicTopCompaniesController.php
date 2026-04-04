<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CompanyVerificationStatus;
use App\Enums\JobPostStatus;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicTopCompaniesController extends Controller
{
    use ApiResponses;

    /** Verified companies with at least one published job, ordered by open role count. */
    public function index(Request $request): JsonResponse
    {
        $limit = min(30, max(1, (int) $request->get('limit', 12)));

        JobPost::runAutoCloseJobs();

        $rows = Company::query()
            ->where('verification_status', CompanyVerificationStatus::Verified)
            ->withCount([
                'jobPosts as open_jobs_count' => function ($q): void {
                    $q->where('status', JobPostStatus::Published)
                        ->whereNotNull('published_at');
                },
            ])
            ->having('open_jobs_count', '>', 0)
            ->orderByDesc('open_jobs_count')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'logo_url']);

        $data = $rows->map(fn (Company $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'logo_url' => $c->logo_url,
            'company_logo_url' => $c->company_logo_url,
            'open_jobs_count' => (int) $c->open_jobs_count,
        ])->values()->all();

        return $this->ok($data, 'OK');
    }
}
