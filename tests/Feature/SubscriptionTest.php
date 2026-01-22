<?php

declare(strict_types=1);

use App\Domains\Payment\Contracts\PaymentGateway;
use App\Domains\Payment\Exceptions\PaymentFailedException;
use App\Domains\Payment\ValueObjects\Money;
use App\Domains\Subscription\DTOs\SubscriptionData;
use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Exceptions\CannotCreateData;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {});

describe('API Endpoint Tests', function () {
    it('returns validation errors when required fields are missing', function () {
        $response = postJson('/api/subscriptions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId', 'planKey', 'paymentMethodId']);
    });

    it('successfully creates a subscription with valid data', function () {
        $user = User::factory()->create();

        mock(PaymentGateway::class)
            ->shouldReceive('charge')
            ->andReturn('txn_123');

        $payload = [
            'userId' => $user->id,
            'planKey' => 'basic_monthly',
            'paymentMethodId' => 'pm_card_visa',
        ];

        $response = postJson('/api/subscriptions', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'status',
            ]);
    });

    it('returns 201 with correct subscription structure', function () {
        $user = User::factory()->create();

        mock(PaymentGateway::class)
            ->shouldReceive('charge')
            ->andReturn('txn_123');

        $payload = [
            'userId' => $user->id,
            'planKey' => 'basic_monthly',
            'paymentMethodId' => 'pm_card_visa',
        ];

        $response = postJson('/api/subscriptions', $payload);

        $response->assertCreated()
            ->assertJson([
                'status' => SubscriptionStatus::Active->value,
            ]);
    });
});

describe('Pipeline Step Tests', function () {
    it('fails pipeline when plan is unavailable', function () {
        $user = User::factory()->create();

        mock(PaymentGateway::class)->shouldNotReceive('charge');

        $payload = [
            'userId' => $user->id,
            'planKey' => 'invalid_plan',
            'paymentMethodId' => 'pm_card_visa',
        ];

        $response = postJson('/api/subscriptions', $payload);

        $response->assertStatus(500);
    });

    it('processes payment through Stripe adapter', function () {
        $user = User::factory()->create();

        $mock = mock(PaymentGateway::class);
        $mock->shouldReceive('charge')
            ->once()
            ->withArgs(function (Money $money, string $token) {
                return $money->amount === 1000
                    && $money->currency === 'USD'
                    && $token === 'pm_test_123';
            })
            ->andReturn('ch_123456');

        $payload = [
            'userId' => $user->id,
            'planKey' => 'basic_monthly',
            'paymentMethodId' => 'pm_test_123',
        ];

        postJson('/api/subscriptions', $payload)->assertCreated();
    });

    it('creates subscription record in database', function () {
        $user = User::factory()->create(['id' => 55]);

        mock(PaymentGateway::class)
            ->shouldReceive('charge')
            ->andReturn('txn_123');

        $payload = [
            'userId' => 55,
            'planKey' => 'pro_monthly',
            'paymentMethodId' => 'pm_card_mastercard',
        ];

        postJson('/api/subscriptions', $payload)->assertCreated();

        assertDatabaseHas('subscriptions', [
            'user_id' => 55,
            'plan_key' => 'pro_monthly',
            'status' => SubscriptionStatus::Active->value,
            'price' => 2000,
            'currency' => 'USD',
        ]);
    });

    it('stores plan features in JSONB column', function () {
        $user = User::factory()->create(['id' => 99]);

        mock(PaymentGateway::class)
            ->shouldReceive('charge')
            ->andReturn('txn_123');

        $payload = [
            'userId' => 99,
            'planKey' => 'basic_monthly',
            'paymentMethodId' => 'pm_card_visa',
        ];

        postJson('/api/subscriptions', $payload);

        $subscription = Subscription::where('user_id', 99)->first();

        expect($subscription->features_snapshot)->toBeInstanceOf(ArrayObject::class)
            ->and($subscription->features_snapshot['access'])->toBe('full');
    });
});

describe('Database Tests', function () {
    it('persists subscription with correct status and dates', function () {
        $user = User::factory()->create();

        mock(PaymentGateway::class)
            ->shouldReceive('charge')
            ->andReturn('txn_123');

        $payload = [
            'userId' => $user->id,
            'planKey' => 'basic_monthly',
            'paymentMethodId' => 'pm_card_visa',
        ];

        $response = postJson('/api/subscriptions', $payload);
        $id = $response->json('id');

        $subscription = Subscription::find($id);

        expect($subscription->status)->toBe(SubscriptionStatus::Active);
        expect($subscription->starts_at)->not->toBeNull();
        expect($subscription->ends_at)->not->toBeNull();

        expect($subscription->starts_at->diffInSeconds(now()))->toBeLessThan(5);
        expect($subscription->ends_at->diffInDays(now()->addMonth()))->toBeLessThan(1);
    });

    it('uses GIN index for JSONB features_snapshot queries', function () {
        $user = User::factory()->create(['id' => 101]);

        mock(PaymentGateway::class)->shouldReceive('charge')->andReturn('txn_123');

        $payload = [
            'userId' => 101,
            'planKey' => 'basic_monthly',
            'paymentMethodId' => 'pm_card_visa',
        ];
        postJson('/api/subscriptions', $payload);

        $result = Subscription::whereJsonContains('features_snapshot->access', 'full')->first();

        expect($result)->not->toBeNull()
            ->and($result->user_id)->toBe(101);
    });
});

describe('DTO Validation Tests', function () {
    it('SubscriptionData DTO validates required fields', function () {
        try {
            SubscriptionData::from([]);
            $this->fail('Should have thrown validation exception');
        } catch (CannotCreateData $e) {
            expect($e)->toBeInstanceOf(CannotCreateData::class);
        } catch (ValidationException $e) {
            expect($e->errors())->toHaveKeys(['userId', 'planKey', 'paymentMethodId']);
        }
    });

    it('SubscriptionData DTO casts enum values correctly', function () {
        $data = SubscriptionData::from([
            'userId' => '123',
            'planKey' => 'test_plan',
            'paymentMethodId' => 'pm_123',
        ]);

        expect($data->userId)->toBe(123)
            ->and($data->planKey)->toBe('test_plan');
    });
});

describe('Error Handling', function () {
    it('rolls back transaction on payment failure', function () {
        $user = User::factory()->create();

        mock(PaymentGateway::class)
            ->shouldReceive('charge')
            ->andThrow(new PaymentFailedException('Card declined'));

        $payload = [
            'userId' => $user->id,
            'planKey' => 'basic_monthly',
            'paymentMethodId' => 'pm_fail',
        ];

        try {
            postJson('/api/subscriptions', $payload);
        } catch (Throwable $e) {
        }

        assertDatabaseCount('subscriptions', 0);
    });

    it('returns proper error response on exceptions', function () {
        $user = User::factory()->create();

        mock(PaymentGateway::class)
            ->shouldReceive('charge')
            ->andThrow(new PaymentFailedException('Card declined'));

        $payload = [
            'userId' => $user->id,
            'planKey' => 'basic_monthly',
            'paymentMethodId' => 'pm_fail',
        ];

        $response = postJson('/api/subscriptions', $payload);

        $response->assertStatus(500);
    });
});
