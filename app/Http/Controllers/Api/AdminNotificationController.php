<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationSender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin-only broadcast notification panel.
 *
 * Route:
 *  POST /api/v1/admin/send-notification
 *
 * Payload:
 *  {
 *    "title":    "string",
 *    "body":     "string",
 *    "audience": "all | job_seekers | employers | premium_employers"
 *  }
 */
class AdminNotificationController extends Controller
{
    public function __construct(private NotificationSender $sender) {}

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'body'     => 'required|string|max:1000',
            'audience' => 'required|string|in:all,job_seekers,employers,premium_employers',
        ]);

        $users = $this->resolveAudience($validated['audience']);

        $count = $this->sender->broadcast(
            $users,
            $validated['title'],
            $validated['body'],
        );

        return response()->json([
            'message'        => 'Notifications dispatched.',
            'recipients_count' => $count,
        ]);
    }

    private function resolveAudience(string $audience)
    {
        return match ($audience) {
            'job_seekers' => User::where('role', 'job_seeker')->get(),
            'employers'   => User::where('role', 'employer')->get(),
            'premium_employers' => $this->premiumEmployers(),
            default       => User::all(), // 'all'
        };
    }

    /**
     * Return users whose company has an active premium subscription.
     * Uses the `isPremium()` helper added to the Company model.
     */
    private function premiumEmployers()
    {
        return User::where('role', 'employer')
            ->whereHas('company', function ($q) {
                $q->whereHas('subscriptionPayments', function ($sq) {
                    $sq->where('purchased_at', '>=', now()->subDays(31));
                });
            })
            ->get();
    }
}
