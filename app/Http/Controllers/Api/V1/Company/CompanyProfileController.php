<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Support\Base64Image;
use App\Models\IndustryType;
use App\Support\ProfileCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyProfileController extends Controller
{
    use ApiResponses;

    public function show(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $user = $request->user();
        $data = $company->toArray();
        $data['profile_completion_percent'] = ProfileCompletion::companyPercent($user, $company);

        return $this->ok($data);
    }

    public function update(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:160'],
            'company_kind' => ['sometimes', Rule::in(['company', 'consultancy'])],
            'industry' => ['nullable', 'string', 'max:120'],
            'industry_type' => IndustryType::validationRule(),
            'website' => ['nullable', 'string', 'max:255'],
            'consultancy_hiring_for' => ['nullable', 'string', 'max:160'],
            'hide_hiring_company' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:5000'],
            'gst_number' => ['nullable', 'string', 'max:32'],
            'location' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'established_year' => ['nullable', 'integer', 'min:1800', 'max:'.(int) date('Y')],
            'company_bio' => ['nullable', 'string', 'max:10000'],
            'what_we_do' => ['nullable', 'string', 'max:5000'],
            'team_members' => ['nullable', 'array', 'max:40'],
            'team_members.*.name' => ['required', 'string', 'max:120'],
            'team_members.*.role' => ['nullable', 'string', 'max:120'],
            'team_members.*.email' => ['nullable', 'string', 'max:255'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            /** Raw base64 image bytes (Flutter); stored as public file, sets `logo_url`. */
            'company_logo' => ['nullable', 'string', 'max:3500000'],
        ]);

        $companyLogo = $validated['company_logo'] ?? null;
        unset($validated['company_logo']);

        if ($validated['website'] ?? null) {
            $w = trim((string) $validated['website']);
            if ($w !== '' && ! preg_match('#^https?://#i', $w)) {
                $w = 'https://'.$w;
            }
            $validated['website'] = $w === '' ? null : $w;
            if ($validated['website'] !== null && filter_var($validated['website'], FILTER_VALIDATE_URL) === false) {
                return $this->fail('Enter a valid website URL.', ['website' => ['Invalid URL.']], 422);
            }
        }

        $company->fill($validated);

        if (filled($companyLogo)) {
            $url = Base64Image::storeFromRawBase64($companyLogo, 'company-logos', 'company-'.$company->id);
            if ($url === null) {
                return $this->fail('Could not save company logo. Use a JPEG, PNG, WebP, or GIF under ~2MB.', [
                    'company_logo' => ['Invalid image data.'],
                ], 422);
            }
            $company->logo_url = $url;
        }

        $company->save();

        $fresh = $company->fresh();
        $user = $request->user();
        $data = $fresh->toArray();
        $data['profile_completion_percent'] = ProfileCompletion::companyPercent($user, $fresh);

        return $this->ok($data, 'Company profile updated.');
    }
}
