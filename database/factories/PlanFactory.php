<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->sentence(),
            'features' => ['Feature A', 'Feature B', 'Feature C'],
            'monthly_price' => 999,
            'yearly_price' => 9999,
            'stripe_product_id' => 'prod_test_'.Str::random(14),
            'stripe_price_id_monthly' => 'price_test_'.Str::random(14),
            'stripe_price_id_yearly' => 'price_test_'.Str::random(14),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
