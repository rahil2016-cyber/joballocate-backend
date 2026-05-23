<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\AudiencePromoCode;
use App\Services\ReferEarnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicReferEarnController extends Controller
{
    use ApiResponses;

    public function __construct(
        private readonly ReferEarnService $referEarn
    ) {}

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'audience' => ['required', Rule::in([AudiencePromoCode::AUDIENCE_JOB_SEEKER, AudiencePromoCode::AUDIENCE_COMPANY])],
        ]);

        $user = $request->user('sanctum');

        return $this->ok($this->referEarn->publicPayload($validated['audience'], $user));
    }

    public function validateCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'audience' => ['required', Rule::in([AudiencePromoCode::AUDIENCE_JOB_SEEKER, AudiencePromoCode::AUDIENCE_COMPANY])],
        ]);

        $result = $this->referEarn->validateForRegistration(
            $validated['code'],
            $validated['audience']
        );

        return $this->ok($result);
    }
}
