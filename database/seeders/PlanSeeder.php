<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Services\PlanService;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planService = app(PlanService::class);

        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Perfect for individuals and small projects',
                'features' => [
                    '5 Projects',
                    '10 GB Storage',
                    'Email Support',
                    'Basic Analytics',
                ],
                'monthly_price' => 999, // $9.99
                'yearly_price' => 9990, // $99.90 (2 months free)
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Great for growing teams and businesses',
                'features' => [
                    '25 Projects',
                    '100 GB Storage',
                    'Priority Email Support',
                    'Advanced Analytics',
                    'API Access',
                    'Team Collaboration',
                ],
                'monthly_price' => 2999, // $29.99
                'yearly_price' => 29990, // $299.90 (2 months free)
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large organizations with advanced needs',
                'features' => [
                    'Unlimited Projects',
                    '1 TB Storage',
                    '24/7 Phone Support',
                    'Custom Analytics',
                    'Full API Access',
                    'Unlimited Team Members',
                    'Dedicated Account Manager',
                    'Custom Integrations',
                ],
                'monthly_price' => 9999, // $99.99
                'yearly_price' => 99990, // $999.90 (2 months free)
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            // Check if plan already exists
            if (!Plan::where('slug', $planData['slug'])->exists()) {
                $planService->create($planData);
                $this->command->info("Created plan: {$planData['name']}");
            } else {
                $this->command->warn("Plan already exists: {$planData['name']}");
            }
        }
    }
}
