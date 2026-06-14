<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\BannerAd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicBannerController extends Controller
{
    use ApiResponses;

    /**
     * Active banners. Query `for=job_seeker` or `for=employer` limits to that app plus `all`.
     * Omit `for` or use an unknown value to return every active banner (backward compatible).
     */
    public function index(Request $request): JsonResponse
    {
        $raw = $request->query('for');
        $for = is_string($raw) && in_array($raw, ['job_seeker', 'employer'], true) ? $raw : null;

        $now = now();
        $q = BannerAd::query()
            ->where('status', 'active')
            ->where(function ($query) use ($now): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            });

        if ($for === 'job_seeker') {
            $q->whereIn('audience', ['all', 'job_seeker']);
        } elseif ($for === 'employer') {
            $q->whereIn('audience', ['all', 'employer']);
        }

        $rows = $q
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (BannerAd $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'content' => $b->content,
                'below_line' => $b->below_line,
                'target_url' => $b->target_url,
                'background_color' => $b->background_color,
                'image_url' => $b->publicImageUrl(),
                'audience' => $b->audience ?? 'all',
                'starts_at' => $b->starts_at?->toIso8601String(),
                'expires_at' => $b->expires_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        return $this->ok($rows)->header('Cache-Control', 'no-store');
    }
}

