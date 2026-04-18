<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cashier.key' => 'pk_test_fake',
            'cashier.secret' => 'sk_test_fake',
            'services.stripe.webhook_secret' => 'whsec_test_fake',
        ]);
    }

    protected function actingAsUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $this->actingAs($user);

        return $user;
    }

    protected function actingAsAdmin(array $attributes = []): User
    {
        $admin = User::factory()->create(array_merge(['is_admin' => true], $attributes));
        $this->actingAs($admin);

        return $admin;
    }
}
