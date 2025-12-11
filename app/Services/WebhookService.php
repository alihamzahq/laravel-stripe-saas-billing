<?php

namespace App\Services;

use App\Models\PaymentLog;
use App\Models\User;
use App\Models\WebhookLog;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
use Stripe\Event;

class WebhookService
{
    /**
     * Log a webhook event.
     */
    public function logEvent(Event $event): WebhookLog
    {
        return WebhookLog::create([
            'event_type' => $event->type,
            'stripe_event_id' => $event->id,
            'payload' => $event->toArray(),
            'status' => WebhookLog::STATUS_RECEIVED,
        ]);
    }

    /**
     * Handle invoice.paid event.
     */
    public function handleInvoicePaid(Event $event, WebhookLog $log): void
    {
        $invoice = $event->data->object;
        $user = $this->getUserByStripeId($invoice->customer);

        if (!$user) {
            $log->markAsFailed('User not found for customer: ' . $invoice->customer);
            return;
        }

        $subscription = $user->subscriptions()->where('stripe_id', $invoice->subscription)->first();

        PaymentLog::log(
            userId: $user->id,
            action: PaymentLog::ACTION_SUBSCRIBE,
            paymentMethod: $this->getPaymentMethodType($invoice),
            status: PaymentLog::STATUS_SUCCESS,
            subscriptionId: $subscription?->id,
            amount: $invoice->amount_paid,
            stripePaymentIntentId: $invoice->payment_intent,
            metadata: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->number,
            ]
        );

        $log->markAsProcessed();
    }

    /**
     * Handle invoice.payment_failed event.
     */
    public function handleInvoicePaymentFailed(Event $event, WebhookLog $log): void
    {
        $invoice = $event->data->object;
        $user = $this->getUserByStripeId($invoice->customer);

        if (!$user) {
            $log->markAsFailed('User not found for customer: ' . $invoice->customer);
            return;
        }

        $subscription = $user->subscriptions()->where('stripe_id', $invoice->subscription)->first();

        PaymentLog::log(
            userId: $user->id,
            action: PaymentLog::ACTION_PAYMENT_FAILED,
            paymentMethod: $this->getPaymentMethodType($invoice),
            status: PaymentLog::STATUS_FAILED,
            subscriptionId: $subscription?->id,
            amount: $invoice->amount_due,
            stripePaymentIntentId: $invoice->payment_intent,
            metadata: [
                'invoice_id' => $invoice->id,
                'failure_message' => $event->data->object->last_finalization_error?->message ?? 'Payment failed',
            ]
        );

        $log->markAsProcessed();
    }

    /**
     * Handle customer.subscription.updated event.
     */
    public function handleSubscriptionUpdated(Event $event, WebhookLog $log): void
    {
        $stripeSubscription = $event->data->object;
        $user = $this->getUserByStripeId($stripeSubscription->customer);

        if (!$user) {
            $log->markAsFailed('User not found for customer: ' . $stripeSubscription->customer);
            return;
        }

        // Cashier handles the subscription update automatically
        // We just log it for tracking purposes
        $subscription = $user->subscriptions()->where('stripe_id', $stripeSubscription->id)->first();

        if ($subscription) {
            // Update subscription status from Stripe
            $subscription->update([
                'stripe_status' => $stripeSubscription->status,
            ]);
        }

        $log->markAsProcessed();
    }

    /**
     * Handle customer.subscription.deleted event.
     */
    public function handleSubscriptionDeleted(Event $event, WebhookLog $log): void
    {
        $stripeSubscription = $event->data->object;
        $user = $this->getUserByStripeId($stripeSubscription->customer);

        if (!$user) {
            $log->markAsFailed('User not found for customer: ' . $stripeSubscription->customer);
            return;
        }

        $subscription = $user->subscriptions()->where('stripe_id', $stripeSubscription->id)->first();

        if ($subscription) {
            PaymentLog::log(
                userId: $user->id,
                action: PaymentLog::ACTION_CANCEL,
                paymentMethod: $subscription->payment_method_type ?? PaymentLog::PAYMENT_METHOD_CARD,
                status: PaymentLog::STATUS_SUCCESS,
                subscriptionId: $subscription->id,
                metadata: [
                    'reason' => 'Subscription deleted via Stripe',
                ]
            );

            // Mark subscription as cancelled
            $subscription->update([
                'stripe_status' => 'canceled',
                'ends_at' => now(),
            ]);
        }

        $log->markAsProcessed();
    }

    /**
     * Handle charge.refunded event.
     */
    public function handleChargeRefunded(Event $event, WebhookLog $log): void
    {
        $charge = $event->data->object;
        $user = $this->getUserByStripeId($charge->customer);

        if (!$user) {
            $log->markAsFailed('User not found for customer: ' . $charge->customer);
            return;
        }

        PaymentLog::log(
            userId: $user->id,
            action: PaymentLog::ACTION_REFUND,
            paymentMethod: $charge->payment_method_details?->type ?? PaymentLog::PAYMENT_METHOD_CARD,
            status: PaymentLog::STATUS_SUCCESS,
            amount: $charge->amount_refunded,
            stripePaymentIntentId: $charge->payment_intent,
            metadata: [
                'charge_id' => $charge->id,
                'refund_reason' => $charge->refunds->data[0]->reason ?? null,
            ]
        );

        $log->markAsProcessed();
    }

    /**
     * Get user by Stripe customer ID.
     */
    private function getUserByStripeId(string $stripeId): ?User
    {
        return User::where('stripe_id', $stripeId)->first();
    }

    /**
     * Get payment method type from invoice.
     */
    private function getPaymentMethodType(object $invoice): string
    {
        if ($invoice->collection_method === 'send_invoice') {
            return PaymentLog::PAYMENT_METHOD_BANK_TRANSFER;
        }

        return PaymentLog::PAYMENT_METHOD_CARD;
    }
}
