<?php

namespace App\Contracts;

use App\Models\User;
use Laravel\Cashier\Subscription;

interface PaymentMethodInterface
{
    /**
     * Create a subscription for the user.
     *
     * @param User $user
     * @param string $priceId Stripe price ID
     * @param array $options Additional options (e.g., payment_method_id for card)
     * @return Subscription
     */
    public function createSubscription(User $user, string $priceId, array $options = []): Subscription;

    /**
     * Get the payment method type identifier.
     *
     * @return string
     */
    public function getType(): string;
}
