<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Rick Sanchez',
            'email' => 'rick@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'rick@example.com']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'rick@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Rick Sanchez',
            'email' => 'rick@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_register_fails_when_password_not_confirmed(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Rick Sanchez',
            'email' => 'rick@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'wrong',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_login_returns_token_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'morty@example.com',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'morty@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertOk()->assertJsonStructure(['user' => ['id', 'email'], 'token']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'morty@example.com',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'morty@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_logout_revokes_only_current_token(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('current')->plainTextToken;
        $user->createToken('other');

        $response = $this->withHeader('Authorization', 'Bearer '.$currentToken)
            ->postJson('/api/logout');

        $response->assertNoContent();
        $this->assertCount(1, $user->fresh()->tokens);
    }
}
