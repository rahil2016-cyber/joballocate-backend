<?php

use App\Http\Controllers\Api\V1\Admin\AdminCompanyController;
use App\Http\Controllers\Api\V1\Admin\AdminCompanyCouponController;
use App\Http\Controllers\Api\V1\Admin\AdminCompanySubscriptionPackageController;
use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminJobPostController;
use App\Http\Controllers\Api\V1\Admin\AdminAnalyticsController;
use App\Http\Controllers\Api\V1\Admin\AdminBannerAdController;
use App\Http\Controllers\Api\V1\Admin\AdminPurchaseController;
use App\Http\Controllers\Api\V1\Admin\AdminSeekerPackageController;
use App\Http\Controllers\Api\V1\Admin\AdminSettingController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Company\CompanyApplicationController;
use App\Http\Controllers\Api\V1\Company\CompanyJobPostController;
use App\Http\Controllers\Api\V1\Company\CompanyProfileController;
use App\Http\Controllers\Api\V1\Company\CompanySubscriptionController;
use App\Http\Controllers\Api\V1\JobSeeker\JobSeekerPackageController;
use App\Http\Controllers\Api\V1\JobSeeker\JobSeekerProfileController;
use App\Http\Controllers\Api\V1\JobSeeker\ResumeDraftController;
use App\Http\Controllers\Api\V1\JobSeeker\ResumeAiController;
use App\Http\Controllers\Api\V1\JobSeeker\ResumeOneOffController;
use App\Http\Controllers\Api\V1\JobSeeker\SeekerActivityController;
use App\Http\Controllers\Api\V1\JobSeeker\SeekerApplicationController;
use App\Http\Controllers\Api\V1\JobSeeker\SeekerSavedJobController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\PublicBannerController;
use App\Http\Controllers\Api\V1\PublicLocationController;
use App\Http\Controllers\Api\V1\PublicJobController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/admin-login', [AuthController::class, 'adminLogin'])->middleware('throttle:20,1');
    Route::post('auth/send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:20,1');
    Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:30,1');

    Route::get('jobs', [PublicJobController::class, 'index']);
    Route::get('jobs/{id}', [PublicJobController::class, 'show'])->whereNumber('id');
    Route::get('banners', [PublicBannerController::class, 'index']);

    // Location dropdown data (Full India)
    Route::get('locations/states', [PublicLocationController::class, 'states']);
    Route::get('locations/districts', [PublicLocationController::class, 'districts']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);
        Route::get('me', MeController::class);

        Route::prefix('company')->middleware('role:company')->group(function () {
            Route::get('profile', [CompanyProfileController::class, 'show']);
            Route::put('profile', [CompanyProfileController::class, 'update']);
            Route::get('subscription/offer', [CompanySubscriptionController::class, 'offer']);
            Route::post('subscription/purchase', [CompanySubscriptionController::class, 'purchase']);
            Route::get('job-posts', [CompanyJobPostController::class, 'index']);
            Route::post('job-posts', [CompanyJobPostController::class, 'store']);
            Route::put('job-posts/{id}', [CompanyJobPostController::class, 'update'])->whereNumber('id');
            Route::get('job-posts/{jobId}/applications', [CompanyApplicationController::class, 'index'])->whereNumber('jobId');
            Route::patch('job-posts/{jobId}/applications/{applicationId}', [CompanyApplicationController::class, 'updateStatus'])
                ->whereNumber('jobId')
                ->whereNumber('applicationId');
        });

        Route::prefix('job-seeker')->middleware('role:job_seeker')->group(function () {
            Route::get('profile', [JobSeekerProfileController::class, 'show']);
            Route::put('profile', [JobSeekerProfileController::class, 'update']);
            Route::get('packages/catalog', [JobSeekerPackageController::class, 'catalog']);
            Route::get('packages/purchases', [JobSeekerPackageController::class, 'purchases']);
            Route::post('packages/select', [JobSeekerPackageController::class, 'select']);
            Route::get('applications', [SeekerApplicationController::class, 'index']);
            Route::delete('applications/{applicationId}', [SeekerApplicationController::class, 'destroy'])
                ->whereNumber('applicationId');
            Route::post('jobs/{jobId}/apply', [SeekerApplicationController::class, 'store'])->whereNumber('jobId');
            Route::get('saved-jobs', [SeekerSavedJobController::class, 'index']);
            Route::post('jobs/{jobId}/save', [SeekerSavedJobController::class, 'store'])->whereNumber('jobId');
            Route::delete('jobs/{jobId}/save', [SeekerSavedJobController::class, 'destroy'])->whereNumber('jobId');
            Route::post('activity/time', [SeekerActivityController::class, 'addTime'])
                ->middleware('throttle:120,1');
            Route::post('resume/ai-assist', [ResumeAiController::class, 'assist'])
                ->middleware('throttle:15,1');
            Route::post('resume/one-off-purchase', [ResumeOneOffController::class, 'purchase'])
                ->middleware('throttle:20,1');
            Route::get('resume/drafts', [ResumeDraftController::class, 'index']);
            Route::post('resume/primary', [ResumeDraftController::class, 'setPrimary'])
                ->middleware('throttle:60,1');
            Route::post('resume/save', [ResumeDraftController::class, 'store'])
                ->middleware('throttle:60,1');
        });

        Route::prefix('admin')->middleware('role:super_admin')->group(function () {
            Route::get('dashboard', AdminDashboardController::class);
            Route::get('companies', [AdminCompanyController::class, 'index']);
            Route::get('companies/{companyId}', [AdminCompanyController::class, 'show'])->whereNumber('companyId');
            Route::patch('companies/{companyId}/verification', [AdminCompanyController::class, 'updateVerification'])->whereNumber('companyId');
            Route::patch('companies/{companyId}/owner-status', [AdminCompanyController::class, 'updateOwnerStatus'])->whereNumber('companyId');
            Route::get('job-posts', [AdminJobPostController::class, 'index']);
            Route::get('job-posts/{jobId}', [AdminJobPostController::class, 'show'])->whereNumber('jobId');
            Route::patch('job-posts/{jobId}/moderation', [AdminJobPostController::class, 'moderate'])->whereNumber('jobId');
            Route::get('users', [AdminUserController::class, 'index']);
            Route::get('users/{userId}', [AdminUserController::class, 'show'])->whereNumber('userId');
            Route::patch('users/{userId}/status', [AdminUserController::class, 'updateStatus'])->whereNumber('userId');
            Route::get('purchases', [AdminPurchaseController::class, 'index']);
            Route::get('seeker-packages', [AdminSeekerPackageController::class, 'index']);
            Route::post('seeker-packages', [AdminSeekerPackageController::class, 'store']);
            Route::patch('seeker-packages/{packageId}', [AdminSeekerPackageController::class, 'update'])->whereNumber('packageId');
            Route::delete('seeker-packages/{packageId}', [AdminSeekerPackageController::class, 'destroy'])->whereNumber('packageId');
            Route::get('analytics/overview', [AdminAnalyticsController::class, 'overview']);
            Route::get('analytics/job-seekers', [AdminAnalyticsController::class, 'seekerUsage']);
            Route::get('settings/moderation', [AdminSettingController::class, 'showModeration']);
            Route::patch('settings/moderation', [AdminSettingController::class, 'updateModeration']);
            Route::get('banner-ads', [AdminBannerAdController::class, 'index']);
            Route::post('banner-ads', [AdminBannerAdController::class, 'store']);
            Route::patch('banner-ads/{bannerId}', [AdminBannerAdController::class, 'update'])->whereNumber('bannerId');
            Route::patch('banner-ads/{bannerId}/start', [AdminBannerAdController::class, 'start'])->whereNumber('bannerId');
            Route::patch('banner-ads/{bannerId}/stop', [AdminBannerAdController::class, 'stop'])->whereNumber('bannerId');
            Route::delete('banner-ads/{bannerId}', [AdminBannerAdController::class, 'destroy'])->whereNumber('bannerId');

            // Company coupons (targeted by state/district)
            Route::get('company-coupons', [AdminCompanyCouponController::class, 'index']);
            Route::post('company-coupons', [AdminCompanyCouponController::class, 'store']);
            Route::delete('company-coupons/{couponId}', [AdminCompanyCouponController::class, 'destroy'])
                ->whereNumber('couponId');

            // Company subscription packages + coupons inside each package
            Route::get('company-packages', [AdminCompanySubscriptionPackageController::class, 'index']);
            Route::post('company-packages', [AdminCompanySubscriptionPackageController::class, 'store']);
            Route::patch('company-packages/{packageId}', [AdminCompanySubscriptionPackageController::class, 'update'])
                ->whereNumber('packageId');
            Route::delete('company-packages/{packageId}', [AdminCompanySubscriptionPackageController::class, 'destroy'])
                ->whereNumber('packageId');
            Route::get('company-packages/{packageId}/coupons', [AdminCompanySubscriptionPackageController::class, 'coupons'])
                ->whereNumber('packageId');
        });
    });
});
