<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Support\IndustryType;
use App\Support\ProfileCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSeekerProfileController extends Controller
{
    use ApiResponses;

    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->jobSeekerProfile;

        if (! $profile) {
            return $this->fail('Profile not found.', null, 404);
        }

        $profile->loadMissing('primaryResumeDraft');

        $user = $request->user();
        $data = $profile->toArray();
        $data['profile_completion_percent'] = ProfileCompletion::seekerPercent($user, $profile);

        return $this->ok($data);
    }

    public function update(Request $request): JsonResponse
    {
        $profile = $request->user()->jobSeekerProfile;

        if (! $profile) {
            return $this->fail('Profile not found.', null, 404);
        }

        $validated = $request->validate([
            'headline' => ['nullable', 'string', 'max:200'],
            'bio' => ['nullable', 'string', 'max:10000'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:80'],
            'education' => ['nullable', 'array', 'max:25'],
            'education.*.title' => ['nullable', 'string', 'max:200'],
            'education.*.institution' => ['nullable', 'string', 'max:200'],
            'education.*.board_or_stream' => ['nullable', 'string', 'max:200'],
            'education.*.marks_or_grade' => ['nullable', 'string', 'max:120'],
            'education.*.year_completed' => ['nullable', 'string', 'max:32'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'expected_salary_min' => ['nullable', 'integer', 'min:0'],
            'expected_salary_max' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'industry_type' => IndustryType::rule(),
            'date_of_birth' => ['nullable', 'date'],
            'resume_url' => ['nullable', 'url', 'max:500'],
        ]);

        $profile->fill($validated);
        $profile->save();

        $fresh = $profile->fresh();
        $user = $request->user();
        $data = $fresh->toArray();
        $data['profile_completion_percent'] = ProfileCompletion::seekerPercent($user, $fresh);

        return $this->ok($data, 'Profile updated.');
    }
}
