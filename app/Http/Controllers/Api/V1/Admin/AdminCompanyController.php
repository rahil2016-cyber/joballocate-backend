<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\CompanyVerificationStatus;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCompanyController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in([
                CompanyVerificationStatus::Unverified->value,
                CompanyVerificationStatus::Pending->value,
                CompanyVerificationStatus::Verified->value,
                CompanyVerificationStatus::Rejected->value,
            ])],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $q = Company::query()
            ->with('owner:id,name,email,phone,is_active')
            ->withCount('jobPosts')
            ->latest('id');

        if (! empty($validated['status'] ?? null)) {
            $q->where('verification_status', $validated['status']);
        }

        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('website', 'like', $term)
                    ->orWhereHas('owner', function ($ownerQ) use ($term): void {
                        $ownerQ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
            });
        }

        $perPage = (int) ($validated['per_page'] ?? 20);
        $rows = $q->paginate($perPage);

        return $this->ok(
            $rows->items(),
            'OK',
            [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ]
        );
    }

    public function show(int $companyId): JsonResponse
    {
        $company = Company::query()
            ->with('owner:id,name,email,phone,is_active')
            ->with(['jobPosts' => fn ($q) => $q->latest()->limit(10)])
            ->withCount(['jobPosts'])
            ->find($companyId);

        if (! $company) {
            return $this->fail('Company not found.', null, 404);
        }

        return $this->ok($company);
    }

    public function updateVerification(Request $request, int $companyId): JsonResponse
    {
        $company = Company::query()->find($companyId);

        if (! $company) {
            return $this->fail('Company not found.', null, 404);
        }

        $validated = $request->validate([
            'verification_status' => ['required', Rule::enum(CompanyVerificationStatus::class)],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $company->verification_status = $validated['verification_status'];
        $company->rejection_reason = $validated['rejection_reason'] ?? null;

        if ($company->verification_status === CompanyVerificationStatus::Verified) {
            $company->verified_at = now();
        } else {
            $company->verified_at = null;
        }

        $company->save();

        return $this->ok($company->fresh(), 'Company verification updated.');
    }

    public function updateOwnerStatus(Request $request, int $companyId): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $company = Company::query()->with('owner')->find($companyId);
        if (! $company || ! $company->owner) {
            return $this->fail('Company not found.', null, 404);
        }

        $company->owner->is_active = (bool) $validated['is_active'];
        $company->owner->save();

        return $this->ok(
            $company->fresh()->load('owner:id,name,email,phone,is_active'),
            $validated['is_active'] ? 'Company owner enabled.' : 'Company owner banned.'
        );
    }

    /** Pin company on job seeker home “top companies” carousel (verified + open jobs still required for listing). */
    public function updateTopCompany(Request $request, int $companyId): JsonResponse
    {
        $validated = $request->validate([
            'is_top_company' => ['required', 'boolean'],
        ]);

        $company = Company::query()->find($companyId);
        if (! $company) {
            return $this->fail('Company not found.', null, 404);
        }

        $company->is_top_company = (bool) $validated['is_top_company'];
        $company->save();

        return $this->ok(
            $company->fresh()->load('owner:id,name,email,phone,is_active'),
            $company->is_top_company ? 'Company marked as spotlight employer.' : 'Company removed from spotlight.'
        );
    }
}
