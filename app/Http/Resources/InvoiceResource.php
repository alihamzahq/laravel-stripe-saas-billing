<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'number' => $this->number,
            'status' => $this->status,
            'total' => $this->total(),
            'subtotal' => $this->subtotal(),
            'tax' => $this->tax(),
            'currency' => $this->currency,
            'date' => $this->date()->toIso8601String(),
            'due_date' => $this->dueDate()?->toIso8601String(),
            'paid_at' => $this->resource->status_transitions?->paid_at
                ? \Carbon\Carbon::createFromTimestamp($this->resource->status_transitions->paid_at)->toIso8601String()
                : null,
            'invoice_pdf' => $this->invoice_pdf,
            'hosted_invoice_url' => $this->hosted_invoice_url,
            'lines' => collect($this->invoiceLineItems())->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'amount' => $item->amount,
                    'currency' => $item->currency,
                ];
            })->toArray(),
        ];
    }
}
