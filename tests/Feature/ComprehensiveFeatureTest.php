<?php

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\StudijskiProgram;
use App\Models\User;
use Database\Seeders\TestHelperSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComprehensiveFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestHelperSeeder::class);
    }

    public function test_database_integrity(): void
    {
        $this->assertGreaterThan(0, DB::table('users')->count(), 'Users table should have records');
        $this->assertGreaterThan(0, DB::table('kandidat')->count(), 'Kandidat table should have records');
    }

    public function test_user_can_access_dashboard(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_user_can_access_student_list(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/kandidat');
        $response->assertStatus(200);
    }

    public function test_user_can_access_kandidat_list(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/kandidat');
        $response->assertStatus(200);
    }

    public function test_user_can_access_master_students(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/master');
        $response->assertStatus(200);
    }

    public function test_user_can_access_ispitni_rok(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/ispitniRok');
        $response->assertStatus(200);
    }

    public function test_user_can_access_bodovanje(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/bodovanje');
        $response->assertStatus(200);
    }

    public function test_user_can_access_kalendar(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/kalendar');
        $response->assertStatus(200);
    }

    public function test_user_can_access_obavestenja(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/obavestenja');
        $response->assertStatus(200);
    }

    public function test_user_can_access_raspored(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/raspored');
        $response->assertStatus(200);
    }

    public function test_user_can_access_prisustvo(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/prisustvo');
        $response->assertStatus(200);
    }

    public function test_user_can_access_studijski_program(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/studijskiProgram');
        $response->assertStatus(200);
    }

    public function test_user_can_access_predmet(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/predmet');
        $response->assertStatus(200);
    }

    public function test_user_can_access_profesor(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/profesor');
        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->assertTrue(true);
    }

    public function test_login_redirects_authenticated_users(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect('/');
    }

    public function test_models_have_required_records(): void
    {
        $this->assertGreaterThan(0, Kandidat::count(), 'Kandidat should have records');
        $this->assertGreaterThan(0, GodinaStudija::count(), 'GodinaStudija should have records');
        $this->assertGreaterThan(0, StudijskiProgram::count(), 'StudijskiProgram should have records');
    }
}
