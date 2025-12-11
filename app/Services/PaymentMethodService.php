<?php

namespace App\Services;

use App\Models\User;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\PaymentMethod;
use Illuminate\Support\Collection;

class PaymentMethodService
{
    /**
     * Get all payment methods for a user.
     */
    public function getPaymentMethods(User $user): Collection
    {
        if (!$user->hasStripeId()) {
            return collect([]);
        }

        $defaultPaymentMethodId = $this->getDefaultPaymentMethodId($user);

        return $user->paymentMethods()->map(function ($paymentMethod) use ($defaultPaymentMethodId) {
            $paymentMethod->is_default = $paymentMethod->id === $defaultPaymentMethodId;
            return $paymentMethod;
        });
    }

    /**
     * Add a new payment method using card token.
     */
    public function addPaymentMethod(User $user, string $token, string $cardHolderName): PaymentMethod
    {
        // Create Stripe customer if not exists
        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        // Create payment method from token
        $stripePaymentMethod = Cashier::stripe()->paymentMethods->create([
            'type' => 'card',
            'card' => [
                'token' => $token,
            ],
            'billing_details' => [
                'name' => $cardHolderName,
            ],
        ]);

        // Attach payment method to customer
        $paymentMethod = $user->addPaymentMethod($stripePaymentMethod->id);

        // Set as default if it's the first payment method
        if ($user->paymentMethods()->count() === 1) {
            $user->updateDefaultPaymentMethod($stripePaymentMethod->id);
        }

        return $paymentMethod;
    }

    /**
     * Remove a payment method.
     */
    public function removePaymentMethod(User $user, string $paymentMethodId): bool
    {
        if (!$user->hasStripeId()) {
            return false;
        }

        $paymentMethod = $user->findPaymentMethod($paymentMethodId);

        if (!$paymentMethod) {
            return false;
        }

        $paymentMethod->delete();

        return true;
    }

    /**
     * Set a payment method as default.
     */
    public function setDefaultPaymentMethod(User $user, string $paymentMethodId): bool
    {
        if (!$user->hasStripeId()) {
            return false;
        }

        $paymentMethod = $user->findPaymentMethod($paymentMethodId);

        if (!$paymentMethod) {
            return false;
        }

        $user->updateDefaultPaymentMethod($paymentMethodId);

        return true;
    }

    /**
     * Create a SetupIntent for adding a new payment method.
     */
    public function createSetupIntent(User $user): string
    {
        // Create Stripe customer if not exists
        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        $intent = $user->createSetupIntent();

        return $intent->client_secret;
    }

    /**
     * Get the default payment method ID for a user.
     */
    private function getDefaultPaymentMethodId(User $user): ?string
    {
        try {
            $customer = Cashier::stripe()->customers->retrieve($user->stripe_id);
            return $customer->invoice_settings->default_payment_method ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
