<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Laravel\Cashier\Invoice;

class InvoiceService
{
    /**
     * Get all invoices for a user.
     */
    public function getInvoices(User $user, bool $includePending = false): Collection
    {
        if (!$user->hasStripeId()) {
            return collect([]);
        }

        return $user->invoices($includePending);
    }

    /**
     * Get a specific invoice by ID.
     */
    public function getInvoice(User $user, string $invoiceId): ?Invoice
    {
        if (!$user->hasStripeId()) {
            return null;
        }

        return $user->findInvoice($invoiceId);
    }

    /**
     * Download invoice as PDF.
     */
    public function downloadInvoice(User $user, string $invoiceId, array $data = []): mixed
    {
        if (!$user->hasStripeId()) {
            return null;
        }

        $invoice = $user->findInvoice($invoiceId);

        if (!$invoice) {
            return null;
        }

        return $invoice->download($data);
    }
}
