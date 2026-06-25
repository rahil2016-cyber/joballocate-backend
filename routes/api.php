<?php

use App\Http\Controllers\Api\V1\Admin\AdminCompanyController;
use App\Http\Controllers\Api\V1\Admin\AdminCompanyCouponController;
use App\Http\Controllers\Api\V1\Admin\AdminCompanySubscriptionPackageController;
use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminJobPostController;
use App\Http\Controllers\Api\V1\Admin\AdminApplicationController;
use App\Http\Controllers\Api\V1\Admin\AdminAnalyticsController;
use App\Http\Controllers\Api\V1\Admin\AdminIndustryTypeController;
use App\Http\Controllers\Api\V1\Admin\AdminBannerAdController;
use App\Http\Controllers\Api\V1\Admin\AdminPurchaseController;
use App\Http\Controllers\Api\V1\Admin\AdminCareerContentController;
use App\Http\Controllers\Api\V1\Admin\AdminSeekerFeedbackController;
use App\Http\Controllers\Api\V1\Admin\AdminSeekerPackageController;
use App\Http\Controllers\Api\V1\Admin\AdminReferEarnController;
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
use App\Http\Controllers\Api\V1\JobSeeker\ResumeHtmlPreviewController;
use App\Http\Controllers\Api\V1\JobSeeker\ResumeAiController;
use App\Http\Controllers\Api\V1\JobSeeker\ResumePdfPurchaseController;
use App\Http\Controllers\Api\V1\JobSeeker\SeekerActivityController;
use App\Http\Controllers\Api\V1\JobSeeker\SeekerCareerAiController;
use App\Http\Controllers\Api\V1\JobSeeker\SeekerCareerContentController;
use App\Http\Controllers\Api\V1\JobSeeker\SeekerFeedbackController;
use App\Http\Controllers\Api\V1\JobSeeker\SeekerApplicationController;
use App\Http\Controllers\Api\V1\JobSeeker\SeekerJobDiscoveryController;
use App\Http\Controllers\Api\V1\JobSeeker\SeekerSavedJobController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\PublicIndustryTypeController;
use App\Http\Controllers\Api\V1\PublicBannerController;
use App\Http\Controllers\Api\V1\PublicTopCompaniesController;
use App\Http\Controllers\Api\V1\PublicLocationController;
use App\Http\Controllers\Api\V1\PublicJobController;
use App\Http\Controllers\Api\V1\PublicReferEarnController;
use App\Http\Controllers\Api\V1\PublicResumeDemoController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AdminNotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/admin-login', [AuthController::class, 'adminLogin'])->middleware('throttle:20,1');
    Route::post('auth/firebase-authenticate', [AuthController::class, 'firebaseAuthenticate'])->middleware('throttle:30,1');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:30,1');

    Route::get('industry-types', [PublicIndustryTypeController::class, 'index']);
    Route::get('seeker-home-popular-categories', [PublicIndustryTypeController::class, 'seekerHomePopular']);
    Route::get('jobs', [PublicJobController::class, 'index']);
    Route::get('jobs/{id}', [PublicJobController::class, 'show'])->whereNumber('id');
    Route::get('jobs/{id}/similar', [PublicJobController::class, 'similar'])->whereNumber('id');
    Route::get('jobs/{id}/share', [PublicJobController::class, 'share'])->whereNumber('id');
    Route::get('companies/top', [PublicTopCompaniesController::class, 'index']);
    Route::get('banners', [PublicBannerController::class, 'index']);
    Route::get('refer-earn', [PublicReferEarnController::class, 'show']);
    Route::post('refer-earn/validate', [PublicReferEarnController::class, 'validateCode']);
    Route::get('resume/demo-profiles', [PublicResumeDemoController::class, 'demoProfiles']);
    Route::get('resume/demo-preview-html-batch', [PublicResumeDemoController::class, 'demoPreviewHtmlBatch']);

    // Location dropdown data (Full India)
    Route::get('locations/states', [PublicLocationController::class, 'states']);
    Route::get('locations/districts', [PublicLocationController::class, 'districts']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);
        Route::post('auth/set-password', [AuthController::class, 'setPassword']);
        Route::get('me', MeController::class);

        // ── FCM device tokens (all authenticated users) ───────────────────
        Route::post('device-token', [DeviceTokenController::class, 'store']);
        Route::delete('device-token', [DeviceTokenController::class, 'destroy']);

        // ── In-app notification inbox ─────────────────────────────────────
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id');

        Route::prefix('company')->middleware('role:company')->group(function () {
            Route::get('profile', [CompanyProfileController::class, 'show']);
            Route::put('profile', [CompanyProfileController::class, 'update']);
            Route::get('subscription/offer', [CompanySubscriptionController::class, 'offer']);
            Route::post('subscription/purchase', [CompanySubscriptionController::class, 'purchase']);
            Route::get('subscription/history', [CompanySubscriptionController::class, 'history']);
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
            Route::post('profile/resume', [JobSeekerProfileController::class, 'uploadResumePdf'])
                ->middleware('throttle:20,1');
            Route::get('packages/catalog', [JobSeekerPackageController::class, 'catalog']);
            Route::get('packages/purchases', [JobSeekerPackageController::class, 'purchases']);
            Route::post('packages/select', [JobSeekerPackageController::class, 'select']);
            Route::get('applications', [SeekerApplicationController::class, 'index']);
            Route::delete('applications/{applicationId}', [SeekerApplicationController::class, 'destroy'])
                ->whereNumber('applicationId');
            Route::post('jobs/{jobId}/apply', [SeekerApplicationController::class, 'store'])->whereNumber('jobId');
            Route::get('saved-jobs', [SeekerSavedJobController::class, 'index']);
            Route::get('recommended-jobs', [SeekerJobDiscoveryController::class, 'recommended']);
            Route::get('related-jobs', [SeekerJobDiscoveryController::class, 'relatedFromApplications']);
            Route::post('jobs/{jobId}/save', [SeekerSavedJobController::class, 'store'])->whereNumber('jobId');
            Route::delete('jobs/{jobId}/save', [SeekerSavedJobController::class, 'destroy'])->whereNumber('jobId');
            Route::post('activity/time', [SeekerActivityController::class, 'addTime'])
                ->middleware('throttle:120,1');
            Route::post('resume/ai-assist', [ResumeAiController::class, 'assist'])
                ->middleware('throttle:15,1');
            Route::post('resume/pdf-purchase', [ResumePdfPurchaseController::class, 'purchase'])
                ->middleware('throttle:30,1');
            Route::get('resume/drafts', [ResumeDraftController::class, 'index']);
            Route::post('resume/primary', [ResumeDraftController::class, 'setPrimary'])
                ->middleware('throttle:60,1');
            Route::post('resume/save', [ResumeDraftController::class, 'store'])
                ->middleware('throttle:60,1');
            Route::post('resume/preview-html', [ResumeHtmlPreviewController::class, 'preview'])
                ->middleware('throttle:200,1');
            Route::post('career/ai-coach', [SeekerCareerAiController::class, 'coach'])
                ->middleware('throttle:20,1');
            Route::get('career/contents', [SeekerCareerContentController::class, 'index']);
            Route::post('career/contents/{careerContent}/helpful', [SeekerCareerContentController::class, 'setHelpful'])
                ->middleware('throttle:60,1');
            Route::get('feedback', [SeekerFeedbackController::class, 'index']);
            Route::post('feedback', [SeekerFeedbackController::class, 'store'])
                ->middleware('throttle:20,1');
        });

        Route::prefix('admin')->middleware('role:super_admin')->group(function () {
            Route::get('dashboard', AdminDashboardController::class);
            Route::get('companies', [AdminCompanyController::class, 'index']);
            Route::get('companies/{companyId}', [AdminCompanyController::class, 'show'])->whereNumber('companyId');
            Route::patch('companies/{companyId}/verification', [AdminCompanyController::class, 'updateVerification'])->whereNumber('companyId');
            Route::patch('companies/{companyId}/owner-status', [AdminCompanyController::class, 'updateOwnerStatus'])->whereNumber('companyId');
            Route::patch('companies/{companyId}/top-company', [AdminCompanyController::class, 'updateTopCompany'])->whereNumber('companyId');
            Route::get('applications', [AdminApplicationController::class, 'index']);
            Route::get('applications/{application}', [AdminApplicationController::class, 'show'])->whereNumber('application');
            Route::patch('applications/{application}', [AdminApplicationController::class, 'update'])->whereNumber('application');
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
            Route::get('career-contents', [AdminCareerContentController::class, 'index']);
            Route::post('career-contents', [AdminCareerContentController::class, 'store']);
            Route::patch('career-contents/{careerContent}', [AdminCareerContentController::class, 'update']);
            Route::delete('career-contents/{careerContent}', [AdminCareerContentController::class, 'destroy']);
            Route::get('seeker-feedback', [AdminSeekerFeedbackController::class, 'index']);
            Route::patch('seeker-feedback/{seekerFeedback}', [AdminSeekerFeedbackController::class, 'update']);
            Route::get('analytics/overview', [AdminAnalyticsController::class, 'overview']);
            Route::get('analytics/job-seekers', [AdminAnalyticsController::class, 'seekerUsage']);
            Route::get('settings/moderation', [AdminSettingController::class, 'showModeration']);
            Route::patch('settings/moderation', [AdminSettingController::class, 'updateModeration']);
            Route::get('settings/refer-earn', [AdminReferEarnController::class, 'showSettings']);
            Route::patch('settings/refer-earn', [AdminReferEarnController::class, 'updateSettings']);
            Route::get('refer-earn/promo-codes', [AdminReferEarnController::class, 'indexPromoCodes']);
            Route::post('refer-earn/promo-codes', [AdminReferEarnController::class, 'storePromoCode']);
            Route::patch('refer-earn/promo-codes/{id}', [AdminReferEarnController::class, 'updatePromoCode'])->whereNumber('id');
            Route::delete('refer-earn/promo-codes/{id}', [AdminReferEarnController::class, 'destroyPromoCode'])->whereNumber('id');
            Route::get('industry-types', [AdminIndustryTypeController::class, 'index']);
            Route::post('industry-types', [AdminIndustryTypeController::class, 'store']);
            Route::patch('industry-types/{id}', [AdminIndustryTypeController::class, 'update'])->whereNumber('id');
            Route::delete('industry-types/{id}', [AdminIndustryTypeController::class, 'destroy'])->whereNumber('id');

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

            // ── Admin: broadcast push notifications ───────────────────────
            Route::post('send-notification', [AdminNotificationController::class, 'send']);
        });
    });
});
