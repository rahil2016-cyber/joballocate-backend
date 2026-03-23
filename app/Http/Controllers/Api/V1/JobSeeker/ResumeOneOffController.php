<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use App\Models\SeekerPackagePurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pay-as-you-go resume build (₹20 demo — no real payment gateway).
 */
class ResumeOneOffController extends Controller
{
    use ApiResponses;

    public const PRICE_INR = 20;

    public const PACKAGE_KEY = 'payg_resume_20';

    public function purchase(Request $request): JsonResponse
    {
        $user = $request->user();

        return DB::transaction(function () use ($user): JsonResponse {
            $profile = JobSeekerProfile::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $profile) {
                JobSeekerProfile::create(['user_id' => $user->id]);
                $profile = JobSeekerProfile::query()
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            if ($profile->canBuildResume()) {
                return $this->fail(
                    'You already have active resume credits from a plan. No extra payment is needed.',
                    null,
                    422
                );
            }

            $expiresAt = now()->addDays(30);

            $profile->resume_builds_remaining = (int) ($profile->resume_builds_remaining ?? 0) + 1;
            $profile->resume_credits_expires_at = $expiresAt;
            $profile->resume_package_key = self::PACKAGE_KEY;
            $profile->save();

            SeekerPackagePurchase::query()->create([
                'user_id' => $user->id,
                'seeker_package_id' => null,
                'package_key' => self::PACKAGE_KEY,
                'title' => 'Single resume (₹'.self::PRICE_INR.')',
                'kind' => 'resume',
                'price_inr' => self::PRICE_INR,
                'duration_days' => 30,
                'applications_granted' => 0,
                'resume_builds_granted' => 1,
                'activated_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            return $this->ok($profile->fresh(), '₹'.self::PRICE_INR.' (demo) — 1 resume build added.');
        });
    }
}
