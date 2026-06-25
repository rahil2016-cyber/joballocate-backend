<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * In-app notification inbox for the authenticated user.
 *
 * Routes:
 *  GET   /api/v1/notifications          – List (paginated, latest first)
 *  POST  /api/v1/notifications/read-all – Mark all as read
 *  POST  /api/v1/notifications/{id}/read – Mark one as read
 */
class NotificationController extends Controller
{
    /**
     * Return a paginated list of the authenticated user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->appNotifications()
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function readAll(Request $request): JsonResponse
    {
        $request->user()
            ->appNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = $request->user()
            ->appNotifications()
            ->findOrFail($id);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * Count of unread notifications (for the badge in the Flutter app).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()
            ->appNotifications()
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
