<?php

declare(strict_types=1);

namespace App\Domains\Payment\Contracts;

use App\Domains\Payment\ValueObjects\Money;

/**
 * Contract for external payment providers.
 */
interface PaymentGateway
{
    /**
     * Charge the given amount to the payment token.
     *
     * @return string Transaction ID
     */
    public function charge(Money $amount, string $paymentToken): string;
}
