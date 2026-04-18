<?php

namespace Tests\Feature\Api;

use App\Models\PaymentLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WebhookLog;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    private const WEBHOOK_SECRET = 'whsec_test_fake';

    #[Test]
    public function webhook_rejects_invalid_signature(): void
    {
        $payload = $this->loadFixture('invoice-paid.json');

        $response = $this->postJson('/api/v1/webhook/stripe', json_decode($payload, true), [
            'Stripe-Signature' => 't=1234567890,v1=invalid_signature',
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid signature']);
        $this->assertDatabaseCount('webhook_logs', 0);
        $this->assertDatabaseCount('payment_logs', 0);
    }

    #[Test]
    public function invoice_paid_event_creates_successful_payment_log(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_test_CUSTOMER_ID']);

        $payload = $this->loadFixture('invoice-paid.json');
        $signature = $this->computeSignature($payload);

        $response = $this->call(
            'POST',
            '/api/v1/webhook/stripe',
            [],
            [],
            [],
            ['HTTP_Stripe-Signature' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response->assertOk();
        $this->assertDatabaseHas('payment_logs', [
            'user_id' => $user->id,
            'action' => PaymentLog::ACTION_SUBSCRIBE,
            'status' => PaymentLog::STATUS_SUCCESS,
            'amount' => 1999,
        ]);
        $this->assertDatabaseHas('webhook_logs', [
            'event_type' => 'invoice.paid',
            'status' => WebhookLog::STATUS_PROCESSED,
        ]);
    }

    #[Test]
    public function subscription_deleted_event_marks_subscription_canceled(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_test_CUSTOMER_ID']);
        $plan = Plan::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_id' => 'sub_test_SUBSCRIPTION_ID',
            'stripe_status' => 'active',
        ]);

        $payload = $this->loadFixture('subscription-deleted.json');
        $signature = $this->computeSignature($payload);

        $response = $this->call(
            'POST',
            '/api/v1/webhook/stripe',
            [],
            [],
            [],
            ['HTTP_Stripe-Signature' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response->assertOk();
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'stripe_status' => 'canceled',
        ]);
        $this->assertNotNull($subscription->fresh()->ends_at);
        $this->assertDatabaseHas('payment_logs', [
            'user_id' => $user->id,
            'action' => PaymentLog::ACTION_CANCEL,
        ]);
    }

    private function loadFixture(string $filename): string
    {
        return file_get_contents(__DIR__.'/../../fixtures/stripe/'.$filename);
    }

    private function computeSignature(string $payload): string
    {
        $timestamp = time();
        $signedPayload = $timestamp.'.'.$payload;
        $signature = hash_hmac('sha256', $signedPayload, self::WEBHOOK_SECRET);

        return "t={$timestamp},v1={$signature}";
    }
}
