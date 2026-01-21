<?php

declare(strict_types=1);

namespace App\Domains\Subscription\DTOs;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class SubscriptionData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly int $userId,

        #[Required, StringType]
        public readonly string $planKey,

        #[Required, StringType]
        public readonly string $paymentMethodId,
    ) {}
}
