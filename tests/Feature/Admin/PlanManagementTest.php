<?php

namespace Tests\Feature\Admin;

use App\Models\Plan;
use App\Services\PlanService;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlanManagementTest extends TestCase
{
    #[Test]
    public function admin_can_create_plan(): void
    {
        $this->actingAsAdmin();

        $this->mock(PlanService::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')
                ->once()
                ->with(\Mockery::on(fn ($data) => $data['name'] === 'Premium' && $data['slug'] === 'premium'))
                ->andReturn(Plan::factory()->make(['name' => 'Premium', 'slug' => 'premium']));
        });

        $response = $this->post(route('admin.plans.store'), [
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Premium plan description',
            'features' => ['Feature 1', 'Feature 2'],
            'monthly_price' => 1999,
            'yearly_price' => 19999,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response->assertRedirect(route('admin.plans.index'))
            ->assertSessionHas('success', 'Plan created successfully.');
    }

    #[Test]
    public function admin_can_toggle_plan_active_status(): void
    {
        $this->actingAsAdmin();
        $plan = Plan::factory()->create(['is_active' => true]);

        $this->mock(PlanService::class, function (MockInterface $mock) use ($plan) {
            $mock->shouldReceive('toggleActive')
                ->once()
                ->with(\Mockery::on(fn ($p) => $p->id === $plan->id))
                ->andReturn($plan);
        });

        $response = $this->post(route('admin.plans.toggle-active', $plan));

        $response->assertRedirect(route('admin.plans.index'))
            ->assertSessionHas('success');
    }
}
