<?php

namespace App\Services;

use App\Factories\PaymentMethodFactory;
use App\Models\PaymentLog;
use App\Models\Plan;
use App\Models\User;
use Laravel\Cashier\Subscription;

class SubscriptionService
{
    /**
     * Create a new subscription for a user.
     *
     * @param User $user
     * @param Plan $plan
     * @param string $paymentMethod 'card' or 'bank_transfer'
     * @param string $billingPeriod 'monthly' or 'yearly'
     * @param array $options Additional options (e.g., payment_method_id for card)
     * @return Subscription
     */
    public function create(
        User $user,
        Plan $plan,
        string $paymentMethod,
        string $billingPeriod = 'monthly',
        array $options = []
    ): Subscription {
        $priceId = $plan->getPriceId($billingPeriod);

        if (!$priceId) {
            throw new \InvalidArgumentException("No Stripe price ID configured for {$billingPeriod} billing");
        }

        $handler = PaymentMethodFactory::make($paymentMethod);
        $subscription = $handler->createSubscription($user, $priceId, $options);

        // Update subscription with plan_id and payment_method_type
        $subscription->update([
            'plan_id' => $plan->id,
            'payment_method_type' => $paymentMethod,
        ]);

        // Log the subscription action
        PaymentLog::log(
            userId: $user->id,
            action: PaymentLog::ACTION_SUBSCRIBE,
            paymentMethod: $paymentMethod,
            status: PaymentLog::STATUS_SUCCESS,
            subscriptionId: $subscription->id,
            amount: $plan->getPrice($billingPeriod),
            metadata: [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'billing_period' => $billingPeriod,
            ]
        );

        return $subscription;
    }

    /**
     * Cancel a user's subscription.
     *
     * @param User $user
     * @return Subscription|null
     */
    public function cancel(User $user): ?Subscription
    {
        $subscription = $user->subscription('default');

        if (!$subscription) {
            return null;
        }

        $subscription->cancel();

        PaymentLog::log(
            userId: $user->id,
            action: PaymentLog::ACTION_CANCEL,
            paymentMethod: $subscription->payment_method_type ?? 'card',
            status: PaymentLog::STATUS_SUCCESS,
            subscriptionId: $subscription->id,
            metadata: [
                'cancelled_at' => now()->toIso8601String(),
                'ends_at' => $subscription->ends_at?->toIso8601String(),
            ]
        );

        return $subscription->fresh();
    }

    /**
     * Resume a cancelled subscription.
     *
     * @param User $user
     * @return Subscription|null
     */
    public function resume(User $user): ?Subscription
    {
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->onGracePeriod()) {
            return null;
        }

        $subscription->resume();

        PaymentLog::log(
            userId: $user->id,
            action: PaymentLog::ACTION_RESUME,
            paymentMethod: $subscription->payment_method_type ?? 'card',
            status: PaymentLog::STATUS_SUCCESS,
            subscriptionId: $subscription->id,
            metadata: [
                'resumed_at' => now()->toIso8601String(),
            ]
        );

        return $subscription->fresh();
    }

    /**
     * Change the user's subscription plan.
     *
     * @param User $user
     * @param Plan $newPlan
     * @param string $billingPeriod 'monthly' or 'yearly'
     * @return Subscription|null
     */
    public function changePlan(User $user, Plan $newPlan, string $billingPeriod = 'monthly'): ?Subscription
    {
        $subscription = $user->subscription('default');

        if (!$subscription) {
            return null;
        }

        $oldPlanId = $subscription->plan_id;
        $newPriceId = $newPlan->getPriceId($billingPeriod);

        if (!$newPriceId) {
            throw new \InvalidArgumentException("No Stripe price ID configured for {$billingPeriod} billing");
        }

        $subscription->swap($newPriceId);

        // Update local plan reference
        $subscription->update([
            'plan_id' => $newPlan->id,
        ]);

        PaymentLog::log(
            userId: $user->id,
            action: PaymentLog::ACTION_CHANGE_PLAN,
            paymentMethod: $subscription->payment_method_type ?? 'card',
            status: PaymentLog::STATUS_SUCCESS,
            subscriptionId: $subscription->id,
            amount: $newPlan->getPrice($billingPeriod),
            metadata: [
                'old_plan_id' => $oldPlanId,
                'new_plan_id' => $newPlan->id,
                'new_plan_name' => $newPlan->name,
                'billing_period' => $billingPeriod,
            ]
        );

        return $subscription->fresh();
    }

    /**
     * Get the user's current subscription.
     *
     * @param User $user
     * @return Subscription|null
     */
    public function getSubscription(User $user): ?Subscription
    {
        return $user->subscription('default');
    }

    /**
     * Refund a subscription payment.
     *
     * @param Subscription $subscription
     * @param int|null $amount Amount in cents (null for full refund)
     * @return bool
     */
    public function refund(Subscription $subscription, ?int $amount = null): bool
    {
        $user = $subscription->user;

        // Get the latest invoice for this subscription
        $latestInvoice = $user->invoices()->first();

        if (!$latestInvoice) {
            return false;
        }

        try {
            if ($amount) {
                // Partial refund
                $user->refund($latestInvoice->payment_intent, [
                    'amount' => $amount,
                ]);
            } else {
                // Full refund
                $user->refund($latestInvoice->payment_intent);
            }

            PaymentLog::log(
                userId: $user->id,
                action: PaymentLog::ACTION_REFUND,
                paymentMethod: $subscription->payment_method_type ?? 'card',
                status: PaymentLog::STATUS_SUCCESS,
                subscriptionId: $subscription->id,
                amount: $amount ?? $latestInvoice->rawTotal(),
                stripePaymentIntentId: $latestInvoice->payment_intent,
                metadata: [
                    'invoice_id' => $latestInvoice->id,
                    'refund_type' => $amount ? 'partial' : 'full',
                ]
            );

            return true;
        } catch (\Exception $e) {
            PaymentLog::log(
                userId: $user->id,
                action: PaymentLog::ACTION_REFUND,
                paymentMethod: $subscription->payment_method_type ?? 'card',
                status: PaymentLog::STATUS_FAILED,
                subscriptionId: $subscription->id,
                metadata: [
                    'error' => $e->getMessage(),
                ]
            );

            return false;
        }
    }
}
