<?php

declare(strict_types=1);

namespace App\Domains\Subscription\Pipes;

use App\Domains\Subscription\DTOs\SubscriptionData;
use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Models\Subscription;
use Closure;

/**
 * Creates and persists the subscription record in the database.
 */
class CreateSubscriptionRecord
{
    public function handle(object $passable, Closure $next): mixed
    {
        /** @var SubscriptionData $data */
        $data = $passable->data;
        /** @var int $price */
        $price = $passable->price;
        /** @var string $currency */
        $currency = $passable->currency;

        $subscription = new Subscription;
        $subscription->user_id = $data->userId;
        $subscription->plan_key = $data->planKey;
        $subscription->status = SubscriptionStatus::Active;
        $subscription->starts_at = now();
        $subscription->ends_at = now()->addMonth();
        $subscription->price = $price;
        $subscription->currency = $currency;

        // Snapshot features (mocked for now)
        $subscription->features_snapshot = new \ArrayObject(['access' => 'full']);

        $subscription->save();

        $passable->subscription = $subscription;

        return $next($passable);
    }
}
