<?php

declare(strict_types=1);

namespace App\Domains\Subscription\Pipes;

use App\Domains\Payment\Contracts\PaymentGateway;
use App\Domains\Payment\Exceptions\PaymentFailedException;
use App\Domains\Payment\ValueObjects\Money;
use App\Domains\Subscription\DTOs\SubscriptionData;
use Closure;
use Exception;

/**
 * Charges the user via the configured payment gateway.
 */
class ProcessPayment
{
    public function __construct(
        protected PaymentGateway $gateway
    ) {}

    /**
     * @throws PaymentFailedException
     */
    public function handle(object $passable, Closure $next): mixed
    {
        try {
            /** @var int $price */
            $price = $passable->price;
            /** @var string $currency */
            $currency = $passable->currency;
            /** @var SubscriptionData $data */
            $data = $passable->data;

            $money = new Money($price, $currency);

            $transactionId = $this->gateway->charge(
                $money,
                $data->paymentMethodId
            );

            $passable->transactionId = $transactionId;

        } catch (Exception $e) {
            throw new PaymentFailedException('Payment failed: '.$e->getMessage(), 0, $e);
        }

        return $next($passable);
    }
}
