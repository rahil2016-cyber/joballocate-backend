<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\SeekerPackagePurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Demo ₹20 “pay to download PDF” — logs purchase for admin revenue; does not alter AI/build credits.
 */
class ResumePdfPurchaseController extends Controller
{
    use ApiResponses;

    public const PRICE_INR = 20;

    public const PACKAGE_KEY = 'resume_pdf_export_20';

    public function purchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resume_template_id' => ['required', 'integer', 'min:1'],
            'resume_template_title' => ['required', 'string', 'max:200'],
        ]);

        $user = $request->user();
        $profile = $user->jobSeekerProfile;

        if (!$profile || !$profile->canBuildResume()) {
            return $this->fail('Resume download is restricted to users with an active subscription.', null, 403);
        }

        $now = now();

        $row = DB::transaction(function () use ($user, $profile, $validated, $now): SeekerPackagePurchase {
            $profile->decrement('resume_builds_remaining');

            return SeekerPackagePurchase::query()->create([
                'user_id' => $user->id,
                'seeker_package_id' => null,
                'package_key' => self::PACKAGE_KEY,
                'title' => 'Resume PDF — '.$validated['resume_template_title'],
                'kind' => 'resume_pdf',
                'price_inr' => self::PRICE_INR,
                'duration_days' => 0,
                'applications_granted' => 0,
                'resume_builds_granted' => 0,
                'activated_at' => $now,
                'expires_at' => $now,
                'resume_template_id' => $validated['resume_template_id'],
                'resume_template_title' => $validated['resume_template_title'],
            ]);
        });

        return $this->ok(
            [
                'purchase_id' => $row->id,
                'price_inr' => self::PRICE_INR,
                'resume_template_id' => $row->resume_template_id,
                'resume_template_title' => $row->resume_template_title,
            ],
            'PDF export unlocked successfully.'
        );
    }
}
