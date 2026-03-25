<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Support\IndustryType;
use App\Support\ProfileCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'industry' => ['nullable', 'string', 'max:120'],
            'industry_type' => IndustryType::rule(),
            'website' => ['nullable', 'url', 'max:255'],
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
        ]);

        $company->fill($validated);
        $company->save();

        $fresh = $company->fresh();
        $user = $request->user();
        $data = $fresh->toArray();
        $data['profile_completion_percent'] = ProfileCompletion::companyPercent($user, $fresh);

        return $this->ok($data, 'Company profile updated.');
    }
}
