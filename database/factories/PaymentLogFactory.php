<?php

namespace Database\Factories;

use App\Models\PaymentLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentLog>
 */
class PaymentLogFactory extends Factory
{
    protected $model = PaymentLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subscription_id' => null,
            'action' => PaymentLog::ACTION_SUBSCRIBE,
            'payment_method' => PaymentLog::PAYMENT_METHOD_CARD,
            'amount' => 999,
            'status' => PaymentLog::STATUS_SUCCESS,
            'stripe_payment_intent_id' => null,
            'metadata' => [],
        ];
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => PaymentLog::STATUS_FAILED,
        ]);
    }
}
