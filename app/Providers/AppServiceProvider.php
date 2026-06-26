<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Razorpay\Api\Api;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Api::class, function () {
            return new Api(
                config('services.razorpay.key_id') ?? 'rzp_test_dummy',
                config('services.razorpay.key_secret') ?? 'dummy_secret'
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
