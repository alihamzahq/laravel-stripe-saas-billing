<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    #[Test]
    public function user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    #[Test]
    #[DataProvider('invalidRegistrationData')]
    public function registration_fails_with_invalid_data(array $payload, string $expectedErrorField): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([$expectedErrorField]);
    }

    public static function invalidRegistrationData(): array
    {
        return [
            'missing email' => [
                ['name' => 'Jane', 'password' => 'secret123', 'password_confirmation' => 'secret123'],
                'email',
            ],
            'duplicate email' => [
                ['name' => 'Jane', 'email' => 'taken@example.com', 'password' => 'secret123', 'password_confirmation' => 'secret123'],
                'email',
            ],
            'password mismatch' => [
                ['name' => 'Jane', 'email' => 'new@example.com', 'password' => 'secret123', 'password_confirmation' => 'wrong123'],
                'password',
            ],
            'password too short' => [
                ['name' => 'Jane', 'email' => 'new@example.com', 'password' => 'abc', 'password_confirmation' => 'abc'],
                'password',
            ],
        ];
    }

    #[Test]
    public function user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'user', 'token']);
        $this->assertNotEmpty($response->json('token'));
    }

    #[Test]
    public function authenticated_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $response->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
