<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlanController extends Controller
{
    /**
     * List all active plans.
     */
    public function index(): AnonymousResourceCollection
    {
        $plans = Plan::active()->ordered()->get();

        return PlanResource::collection($plans);
    }

    /**
     * Show a single plan.
     */
    public function show(Plan $plan): JsonResponse
    {
        if (!$plan->is_active) {
            return response()->json([
                'message' => 'Plan not found',
            ], 404);
        }

        return response()->json([
            'plan' => new PlanResource($plan),
        ]);
    }
}
