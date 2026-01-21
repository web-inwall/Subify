<?php

declare(strict_types=1);

namespace App\Domains\Payment\Adapters;

use App\Domains\Payment\Contracts\PaymentGateway;
use App\Domains\Payment\ValueObjects\Money;

readonly class FakePaymentAdapter implements PaymentGateway
{
    public function charge(Money $amount, string $paymentToken): string
    {
        return 'fake_txn_'.uniqid();
    }
}
