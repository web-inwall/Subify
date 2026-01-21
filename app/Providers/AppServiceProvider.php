<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Domains\Payment\Contracts\PaymentGateway::class, function ($app) {
            return match (config('services.payment.driver')) {
                'stripe' => new \App\Domains\Payment\Adapters\StripePaymentAdapter,
                default => new \App\Domains\Payment\Adapters\FakePaymentAdapter,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('recurring_payments', function ($job) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10);
        });
    }
}
