<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\IndustryType;
use Illuminate\Http\JsonResponse;

class PublicIndustryTypeController extends Controller
{
    use ApiResponses;

    public function index(): JsonResponse
    {
        $rows = IndustryType::query()
            ->activeOrdered()
            ->get(['key', 'label', 'sort_order']);

        $data = $rows->map(fn (IndustryType $r) => [
            'key' => $r->key,
            'label' => $r->label,
            'sort_order' => (int) $r->sort_order,
        ])->values()->all();

        return $this->ok($data);
    }

    /**
     * Tiles for the job seeker app “Popular categories” strip (admin-configured).
     * Includes rows with {@see IndustryType::$show_on_seeker_home}; may include
     * inactive keys used only for keyword browse (no job posts use that key).
     */
    public function seekerHomePopular(): JsonResponse
    {
        $rows = IndustryType::query()
            ->where('show_on_seeker_home', true)
            ->select('industry_types.*')
            ->selectSub(function ($query) {
                $query->selectRaw('count(*)')
                    ->from('job_posts')
                    ->whereColumn('job_posts.industry_type', 'industry_types.key')
                    ->where('job_posts.status', \App\Enums\JobPostStatus::Published->value)
                    ->whereNotNull('job_posts.published_at');
            }, 'job_posts_count')
            ->orderByDesc('job_posts_count')
            ->orderBy('seeker_home_sort_order')
            ->orderBy('label')
            ->get();


        $data = $rows->map(function (IndustryType $r) {
            $search = $r->seeker_home_search;
            $search = is_string($search) && $search !== '' ? $search : null;
            $useIndustry = $search === null;

            return [
                'label' => $r->label,
                'industry_type' => $useIndustry ? $r->key : null,
                'search' => $search,
                'icon' => $r->seeker_home_icon,
                'accent_dot' => (bool) $r->seeker_home_accent_dot,
            ];
        })->values()->all();

        return $this->ok($data);
    }
}
