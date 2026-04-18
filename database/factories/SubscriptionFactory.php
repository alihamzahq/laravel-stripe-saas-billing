<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'type' => 'default',
            'stripe_id' => 'sub_test_'.Str::random(14),
            'stripe_status' => 'active',
            'stripe_price' => 'price_test_'.Str::random(14),
            'payment_method_type' => 'card',
            'quantity' => 1,
            'trial_ends_at' => null,
            'ends_at' => null,
        ];
    }

    public function canceled(): static
    {
        return $this->state(fn () => [
            'stripe_status' => 'canceled',
            'ends_at' => now()->subDay(),
        ]);
    }

    public function onGracePeriod(): static
    {
        return $this->state(fn () => [
            'stripe_status' => 'active',
            'ends_at' => now()->addDays(15),
        ]);
    }
}
