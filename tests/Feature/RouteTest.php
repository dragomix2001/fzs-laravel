<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Kandidat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RouteTest extends TestCase
{
    protected function getAuthUser(): User
    {
        return User::first();
    }

    public function test_student_upis_route_loads(): void
    {
        $user = $this->getAuthUser();
        
        if (!$user) {
            $this->markTestSkipped('No users found');
            return;
        }
        
        $kandidat = Kandidat::first();
        if (!$kandidat) {
            $this->markTestSkipped('No Kandidat records found');
            return;
        }
        
        $response = $this->actingAs($user)
            ->get("/student/{$kandidat->id}/upis");
        
        $response->assertStatus(200);
    }

    public function test_dashboard_route_loads(): void
    {
        $user = $this->getAuthUser();
        
        if (!$user) {
            $this->markTestSkipped('No users found');
            return;
        }
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }

    public function test_home_route_loads(): void
    {
        $user = $this->getAuthUser();
        
        if (!$user) {
            $this->markTestSkipped('No users found');
            return;
        }
        
        $response = $this->actingAs($user)->get('/home');
        
        $response->assertStatus(200);
    }

    public function test_root_route_redirects(): void
    {
        $response = $this->get('/');
        
        $response->assertStatus(302);
    }

    public function test_login_route_loads(): void
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
    }

    public function test_ispitni_rok_route_loads(): void
    {
        $user = $this->getAuthUser();
        
        if (!$user) {
            $this->markTestSkipped('No users found');
            return;
        }
        
        $response = $this->actingAs($user)->get('/ispitniRok');
        
        $response->assertStatus(200);
    }

    public function test_bodovanje_route_loads(): void
    {
        $user = $this->getAuthUser();
        
        if (!$user) {
            $this->markTestSkipped('No users found');
            return;
        }
        
        $response = $this->actingAs($user)->get('/bodovanje');
        
        $response->assertStatus(200);
    }
}
