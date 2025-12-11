<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $stripePaymentMethod = $this->asStripePaymentMethod();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'brand' => $stripePaymentMethod->card->brand ?? null,
            'last_four' => $stripePaymentMethod->card->last4 ?? null,
            'exp_month' => $stripePaymentMethod->card->exp_month ?? null,
            'exp_year' => $stripePaymentMethod->card->exp_year ?? null,
            'is_default' => $this->is_default ?? false,
        ];
    }
}
