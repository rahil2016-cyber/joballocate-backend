<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\BannerAd;
use Illuminate\Http\JsonResponse;

class PublicBannerController extends Controller
{
    use ApiResponses;

    public function index(): JsonResponse
    {
        $now = now();
        $rows = BannerAd::query()
            ->where('status', 'active')
            ->where(function ($q) use ($now): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (BannerAd $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'content' => $b->content,
                'target_url' => $b->target_url,
                'background_color' => $b->background_color,
                'image_url' => $b->image_path ? asset('storage/'.$b->image_path) : null,
                'starts_at' => $b->starts_at?->toIso8601String(),
                'expires_at' => $b->expires_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        return $this->ok($rows);
    }
}

