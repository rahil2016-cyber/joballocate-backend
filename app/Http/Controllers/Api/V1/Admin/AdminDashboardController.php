<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\CompanyVerificationStatus;
use App\Enums\JobPostStatus;
use App\Enums\UserRole;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    use ApiResponses;

    public function __invoke(): JsonResponse
    {
        return $this->ok([
            'users_total' => User::query()->count(),
            'users_job_seekers' => User::query()->where('role', UserRole::JobSeeker->value)->count(),
            'users_companies' => User::query()->where('role', UserRole::Company->value)->count(),
            'companies_total' => Company::query()->count(),
            'companies_pending_verification' => Company::query()
                ->whereIn('verification_status', [
                    CompanyVerificationStatus::Pending->value,
                    CompanyVerificationStatus::Unverified->value,
                ])
                ->count(),
            'jobs_total' => JobPost::query()->count(),
            'jobs_pending_review' => JobPost::query()->where('status', JobPostStatus::PendingReview->value)->count(),
            'jobs_published' => JobPost::query()->where('status', JobPostStatus::Published->value)->count(),
            'applications_total' => Application::query()->count(),
        ]);
    }
}
