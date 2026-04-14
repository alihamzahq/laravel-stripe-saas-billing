<?php

namespace App\Handlers;

use App\Contracts\PaymentMethodInterface;
use App\Models\User;
use Laravel\Cashier\Subscription;

class CardPaymentHandler implements PaymentMethodInterface
{
    /**
     * Create a subscription using card payment method.
     *
     * @param  string  $priceId  Stripe price ID
     * @param  array  $options  Must contain 'payment_method_id'
     */
    public function createSubscription(User $user, string $priceId, array $options = []): Subscription
    {
        $paymentMethodId = $options['payment_method_id'] ?? null;

        if (! $paymentMethodId) {
            throw new \InvalidArgumentException('Payment method ID is required for card payments');
        }

        return $user->newSubscription('default', $priceId)
            ->create($paymentMethodId);
    }

    /**
     * Get the payment method type identifier.
     */
    public function getType(): string
    {
        return 'card';
    }
}
