<?php

namespace Database\Factories;

use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebhookLog>
 */
class WebhookLogFactory extends Factory
{
    protected $model = WebhookLog::class;

    public function definition(): array
    {
        return [
            'event_type' => 'invoice.paid',
            'stripe_event_id' => 'evt_test_'.Str::random(14),
            'payload' => ['id' => 'evt_test', 'type' => 'invoice.paid'],
            'status' => WebhookLog::STATUS_RECEIVED,
            'error_message' => null,
            'processed_at' => null,
        ];
    }

    public function processed(): static
    {
        return $this->state(fn () => [
            'status' => WebhookLog::STATUS_PROCESSED,
            'processed_at' => now(),
        ]);
    }
}
