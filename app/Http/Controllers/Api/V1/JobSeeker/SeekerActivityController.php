<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeekerActivityController extends Controller
{
    use ApiResponses;

    /**
     * Accumulate time the seeker spent in the app (client reports foreground seconds).
     */
    public function addTime(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'seconds' => ['required', 'integer', 'min:1', 'max:300'],
        ]);

        $user = $request->user();
        $profile = $user->jobSeekerProfile;

        if (! $profile) {
            $profile = JobSeekerProfile::create(['user_id' => $user->id]);
        }

        $add = (int) $validated['seconds'];
        $profile->total_time_spent_seconds = (int) $profile->total_time_spent_seconds + $add;
        $profile->last_app_activity_at = now();
        $profile->save();

        return $this->ok([
            'total_time_spent_seconds' => (int) $profile->total_time_spent_seconds,
        ], 'Time recorded.');
    }
}
