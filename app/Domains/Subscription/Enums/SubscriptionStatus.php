<?php

declare(strict_types=1);

namespace App\Domains\Subscription\Enums;

/**
 * Represents the lifecycle state of a user subscription.
 */
enum SubscriptionStatus: string
{
    case Active = 'active';
    case Canceled = 'canceled';
    case PastDue = 'past_due';
    case Pending = 'pending';

    /**
     * Determine if the status grants access to services.
     */
    public function hasAccess(): bool
    {
        return match ($this) {
            self::Active, self::PastDue => true,
            default => false,
        };
    }
}
