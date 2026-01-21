<?php

declare(strict_types=1);

namespace App\Domains\Subscription\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Handles recurring payment processing for active subscriptions.
 */
class ProcessRecurringPayments implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function uniqueId(): string
    {
        return 'recurring_payments_batch';
    }

    public function middleware(): array
    {
        return [new RateLimited('recurring_payments')];
    }

    public function handle(): void
    {
        Log::info('Processing renewals...');

    }
}
