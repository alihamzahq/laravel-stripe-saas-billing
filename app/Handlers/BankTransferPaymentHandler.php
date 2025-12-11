<?php

namespace App\Handlers;

use App\Contracts\PaymentMethodInterface;
use App\Models\User;
use Laravel\Cashier\Subscription;

class BankTransferPaymentHandler implements PaymentMethodInterface
{
    /**
     * Create a subscription using bank transfer payment method.
     * Creates an invoice and sends it to the customer for payment.
     *
     * @param User $user
     * @param string $priceId Stripe price ID
     * @param array $options Optional settings like 'days_until_due'
     * @return Subscription
     */
    public function createSubscription(User $user, string $priceId, array $options = []): Subscription
    {
        $daysUntilDue = $options['days_until_due'] ?? 7;

        return $user->newSubscription('default', $priceId)
            ->createAndSendInvoice([], [
                'days_until_due' => $daysUntilDue,
            ]);
    }

    /**
     * Get the payment method type identifier.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'bank_transfer';
    }
}
