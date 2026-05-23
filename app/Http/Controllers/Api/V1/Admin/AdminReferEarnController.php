<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\AudiencePromoCode;
use App\Services\PlatformSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminReferEarnController extends Controller
{
    use ApiResponses;

    public function __construct(
        private readonly PlatformSettingService $settings
    ) {}

    public function showSettings(): JsonResponse
    {
        return $this->ok($this->settings->referEarnSettings());
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_seeker_enabled' => ['sometimes', 'boolean'],
            'company_enabled' => ['sometimes', 'boolean'],
            'job_seeker_benefits_text' => ['sometimes', 'string', 'max:5000'],
            'company_benefits_text' => ['sometimes', 'string', 'max:5000'],
            'app_download_url' => ['nullable', 'string', 'max:500'],
            'deep_link_scheme' => ['sometimes', 'string', 'max:32', 'regex:/^[a-z][a-z0-9+.-]*$/i'],
            'job_share_web_base_url' => ['nullable', 'string', 'max:500'],
        ]);

        $updated = $this->settings->updateReferEarnSettings(
            $validated,
            $request->user()?->id
        );

        return $this->ok($updated, 'Refer & earn settings updated.');
    }

    public function indexPromoCodes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'audience' => ['nullable', Rule::in([AudiencePromoCode::AUDIENCE_JOB_SEEKER, AudiencePromoCode::AUDIENCE_COMPANY])],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $q = AudiencePromoCode::query()->orderByDesc('id');

        if (! empty($validated['audience'] ?? null)) {
            $q->where('audience', $validated['audience']);
        }
        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term): void {
                $query->where('code', 'like', $term)->orWhere('label', 'like', $term);
            });
        }

        $rows = $q->limit(200)->get()->map(fn (AudiencePromoCode $r) => $this->promoRow($r))->values()->all();

        return $this->ok($rows);
    }

    public function storePromoCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'audience' => ['required', Rule::in([AudiencePromoCode::AUDIENCE_JOB_SEEKER, AudiencePromoCode::AUDIENCE_COMPANY])],
            'label' => ['nullable', 'string', 'max:120'],
            'benefit_description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'max_redemptions' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        $code = strtoupper(preg_replace('/\s+/', '', $validated['code']) ?? '');

        $row = AudiencePromoCode::query()->create([
            'code' => $code,
            'audience' => $validated['audience'],
            'label' => $validated['label'] ?? null,
            'benefit_description' => $validated['benefit_description'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'max_redemptions' => $validated['max_redemptions'] ?? null,
            'redemptions_count' => 0,
        ]);

        return $this->ok($this->promoRow($row), 'Promo code created.', null, 201);
    }

    public function updatePromoCode(Request $request, int $id): JsonResponse
    {
        $row = AudiencePromoCode::query()->find($id);
        if (! $row) {
            return $this->fail('Promo code not found.', null, 404);
        }

        $validated = $request->validate([
            'label' => ['sometimes', 'nullable', 'string', 'max:120'],
            'benefit_description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'max_redemptions' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        foreach (['label', 'benefit_description', 'is_active', 'max_redemptions'] as $f) {
            if (array_key_exists($f, $validated)) {
                $row->{$f} = $validated[$f];
            }
        }
        $row->save();

        return $this->ok($this->promoRow($row->fresh()), 'Promo code updated.');
    }

    public function destroyPromoCode(int $id): JsonResponse
    {
        $row = AudiencePromoCode::query()->find($id);
        if (! $row) {
            return $this->fail('Promo code not found.', null, 404);
        }
        $row->delete();

        return $this->ok(null, 'Promo code deleted.');
    }

    private function promoRow(AudiencePromoCode $r): array
    {
        return [
            'id' => $r->id,
            'code' => $r->code,
            'audience' => $r->audience,
            'label' => $r->label,
            'benefit_description' => $r->benefit_description,
            'is_active' => (bool) $r->is_active,
            'max_redemptions' => $r->max_redemptions,
            'redemptions_count' => (int) $r->redemptions_count,
            'created_at' => $r->created_at?->toIso8601String(),
        ];
    }
}
