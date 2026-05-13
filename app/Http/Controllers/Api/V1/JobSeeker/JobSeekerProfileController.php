<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Support\Base64Image;
use App\Support\Identifier;
use App\Models\IndustryType;
use App\Support\ProfileCompletion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $this->appendMobileAliases($data);
        $data['profile_completion_percent'] = ProfileCompletion::seekerPercent($user, $profile);
        $this->mergeContactFromUser($data, $user);

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
            'education.*.study_mode' => ['nullable', 'string', 'max:64'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'expected_salary_min' => ['nullable', 'integer', 'min:0'],
            'expected_salary_max' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'industry_type' => IndustryType::validationRule(),
            'date_of_birth' => ['nullable', 'date'],
            // Mobile app currently sends `dob`; normalize into `date_of_birth`.
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', Rule::in(['Male', 'Female', 'Prefer not to say'])],
            'portfolio_url' => ['nullable', 'url', 'max:500'],
            'internships' => ['nullable', 'array', 'max:50'],
            'internships.*.organization' => ['nullable', 'string', 'max:200'],
            'internships.*.role' => ['nullable', 'string', 'max:200'],
            'internships.*.duration' => ['nullable', 'string', 'max:120'],
            'internships.*.description' => ['nullable', 'string', 'max:5000'],
            'projects' => ['nullable', 'array', 'max:50'],
            'projects.*.title' => ['nullable', 'string', 'max:200'],
            'projects.*.link' => ['nullable', 'string', 'max:500'],
            'projects.*.description' => ['nullable', 'string', 'max:5000'],
            'achievements' => ['nullable', 'array', 'max:100'],
            'achievements.*' => ['string', 'max:300'],
            'resume_document' => ['nullable', 'array'],
            'hometown' => ['nullable', 'string', 'max:160'],
            'residing_in_india' => ['sometimes', 'boolean'],
            'highest_qualification' => ['nullable', 'string', 'max:64'],
            'work_experience' => ['nullable', 'array', 'max:40'],
            'work_experience.*' => ['nullable', 'array'],
            'work_experience.*.id' => ['nullable', 'string', 'max:64'],
            'work_experience.*.company_name' => ['nullable', 'string', 'max:300'],
            'work_experience.*.date_range' => ['nullable', 'string', 'max:200'],
            'work_experience.*.bullets' => ['nullable', 'array', 'max:40'],
            'work_experience.*.bullets.*' => ['nullable', 'string', 'max:2000'],
            'languages_known' => ['nullable', 'array', 'max:40'],
            'languages_known.*' => ['nullable', 'array'],
            'languages_known.*.language' => ['nullable', 'string', 'max:80'],
            'languages_known.*.proficiency' => ['nullable', 'string', 'max:80'],
            'certifications_structured' => ['nullable', 'array', 'max:40'],
            'certifications_structured.*' => ['nullable', 'array'],
            'certifications_structured.*.name' => ['nullable', 'string', 'max:200'],
            'certifications_structured.*.date' => ['nullable', 'string', 'max:64'],
            'academic_achievements' => ['nullable', 'array', 'max:40'],
            'academic_achievements.*.title' => ['nullable', 'string', 'max:200'],
            'academic_achievements.*.detail' => ['nullable', 'string', 'max:2000'],
            'awards_honors' => ['nullable', 'array', 'max:40'],
            'awards_honors.*.title' => ['nullable', 'string', 'max:200'],
            'awards_honors.*.detail' => ['nullable', 'string', 'max:2000'],
            'competitive_exam_results' => ['nullable', 'array', 'max:40'],
            'competitive_exam_results.*.exam' => ['nullable', 'string', 'max:200'],
            'competitive_exam_results.*.result' => ['nullable', 'string', 'max:200'],
            'resume_url' => ['nullable', 'url', 'max:500'],
            'profile_photo_url' => ['nullable', 'url', 'max:500'],
            /** Raw base64 image (Flutter); stored as public file, sets `profile_photo_url`. */
            'profile_photo' => ['nullable', 'string', 'max:3500000'],
            'email' => ['sometimes', 'nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
        ]);

        $photoB64 = $validated['profile_photo'] ?? null;
        unset($validated['profile_photo']);

        if (array_key_exists('dob', $validated) && ! array_key_exists('date_of_birth', $validated)) {
            $validated['date_of_birth'] = $validated['dob'];
        }
        unset($validated['dob']);

        $user = $request->user();
        $hasEmailKey = array_key_exists('email', $validated);
        $hasPhoneKey = array_key_exists('phone', $validated);
        $emailIn = $hasEmailKey ? ($validated['email'] ?? null) : null;
        $phoneIn = $hasPhoneKey ? ($validated['phone'] ?? null) : null;
        unset($validated['email'], $validated['phone']);

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

        if ($hasEmailKey || $hasPhoneKey) {
            $this->applyUserContactUpdates($user, $emailIn, $phoneIn);
        }

        $fresh = $profile->fresh();
        $user->refresh();
        $data = $fresh->toArray();
        $this->appendMobileAliases($data);
        $data['profile_completion_percent'] = ProfileCompletion::seekerPercent($user, $fresh);
        $this->mergeContactFromUser($data, $user);

        return $this->ok($data, 'Profile updated.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function mergeContactFromUser(array &$data, User $user): void
    {
        $data['email'] = Identifier::isSyntheticEmail($user->email) ? null : $user->email;
        $data['phone'] = $user->phone;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function appendMobileAliases(array &$data): void
    {
        // Keep backward compatibility with existing Flutter payload/field names.
        $data['dob'] = $data['date_of_birth'] ?? null;
    }

    private function applyUserContactUpdates(User $user, mixed $emailIn, mixed $phoneIn): void
    {
        $dirty = false;

        if ($emailIn !== null) {
            $trim = is_string($emailIn) ? trim($emailIn) : '';
            if ($trim === '') {
                if (filled($user->phone)) {
                    $digits = preg_replace('/\D/', '', (string) $user->phone) ?? '';
                    if ($digits !== '') {
                        $user->email = Identifier::syntheticEmailFromPhone($digits);
                        $dirty = true;
                    }
                }
            } else {
                $user->email = strtolower($trim);
                $dirty = true;
            }
        }

        if ($phoneIn !== null) {
            $raw = is_string($phoneIn) ? $phoneIn : '';
            $digits = preg_replace('/\D/', '', $raw) ?? '';
            $newPhone = $digits !== '' ? $digits : null;
            if ($newPhone !== $user->phone) {
                if ($newPhone !== null && User::query()->where('phone', $newPhone)->where('id', '!=', $user->id)->exists()) {
                    throw ValidationException::withMessages([
                        'phone' => ['This phone number is already used by another account.'],
                    ]);
                }
                $user->phone = $newPhone;
                $dirty = true;
            }
        }

        if ($dirty) {
            if (Identifier::isSyntheticEmail($user->email) && filled($user->phone)) {
                $d = preg_replace('/\D/', '', (string) $user->phone) ?? '';
                if ($d !== '') {
                    $user->email = Identifier::syntheticEmailFromPhone($d);
                }
            }
            $user->save();
        }
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
        $this->appendMobileAliases($data);
        $data['profile_completion_percent'] = ProfileCompletion::seekerPercent($user, $fresh);
        $this->mergeContactFromUser($data, $user);

        return $this->ok($data, 'Resume uploaded.');
    }
}
