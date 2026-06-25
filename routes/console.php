<?php

use App\Console\Commands\NotifySubscriptionExpiry;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run subscription expiry notifications every day at 9:00 AM
Schedule::command(NotifySubscriptionExpiry::class)->dailyAt('09:00');
