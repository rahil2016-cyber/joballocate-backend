<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages FCM device token registration for the authenticated user.
 *
 * Routes:
 *  POST   /api/v1/device-token         – Register / refresh a token
 *  DELETE /api/v1/device-token         – Remove a token (on logout)
 */
class DeviceTokenController extends Controller
{
    /**
     * Register (or refresh) an FCM token for the current user.
     *
     * Body: { "fcm_token": "...", "device_type": "android|ios|web" }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token'   => 'required|string|max:512',
            'device_type' => 'nullable|string|in:android,ios,web',
        ]);

        $user = $request->user();

        // Upsert: update existing row for this token, or create a new one.
        UserDeviceToken::updateOrCreate(
            [
                'user_id'   => $user->id,
                'fcm_token' => $validated['fcm_token'],
            ],
            [
                'device_type' => $validated['device_type'] ?? 'android',
            ]
        );

        return response()->json(['message' => 'Device token registered.']);
    }

    /**
     * Remove a specific FCM token (called on user logout or token rotation).
     *
     * Body: { "fcm_token": "..." }
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string|max:512',
        ]);

        $request->user()
            ->deviceTokens()
            ->where('fcm_token', $validated['fcm_token'])
            ->delete();

        return response()->json(['message' => 'Device token removed.']);
    }
}
