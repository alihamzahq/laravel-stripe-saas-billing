<?php

namespace Tests\Feature\Api;

use App\Models\Plan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlanTest extends TestCase
{
    #[Test]
    public function plans_index_returns_only_active_plans_ordered_by_sort_order(): void
    {
        $planA = Plan::factory()->create(['name' => 'Starter', 'sort_order' => 0]);
        $planB = Plan::factory()->create(['name' => 'Pro', 'sort_order' => 1]);
        Plan::factory()->inactive()->create(['name' => 'Hidden']);
        $planC = Plan::factory()->create(['name' => 'Business', 'sort_order' => 2]);

        $response = $this->getJson('/api/v1/plans');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(3, $data);
        $this->assertSame($planA->id, $data[0]['id']);
        $this->assertSame($planB->id, $data[1]['id']);
        $this->assertSame($planC->id, $data[2]['id']);
    }

    #[Test]
    public function plans_show_returns_404_for_inactive_plan(): void
    {
        $plan = Plan::factory()->inactive()->create();

        $response = $this->getJson("/api/v1/plans/{$plan->id}");

        $response->assertNotFound();
    }
}
