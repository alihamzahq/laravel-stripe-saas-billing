<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'stripe_id' => $this->stripe_id,
            'pm_type' => $this->pm_type,
            'pm_last_four' => $this->pm_last_four,
            'trial_ends_at' => $this->trial_ends_at,
            'has_active_subscription' => $this->hasActiveSubscription(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
