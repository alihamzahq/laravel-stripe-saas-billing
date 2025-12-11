<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubscribeRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Create a new subscription.
     */
    public function store(SubscribeRequest $request): JsonResponse
    {
        $user = $request->user();

        // Check if user already has an active subscription
        if ($user->subscribed('default')) {
            return response()->json([
                'message' => 'You already have an active subscription. Please cancel or change your plan instead.',
            ], 422);
        }

        $plan = Plan::findOrFail($request->plan_id);

        if (!$plan->is_active) {
            return response()->json([
                'message' => 'This plan is not available.',
            ], 422);
        }

        try {
            $subscription = $this->subscriptionService->create(
                user: $user,
                plan: $plan,
                paymentMethod: $request->payment_method,
                billingPeriod: $request->billing_period ?? 'monthly',
                options: [
                    'payment_method_id' => $request->payment_method_id,
                ]
            );

            return response()->json([
                'message' => 'Subscription created successfully',
                'subscription' => new SubscriptionResource($subscription),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create subscription',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get the authenticated user's subscription.
     */
    public function me(Request $request): JsonResponse
    {
        $subscription = $this->subscriptionService->getSubscription($request->user());

        if (!$subscription) {
            return response()->json([
                'message' => 'No active subscription found',
                'subscription' => null,
            ]);
        }

        return response()->json([
            'subscription' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Cancel the authenticated user's subscription.
     */
    public function cancel(Request $request): JsonResponse
    {
        $subscription = $this->subscriptionService->cancel($request->user());

        if (!$subscription) {
            return response()->json([
                'message' => 'No active subscription to cancel',
            ], 422);
        }

        return response()->json([
            'message' => 'Subscription cancelled successfully. You will have access until the end of your billing period.',
            'subscription' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resume(Request $request): JsonResponse
    {
        $subscription = $this->subscriptionService->resume($request->user());

        if (!$subscription) {
            return response()->json([
                'message' => 'No subscription to resume or subscription is not on grace period',
            ], 422);
        }

        return response()->json([
            'message' => 'Subscription resumed successfully',
            'subscription' => new SubscriptionResource($subscription),
        ]);
    }

    /**
     * Change the subscription plan.
     */
    public function changePlan(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'billing_period' => ['nullable', 'in:monthly,yearly'],
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        if (!$plan->is_active) {
            return response()->json([
                'message' => 'This plan is not available.',
            ], 422);
        }

        try {
            $subscription = $this->subscriptionService->changePlan(
                user: $request->user(),
                newPlan: $plan,
                billingPeriod: $request->billing_period ?? 'monthly'
            );

            if (!$subscription) {
                return response()->json([
                    'message' => 'No active subscription to change',
                ], 422);
            }

            return response()->json([
                'message' => 'Plan changed successfully',
                'subscription' => new SubscriptionResource($subscription),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to change plan',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
