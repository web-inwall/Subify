<?php

declare(strict_types=1);

namespace App\Domains\Subscription\Actions;

use App\Domains\Payment\Exceptions\PaymentFailedException;
use App\Domains\Subscription\DTOs\SubscriptionData;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Pipes\CreateSubscriptionRecord;
use App\Domains\Subscription\Pipes\EnsurePlanIsAvailable;
use App\Domains\Subscription\Pipes\ProcessPayment;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * Orchestrates the subscription creation process.
 */
final readonly class SubscribeUserAction
{
    public function __construct(
        private Pipeline $pipeline
    ) {}

    /**
     * @throws PaymentFailedException
     */
    public function execute(SubscriptionData $data): Subscription
    {
        return DB::transaction(function () use ($data) {
            $passable = new stdClass;
            $passable->data = $data;
            $passable->price = null;
            $passable->currency = null;
            $passable->transactionId = null;
            $passable->subscription = null;

            return $this->pipeline
                ->send($passable)
                ->through([
                    EnsurePlanIsAvailable::class,
                    ProcessPayment::class,
                    CreateSubscriptionRecord::class,
                ])
                ->then(fn (object $context) => $context->subscription);
        });
    }
}
