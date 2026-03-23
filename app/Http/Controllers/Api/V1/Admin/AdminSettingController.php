<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Services\PlatformSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    use ApiResponses;

    public function __construct(
        private readonly PlatformSettingService $settings
    ) {}

    public function showModeration(): JsonResponse
    {
        return $this->ok($this->settings->moderationSettings());
    }

    public function updateModeration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auto_verify_new_companies' => ['required', 'boolean'],
            'auto_publish_new_jobs' => ['required', 'boolean'],
        ]);

        $updated = $this->settings->updateModerationSettings(
            $validated,
            $request->user()?->id
        );

        return $this->ok($updated, 'Moderation settings updated.');
    }
}

