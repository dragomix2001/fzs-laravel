<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Kandidat;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Пријава');
    }

    public function test_authenticated_user_cannot_see_login_page(): void
    {
        $user = Kandidat::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/home');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = Kandidat::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = Kandidat::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = Kandidat::factory()->create();

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/home');

        $response->assertRedirect('/login');
    }

    public function test_user_can_logout(): void
    {
        $user = Kandidat::factory()->create();

        $this->actingAs($user)
             ->post('/logout')
             ->assertRedirect('/');

        $this->assertGuest();
    }
}