<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'stripe_id' => $this->stripe_id,
            'stripe_status' => $this->stripe_status,
            'stripe_price' => $this->stripe_price,
            'plan_id' => $this->plan_id,
            'payment_method_type' => $this->payment_method_type,
            'quantity' => $this->quantity,
            'trial_ends_at' => $this->trial_ends_at,
            'ends_at' => $this->ends_at,
            'is_active' => $this->resource->active(),
            'is_cancelled' => $this->resource->canceled(),
            'is_on_trial' => $this->resource->onTrial(),
            'is_on_grace_period' => $this->resource->onGracePeriod(),
            'plan' => $this->whenLoaded('plan', function () {
                return new PlanResource($this->plan);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
