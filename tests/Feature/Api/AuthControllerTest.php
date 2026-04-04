<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        $this->user = User::create([
            'name' => 'Api User',
            'email' => 'api.auth@test.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('user.id', $this->user->id);
        $response->assertJsonPath('message', 'Успешна пријава');
        $this->assertIsString($response->json('token'));
        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 422);
        $response->assertJsonPath('error.validation.email.0', 'Подаци за пријаву нису исправни.');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_user_returns_authenticated_api_user(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/auth/user');

        $response->assertStatus(200);
        $response->assertJsonPath('id', $this->user->id);
        $response->assertJsonMissingPath('password');
    }

    public function test_update_profile_updates_name_and_email(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/auth/profile', [
            'name' => 'Updated Api User',
            'email' => 'updated.api.auth@test.com',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Профил ажуриран');
        $response->assertJsonPath('user.name', 'Updated Api User');
        $response->assertJsonPath('user.email', 'updated.api.auth@test.com');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Api User',
            'email' => 'updated.api.auth@test.com',
        ]);
    }

    public function test_change_password_updates_stored_hash(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password123',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Лозинка промењена');

        $this->user->refresh();
        $this->assertTrue(Hash::check('new-password123', $this->user->password));
    }

    public function test_logout_revokes_current_access_token(): void
    {
        $token = $this->user->createToken('mobile-app');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Одјава успешна');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
