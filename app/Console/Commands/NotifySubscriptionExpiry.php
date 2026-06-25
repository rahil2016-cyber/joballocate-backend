<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\NotificationSender;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Run daily (via scheduler) to notify employers whose subscription
 * is expiring in 3 days or 1 day.
 *
 * artisan notify:subscription-expiry
 */
class NotifySubscriptionExpiry extends Command
{
    protected $signature   = 'notify:subscription-expiry';
    protected $description = 'Send push notifications to employers whose premium subscription is about to expire.';

    public function __construct(private NotificationSender $sender)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $warningDays = [3, 1];

        $companies = Company::with(['user', 'subscriptionPayments'])->get();

        $notified = 0;
        foreach ($companies as $company) {
            $expiresAt = $company->subscriptionExpiresAt();
            if ($expiresAt === null) continue;

            $daysLeft = (int) Carbon::now()->diffInDays($expiresAt, false);

            if (in_array($daysLeft, $warningDays, true) && $company->user !== null) {
                $this->sender->subscriptionExpiringSoon($company->user, $daysLeft);
                $notified++;
            }
        }

        $this->info("Subscription expiry notifications sent: {$notified}");

        return self::SUCCESS;
    }
}
