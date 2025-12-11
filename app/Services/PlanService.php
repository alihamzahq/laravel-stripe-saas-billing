<?php

namespace App\Services;

use App\Models\Plan;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;

class PlanService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = Cashier::stripe();
    }

    /**
     * Create a new plan with Stripe product and prices.
     *
     * @param array $data
     * @return Plan
     */
    public function create(array $data): Plan
    {
        // Create Stripe product
        $product = $this->stripe->products->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        // Create monthly price
        $monthlyPrice = $this->stripe->prices->create([
            'product' => $product->id,
            'unit_amount' => $data['monthly_price'],
            'currency' => config('cashier.currency', 'usd'),
            'recurring' => ['interval' => 'month'],
        ]);

        // Create yearly price
        $yearlyPrice = $this->stripe->prices->create([
            'product' => $product->id,
            'unit_amount' => $data['yearly_price'],
            'currency' => config('cashier.currency', 'usd'),
            'recurring' => ['interval' => 'year'],
        ]);

        // Create local plan record
        return Plan::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'features' => $data['features'] ?? [],
            'monthly_price' => $data['monthly_price'],
            'yearly_price' => $data['yearly_price'],
            'stripe_product_id' => $product->id,
            'stripe_price_id_monthly' => $monthlyPrice->id,
            'stripe_price_id_yearly' => $yearlyPrice->id,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    /**
     * Update an existing plan.
     *
     * @param Plan $plan
     * @param array $data
     * @return Plan
     */
    public function update(Plan $plan, array $data): Plan
    {
        // Update Stripe product if name or description changed
        if (isset($data['name']) || isset($data['description'])) {
            $this->stripe->products->update($plan->stripe_product_id, [
                'name' => $data['name'] ?? $plan->name,
                'description' => $data['description'] ?? $plan->description,
            ]);
        }

        // If prices changed, create new Stripe prices (can't update existing prices)
        if (isset($data['monthly_price']) && $data['monthly_price'] !== $plan->monthly_price) {
            $monthlyPrice = $this->stripe->prices->create([
                'product' => $plan->stripe_product_id,
                'unit_amount' => $data['monthly_price'],
                'currency' => config('cashier.currency', 'usd'),
                'recurring' => ['interval' => 'month'],
            ]);

            // Archive old price
            $this->stripe->prices->update($plan->stripe_price_id_monthly, [
                'active' => false,
            ]);

            $data['stripe_price_id_monthly'] = $monthlyPrice->id;
        }

        if (isset($data['yearly_price']) && $data['yearly_price'] !== $plan->yearly_price) {
            $yearlyPrice = $this->stripe->prices->create([
                'product' => $plan->stripe_product_id,
                'unit_amount' => $data['yearly_price'],
                'currency' => config('cashier.currency', 'usd'),
                'recurring' => ['interval' => 'year'],
            ]);

            // Archive old price
            $this->stripe->prices->update($plan->stripe_price_id_yearly, [
                'active' => false,
            ]);

            $data['stripe_price_id_yearly'] = $yearlyPrice->id;
        }

        $plan->update($data);

        return $plan->fresh();
    }

    /**
     * Delete (archive) a plan.
     *
     * @param Plan $plan
     * @return bool
     */
    public function delete(Plan $plan): bool
    {
        // Archive the Stripe product (can't delete products with prices)
        $this->stripe->products->update($plan->stripe_product_id, [
            'active' => false,
        ]);

        // Archive the prices
        if ($plan->stripe_price_id_monthly) {
            $this->stripe->prices->update($plan->stripe_price_id_monthly, [
                'active' => false,
            ]);
        }

        if ($plan->stripe_price_id_yearly) {
            $this->stripe->prices->update($plan->stripe_price_id_yearly, [
                'active' => false,
            ]);
        }

        // Soft delete or hard delete the local plan
        return $plan->delete();
    }

    /**
     * Toggle plan active status.
     *
     * @param Plan $plan
     * @return Plan
     */
    public function toggleActive(Plan $plan): Plan
    {
        $newStatus = !$plan->is_active;

        // Update Stripe product status
        $this->stripe->products->update($plan->stripe_product_id, [
            'active' => $newStatus,
        ]);

        $plan->update(['is_active' => $newStatus]);

        return $plan->fresh();
    }

    /**
     * Get all active plans ordered by sort_order.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivePlans()
    {
        return Plan::active()->ordered()->get();
    }

    /**
     * Get all plans ordered by sort_order.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPlans()
    {
        return Plan::ordered()->get();
    }
}
