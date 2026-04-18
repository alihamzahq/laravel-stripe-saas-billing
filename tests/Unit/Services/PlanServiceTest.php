<?php

namespace Tests\Unit\Services;

use App\Models\Plan;
use App\Services\PlanService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Stripe\StripeClient;
use Tests\TestCase;

class PlanServiceTest extends TestCase
{
    #[Test]
    public function create_builds_stripe_product_and_prices_and_persists_local_plan(): void
    {
        $stripe = Mockery::mock(StripeClient::class);
        $stripe->products = Mockery::mock();
        $stripe->prices = Mockery::mock();

        $stripe->products->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn ($args) => $args['name'] === 'Premium'))
            ->andReturn((object) ['id' => 'prod_fake_123']);

        $stripe->prices->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn ($args) => $args['unit_amount'] === 1999 && $args['recurring']['interval'] === 'month'))
            ->andReturn((object) ['id' => 'price_monthly_fake']);

        $stripe->prices->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn ($args) => $args['unit_amount'] === 19999 && $args['recurring']['interval'] === 'year'))
            ->andReturn((object) ['id' => 'price_yearly_fake']);

        $service = new PlanService($stripe);

        $plan = $service->create([
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Top tier',
            'features' => ['feat1'],
            'monthly_price' => 1999,
            'yearly_price' => 19999,
        ]);

        $this->assertInstanceOf(Plan::class, $plan);
        $this->assertSame('prod_fake_123', $plan->stripe_product_id);
        $this->assertSame('price_monthly_fake', $plan->stripe_price_id_monthly);
        $this->assertSame('price_yearly_fake', $plan->stripe_price_id_yearly);
        $this->assertDatabaseHas('plans', ['slug' => 'premium', 'monthly_price' => 1999]);
    }
}
