<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(302);
    }

    public function test_home_page_requires_authentication()
    {
        $response = $this->get('/home');
        $response->assertRedirect('/login');
    }

    public function test_api_routes_exist()
    {
        $response = $this->get('/api');
        $response->assertStatus(200);
    }
}
