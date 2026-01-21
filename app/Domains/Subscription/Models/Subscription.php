<?php

declare(strict_types=1);

namespace App\Domains\Subscription\Models;

use App\Domains\Subscription\Enums\SubscriptionStatus;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a user subscription with snapshot of plan features.
 *
 * @property SubscriptionStatus $status
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \ArrayObject $features_snapshot
 */
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_key',
        'status',
        'starts_at',
        'ends_at',
        'features_snapshot',
        'price',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'features_snapshot' => AsArrayObject::class,
            'price' => 'integer',
        ];
    }
}
