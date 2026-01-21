<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domains\Subscription\Actions\SubscribeUserAction;
use App\Domains\Subscription\DTOs\SubscriptionData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function store(SubscriptionData $data, SubscribeUserAction $action): JsonResponse
    {
        $subscription = $action->execute($data);

        return response()->json([
            'id' => $subscription->id,
            'status' => $subscription->status,
        ], 201);
    }
}
