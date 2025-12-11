<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Services\PaymentMethodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentMethodController extends Controller
{
    public function __construct(
        protected PaymentMethodService $paymentMethodService
    ) {}

    /**
     * List user's payment methods.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $paymentMethods = $this->paymentMethodService->getPaymentMethods($request->user());

        return PaymentMethodResource::collection($paymentMethods);
    }

    /**
     * Add a new payment method.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'card_holder_name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $paymentMethod = $this->paymentMethodService->addPaymentMethod(
                user: $request->user(),
                token: $request->token,
                cardHolderName: $request->card_holder_name
            );

            return response()->json([
                'message' => 'Payment method added successfully',
                'payment_method' => new PaymentMethodResource($paymentMethod),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add payment method',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove a payment method.
     */
    public function destroy(Request $request, string $payment_method): JsonResponse
    {
        try {
            $removed = $this->paymentMethodService->removePaymentMethod($request->user(), $payment_method);

            if (!$removed) {
                return response()->json([
                    'message' => 'Payment method not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Payment method removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove payment method',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Create a SetupIntent for adding a new payment method.
     */
    public function setupIntent(Request $request): JsonResponse
    {
        try {
            $clientSecret = $this->paymentMethodService->createSetupIntent($request->user());

            return response()->json([
                'client_secret' => $clientSecret,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create setup intent',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Set a payment method as default.
     */
    public function setDefault(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method_id' => ['required', 'string'],
        ]);

        try {
            $updated = $this->paymentMethodService->setDefaultPaymentMethod(
                $request->user(),
                $request->payment_method_id
            );

            if (!$updated) {
                return response()->json([
                    'message' => 'Payment method not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Default payment method updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update default payment method',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
