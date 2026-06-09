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
            'resume_url' => ['nullable', 'url', 'max:500'],
            'profile_photo_url' => ['nullable', 'url', 'max:500'],
            /** Raw base64 image (Flutter); stored as public file, sets `profile_photo_url`. */
            'profile_photo' => ['nullable', 'string', 'max:3500000'],
            'email' => ['sometimes', 'nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'onboarded' => ['sometimes', 'boolean'],
            'job_roles' => ['nullable', 'array'],
            'job_roles.*' => ['string', 'max:120'],
            'is_experienced' => ['nullable', 'boolean'],
            'current_company' => ['nullable', 'string', 'max:200'],
            'current_role' => ['nullable', 'string', 'max:200'],
            'preferred_locations' => ['nullable', 'array'],
            'preferred_locations.*' => ['string', 'max:120'],
            'willing_to_relocate' => ['nullable', 'boolean'],
            'employment_preferences' => ['nullable', 'array'],
            'employment_preferences.*' => ['string', 'max:120'],
            'expected_salary' => ['nullable', 'integer', 'min:0'],
            'onboarding_step' => ['sometimes', 'integer', 'min:1', 'max:11'],
            'current_status' => ['nullable', 'string', 'max:64'],
        ]);

        $photoB64 = $validated['profile_photo'] ?? null;
        unset($validated['profile_photo']);

        $user = $request->user();
        $hasEmailKey = array_key_exists('email', $validated);
        $hasPhoneKey = array_key_exists('phone', $validated);
        $emailIn = $hasEmailKey ? ($validated['email'] ?? null) : null;
        $phoneIn = $hasPhoneKey ? ($validated['phone'] ?? null) : null;
        unset($validated['email'], $validated['phone']);

        $hasNameKey = array_key_exists('name', $validated);
        $nameIn = $hasNameKey ? ($validated['name'] ?? null) : null;
        unset($validated['name']);

        if ($hasNameKey && $nameIn !== null) {
            $user->name = $nameIn;
            $user->save();
        }

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
        $data['profile_completion_percent'] = ProfileCompletion::seekerPercent($user, $fresh);
        $this->mergeContactFromUser($data, $user);

        return $this->ok($data, 'Profile updated.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function mergeContactFromUser(array &$data, User $user): void
    {
        $data['name'] = $user->name;
        $data['email'] = Identifier::isSyntheticEmail($user->email) ? null : $user->email;
        $data['phone'] = $user->phone;
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
            'resume' => [
                'required',
                'file',
                'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'max:5120'
            ], // 5MB
        ]);

        /** @var UploadedFile $file */
        $file = $validated['resume'];

        $ext = $file->getClientOriginalExtension();
        if (empty($ext)) {
            $ext = 'pdf';
        }

        $name = 'resume_'.$request->user()->id.'_'.time().'.'.$ext;
        $path = $file->storeAs('resumes', $name, 'public');

        $profile->resume_url = url('/media/resumes/'.basename($path));
        $profile->save();

        $fresh = $profile->fresh();
        $user = $request->user();
        $data = $fresh->toArray();
        $data['profile_completion_percent'] = ProfileCompletion::seekerPercent($user, $fresh);
        $this->mergeContactFromUser($data, $user);

        return $this->ok($data, 'Resume uploaded.');
    }
}
