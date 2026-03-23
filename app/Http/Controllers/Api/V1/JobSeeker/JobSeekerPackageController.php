<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use App\Models\SeekerPackage;
use App\Models\SeekerPackagePurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSeekerPackageController extends Controller
{
    use ApiResponses;

    /** Public catalog for mobile (loaded from DB; seeded by SeekerPackageSeeder). */
    public function catalog(): JsonResponse
    {
        $rows = SeekerPackage::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (SeekerPackage $p) => [
                'key' => $p->key,
                'title' => $p->title,
                'description' => $p->description,
                'kind' => $p->kind,
                'price_inr' => (int) $p->price_inr,
                'list_price_inr' => $p->list_price_inr !== null ? (int) $p->list_price_inr : null,
                'duration_days' => $p->duration_days,
                'applications_included' => (int) $p->applications_included,
                'resume_builds_included' => (int) $p->resume_builds_included,
            ])
            ->values()
            ->all();

        return $this->ok($rows, 'OK');
    }

    /**
     * Paginated history of plan activations for this account (survives reinstall; tied to user_id).
     */
    public function purchases(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 50);
        $page = max(1, (int) $request->query('page', 1));

        $paginator = SeekerPackagePurchase::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('activated_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(fn (SeekerPackagePurchase $p) => [
            'id' => $p->id,
            'package_key' => $p->package_key,
            'title' => $p->title,
            'kind' => $p->kind,
            'price_inr' => $p->price_inr,
            'duration_days' => $p->duration_days,
            'applications_granted' => $p->applications_granted,
            'resume_builds_granted' => $p->resume_builds_granted,
            'activated_at' => $p->activated_at?->toIso8601String(),
            'expires_at' => $p->expires_at?->toIso8601String(),
        ])->values()->all();

        return $this->ok($items, 'OK', [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ]);
    }

    /**
     * Activate a package without real payment (placeholder until gateway is integrated).
     * Job-only and resume-only plans update only their track so the other track is preserved.
     */
    public function select(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'package_key' => ['required', 'string', 'max:64'],
        ]);

        $user = $request->user();
        $profile = $user->jobSeekerProfile;

        if (! $profile) {
            $profile = JobSeekerProfile::create([
                'user_id' => $user->id,
            ]);
        }

        $pkg = SeekerPackage::query()
            ->where('key', $validated['package_key'])
            ->where('is_active', true)
            ->firstOrFail();

        $activatedAt = now();
        $expiresAt = $activatedAt->copy()->addDays($pkg->duration_days);

        $profile->package_key = $pkg->key;
        $profile->package_activated_at = $activatedAt;
        $profile->package_expires_at = $expiresAt;

        switch ($pkg->kind) {
            case 'resume':
                $profile->resume_builds_remaining = (int) $pkg->resume_builds_included;
                $profile->resume_credits_expires_at = $expiresAt;
                $profile->resume_package_key = $pkg->key;
                break;
            case 'combo':
                $profile->applications_remaining = (int) $pkg->applications_included;
                $profile->resume_builds_remaining = (int) $pkg->resume_builds_included;
                $profile->job_credits_expires_at = $expiresAt;
                $profile->resume_credits_expires_at = $expiresAt;
                $profile->job_package_key = $pkg->key;
                $profile->resume_package_key = $pkg->key;
                break;
            case 'job_applications':
            default:
                $profile->applications_remaining = (int) $pkg->applications_included;
                $profile->job_credits_expires_at = $expiresAt;
                $profile->job_package_key = $pkg->key;
                break;
        }

        $profile->save();

        SeekerPackagePurchase::query()->create([
            'user_id' => $user->id,
            'seeker_package_id' => $pkg->id,
            'package_key' => $pkg->key,
            'title' => $pkg->title,
            'kind' => $pkg->kind,
            'price_inr' => $pkg->price_inr,
            'duration_days' => $pkg->duration_days,
            'applications_granted' => (int) $pkg->applications_included,
            'resume_builds_granted' => (int) $pkg->resume_builds_included,
            'activated_at' => $activatedAt,
            'expires_at' => $expiresAt,
        ]);

        return $this->ok($profile->fresh(), 'Package activated. Credits are updated on your profile.');
    }
}
