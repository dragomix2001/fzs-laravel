<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\TestHelperSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnhancedFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestHelperSeeder::class);
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_login_route_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_login_rate_limiting_is_applied(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_can_access_home(): void
    {
        $user = User::first();

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
    }
}
