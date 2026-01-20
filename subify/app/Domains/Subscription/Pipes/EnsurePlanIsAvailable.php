<?php

declare(strict_types=1);

namespace App\Domains\Subscription\Pipes;

use Closure;
use InvalidArgumentException;

/**
 * Validates that the requested plan exists and is available.
 */
class EnsurePlanIsAvailable
{
    public function handle(object $passable, Closure $next): mixed
    {
        // In a real app, fetch from database or config
        $plans = [
            'basic_monthly' => ['price' => 1000, 'currency' => 'USD'],
            'pro_monthly' => ['price' => 2000, 'currency' => 'USD'],
        ];

        if (! array_key_exists($passable->data->planKey, $plans)) {
            throw new InvalidArgumentException("Invalid plan key: {$passable->data->planKey}");
        }

        $plan = $plans[$passable->data->planKey];
        $passable->price = $plan['price'];
        $passable->currency = $plan['currency'];

        return $next($passable);
    }
}
