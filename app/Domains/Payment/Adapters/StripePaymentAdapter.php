<?php

declare(strict_types=1);

namespace App\Domains\Payment\Adapters;

use App\Domains\Payment\Contracts\PaymentGateway;
use App\Domains\Payment\ValueObjects\Money;
use Illuminate\Support\Str;

readonly class StripePaymentAdapter implements PaymentGateway
{
    public function charge(Money $amount, string $paymentToken): string
    {
        // Simulate API latency
        sleep(1);

        return (string) Str::uuid();
    }
}
