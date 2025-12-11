<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookService $webhookService
    ) {}

    /**
     * Handle incoming Stripe webhooks.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Log the webhook event
        $log = $this->webhookService->logEvent($event);

        // Handle specific events
        match ($event->type) {
            'invoice.paid' => $this->webhookService->handleInvoicePaid($event, $log),
            'invoice.payment_failed' => $this->webhookService->handleInvoicePaymentFailed($event, $log),
            'customer.subscription.updated' => $this->webhookService->handleSubscriptionUpdated($event, $log),
            'customer.subscription.deleted' => $this->webhookService->handleSubscriptionDeleted($event, $log),
            'charge.refunded' => $this->webhookService->handleChargeRefunded($event, $log),
            default => $log->markAsProcessed(),
        };

        return response()->json(['status' => 'success']);
    }
}
