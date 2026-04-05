<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();
    }

    protected function authenticatedUser(): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => 'user_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Model::reguard();
        Mockery::close();
        parent::tearDown();
    }

    // ============================================================
    // INDEX METHOD TESTS
    // ============================================================

    public function test_index_returns_home_view_for_authenticated_user(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    public function test_index_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // ADMIN TEST METHOD TESTS
    // ============================================================

    public function test_admin_test_returns_form_view_for_authenticated_user(): void
    {
        $user = $this->authenticatedUser();
        $user->update(['role' => 'admin']);
        $this->actingAs($user);

        $response = $this->get('/admintest');

        $response->assertStatus(200);
        $response->assertViewIs('form');
    }

    public function test_admin_test_redirects_non_admin_user_away(): void
    {
        $user = $this->authenticatedUser();
        $user->update(['role' => 'student']);
        $this->actingAs($user);

        $response = $this->get('/admintest');

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }
}
