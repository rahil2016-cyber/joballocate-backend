<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

/**
 * High-level notification helper.
 *
 * Handles:
 *  - Persisting an in-app Notification record.
 *  - Dispatching a push via FcmService across all user device tokens.
 */
class NotificationSender
{
    public function __construct(private FcmService $fcm) {}

    // -----------------------------------------------------------------
    // Generic send
    // -----------------------------------------------------------------

    /**
     * Create an in-app notification record and (optionally) send a push.
     */
    public function send(User $user, string $title, string $body, string $type = '', array $fcmData = []): void
    {
        // 1. Persist in-app record
        Notification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type ?: null,
        ]);

        // 2. Push to all user devices
        $tokens = $user->deviceTokens()->pluck('fcm_token')->filter()->values()->all();
        if (! empty($tokens)) {
            $this->fcm->sendToTokens($tokens, $title, $body, $fcmData);
        }
    }

    // -----------------------------------------------------------------
    // Job Seeker notifications
    // -----------------------------------------------------------------

    public function newJobMatch(User $seeker, string $jobTitle, int $jobId): void
    {
        $this->send(
            $seeker,
            'New Job Match 🎯',
            "A new job matching your skills is available: {$jobTitle}",
            'new_job',
            ['job_id' => (string) $jobId],
        );
    }

    public function applicationShortlisted(User $seeker, string $jobTitle, int $applicationId): void
    {
        $this->send(
            $seeker,
            'Application Shortlisted ⭐',
            "Your application for '{$jobTitle}' has been shortlisted!",
            'shortlisted',
            ['application_id' => (string) $applicationId],
        );
    }

    public function interviewScheduled(User $seeker, string $jobTitle, int $applicationId): void
    {
        $this->send(
            $seeker,
            'Interview Scheduled 📅',
            "An interview has been scheduled for your application for '{$jobTitle}'.",
            'interview',
            ['application_id' => (string) $applicationId],
        );
    }

    public function applicationAccepted(User $seeker, string $jobTitle, int $applicationId): void
    {
        $this->send(
            $seeker,
            'Application Accepted 🎉',
            "Congratulations! You have been accepted for '{$jobTitle}'.",
            'accepted',
            ['application_id' => (string) $applicationId],
        );
    }

    public function applicationRejected(User $seeker, string $jobTitle, int $applicationId): void
    {
        $this->send(
            $seeker,
            'Application Update',
            "Your application for '{$jobTitle}' was not selected at this time.",
            'rejected',
            ['application_id' => (string) $applicationId],
        );
    }

    // -----------------------------------------------------------------
    // Employer notifications
    // -----------------------------------------------------------------

    public function newApplicationReceived(User $employer, string $jobTitle, int $applicationId): void
    {
        $this->send(
            $employer,
            'New Application Received 📋',
            "A candidate has applied for your job: {$jobTitle}",
            'new_application',
            ['application_id' => (string) $applicationId],
        );
    }

    public function candidateAcceptedInvitation(User $employer, string $candidateName, int $applicationId): void
    {
        $this->send(
            $employer,
            'Candidate Accepted Invitation ✅',
            "{$candidateName} has accepted your interview invitation.",
            'candidate_accepted',
            ['application_id' => (string) $applicationId],
        );
    }

    public function subscriptionExpiringSoon(User $employer, int $daysLeft): void
    {
        $this->send(
            $employer,
            'Premium Subscription Expiring 🔔',
            "Your premium subscription expires in {$daysLeft} day(s). Renew now to keep posting jobs.",
            'subscription_expiring',
        );
    }

    public function paymentSuccessful(User $employer, string $planName, string $amount): void
    {
        $this->send(
            $employer,
            'Payment Successful 💳',
            "Your payment of {$amount} for the {$planName} plan was successful. Enjoy premium access!",
            'payment_success',
        );
    }

    // -----------------------------------------------------------------
    // Admin broadcast
    // -----------------------------------------------------------------

    /**
     * Broadcast to a list of users (used by AdminNotificationController).
     */
    public function broadcast(iterable $users, string $title, string $body, array $fcmData = []): int
    {
        $count = 0;
        foreach ($users as $user) {
            $this->send($user, $title, $body, 'broadcast', $fcmData);
            $count++;
        }
        return $count;
    }
}
