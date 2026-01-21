<?php

declare(strict_types=1);

namespace App\Domains\Subscription\Pipes;

use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Models\Subscription;
use ArrayObject;
use Closure;

/**
 * Creates and persists the subscription record in the database.
 */
class CreateSubscriptionRecord
{
    public function handle(object $passable, Closure $next): mixed
    {
        $subscription = new Subscription;
        $subscription->user_id = $passable->data->userId;
        $subscription->plan_key = $passable->data->planKey;
        $subscription->status = SubscriptionStatus::Active;
        $subscription->starts_at = now();
        $subscription->ends_at = now()->addMonth();
        $subscription->price = $passable->price;
        $subscription->currency = $passable->currency;

        $subscription->features_snapshot = new ArrayObject(['access' => 'full']);

        $subscription->save();

        $passable->subscription = $subscription;

        return $next($passable);
    }
}
