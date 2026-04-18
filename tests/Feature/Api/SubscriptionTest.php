<?php

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    #[Test]
    public function user_can_subscribe_to_active_plan(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();
        $fakeSubscription = Subscription::factory()->make([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $this->mock(SubscriptionService::class, function (MockInterface $mock) use ($fakeSubscription) {
            $mock->shouldReceive('create')
                ->once()
                ->andReturn($fakeSubscription);
        });

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/subscriptions', [
            'plan_id' => $plan->id,
            'payment_method' => 'card',
            'payment_method_id' => 'pm_test_fake',
            'billing_period' => 'monthly',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Subscription created successfully');
    }

    #[Test]
    public function user_cannot_subscribe_when_already_has_active_subscription(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/subscriptions', [
            'plan_id' => $plan->id,
            'payment_method' => 'card',
            'payment_method_id' => 'pm_test_fake',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'You already have an active subscription. Please cancel or change your plan instead.');
    }

    #[Test]
    public function user_can_cancel_active_subscription(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $this->mock(SubscriptionService::class, function (MockInterface $mock) use ($subscription) {
            $mock->shouldReceive('cancel')
                ->once()
                ->andReturn($subscription);
        });

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/subscriptions/cancel');

        $response->assertOk()
            ->assertJsonPath('message', 'Subscription cancelled successfully. You will have access until the end of your billing period.');
    }
}
