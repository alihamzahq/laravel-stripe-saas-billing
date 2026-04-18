<?php

namespace Tests\Unit\Services;

use App\Models\PaymentLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    #[Test]
    public function cancel_returns_null_when_user_has_no_subscription(): void
    {
        $user = User::factory()->create();
        $service = new SubscriptionService;

        $result = $service->cancel($user);

        $this->assertNull($result);
        $this->assertDatabaseCount('payment_logs', 0);
    }

    #[Test]
    public function resume_returns_null_when_subscription_is_not_on_grace_period(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_status' => 'active',
            'ends_at' => null,
        ]);

        $service = new SubscriptionService;

        $result = $service->resume($user->fresh());

        $this->assertNull($result);
        $this->assertDatabaseMissing('payment_logs', [
            'user_id' => $user->id,
            'action' => PaymentLog::ACTION_RESUME,
        ]);
    }
}
