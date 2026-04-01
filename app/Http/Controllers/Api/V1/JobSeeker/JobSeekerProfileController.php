<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Support\Base64Image;
use App\Support\IndustryType;
use App\Support\ProfileCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

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
            'state' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'industry_type' => IndustryType::rule(),
            'date_of_birth' => ['nullable', 'date'],
            'resume_url' => ['nullable', 'url', 'max:500'],
            'profile_photo_url' => ['nullable', 'url', 'max:500'],
            /** Raw base64 image (Flutter); stored as public file, sets `profile_photo_url`. */
            'profile_photo' => ['nullable', 'string', 'max:3500000'],
        ]);

        $photoB64 = $validated['profile_photo'] ?? null;
        unset($validated['profile_photo']);

        $profile->fill($validated);

        if (filled($photoB64)) {
            $url = Base64Image::storeFromRawBase64(
                $photoB64,
                'profile-photos',
                'seeker-'.$request->user()->id,
            );
            if ($url === null) {
                return $this->fail('Could not save profile photo. Use a JPEG, PNG, WebP, or GIF under ~2MB.', [
                    'profile_photo' => ['Invalid image data.'],
                ], 422);
            }
            $profile->profile_photo_url = $url;
        }

        $profile->save();

        $fresh = $profile->fresh();
        $user = $request->user();
        $data = $fresh->toArray();
        $data['profile_completion_percent'] = ProfileCompletion::seekerPercent($user, $fresh);

        return $this->ok($data, 'Profile updated.');
    }

    public function uploadResumePdf(Request $request): JsonResponse
    {
        $profile = $request->user()->jobSeekerProfile;

        if (! $profile) {
            return $this->fail('Profile not found.', null, 404);
        }

        $validated = $request->validate([
            'resume' => ['required', 'file', 'mimetypes:application/pdf', 'max:5120'], // 5MB
        ]);

        /** @var UploadedFile $file */
        $file = $validated['resume'];

        $name = 'resume_'.$request->user()->id.'_'.time().'.pdf';
        $path = $file->storeAs('resumes', $name, 'public');

        $profile->resume_url = url('/media/resumes/'.basename($path));
        $profile->save();

        $fresh = $profile->fresh();
        $user = $request->user();
        $data = $fresh->toArray();
        $data['profile_completion_percent'] = ProfileCompletion::seekerPercent($user, $fresh);

        return $this->ok($data, 'Resume uploaded.');
    }
}
