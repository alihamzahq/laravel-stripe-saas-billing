<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * List user's invoices.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $user = $request->user();

        if (!$user->hasStripeId()) {
            return response()->json([
                'data' => [],
            ]);
        }

        $invoices = $this->invoiceService->getInvoices(
            $user,
            $request->boolean('include_pending', false)
        );

        return InvoiceResource::collection($invoices);
    }

    /**
     * Get a specific invoice.
     */
    public function show(Request $request, string $invoice): JsonResponse
    {
        $user = $request->user();

        $invoiceData = $this->invoiceService->getInvoice($user, $invoice);

        if (!$invoiceData) {
            return response()->json([
                'message' => 'Invoice not found',
            ], 404);
        }

        return response()->json([
            'invoice' => new InvoiceResource($invoiceData),
        ]);
    }

    /**
     * Download invoice as PDF.
     */
    public function download(Request $request, string $invoice): mixed
    {
        $user = $request->user();

        $response = $this->invoiceService->downloadInvoice($user, $invoice, [
            'vendor' => config('app.name'),
            'product' => 'Subscription',
        ]);

        if (!$response) {
            return response()->json([
                'message' => 'Invoice not found',
            ], 404);
        }

        return $response;
    }
}
