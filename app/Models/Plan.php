<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'features',
        'monthly_price',
        'yearly_price',
        'stripe_product_id',
        'stripe_price_id_monthly',
        'stripe_price_id_yearly',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'monthly_price' => 'integer',
        'yearly_price' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to get only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order plans by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(\Laravel\Cashier\Subscription::class, 'plan_id');
    }

    /**
     * Get the formatted monthly price.
     */
    public function getFormattedMonthlyPriceAttribute(): string
    {
        return '$' . number_format($this->monthly_price / 100, 2);
    }

    /**
     * Get the formatted yearly price.
     */
    public function getFormattedYearlyPriceAttribute(): string
    {
        return '$' . number_format($this->yearly_price / 100, 2);
    }

    /**
     * Get the Stripe price ID based on billing period.
     */
    public function getPriceId(string $period = 'monthly'): ?string
    {
        return $period === 'yearly'
            ? $this->stripe_price_id_yearly
            : $this->stripe_price_id_monthly;
    }

    /**
     * Get the price based on billing period.
     */
    public function getPrice(string $period = 'monthly'): int
    {
        return $period === 'yearly'
            ? $this->yearly_price
            : $this->monthly_price;
    }
}
