<?php

declare(strict_types=1);

namespace App\Domains\Subscription\Pipes;

use App\Domains\Subscription\DTOs\SubscriptionData;
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

        /** @var SubscriptionData $data */
        $data = $passable->data;

        if (! array_key_exists($data->planKey, $plans)) {
            throw new InvalidArgumentException("Invalid plan key: {$data->planKey}");
        }

        $plan = $plans[$data->planKey];
        $passable->price = $plan['price'];
        $passable->currency = $plan['currency'];

        return $next($passable);
    }
}
