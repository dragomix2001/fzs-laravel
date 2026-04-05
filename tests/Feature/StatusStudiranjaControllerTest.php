<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\StatusStudiranja;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class StatusStudiranjaControllerTest extends TestCase
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

    public function test_index_displays_all_status_studiranja_for_authenticated_user(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        StatusStudiranja::create([
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/statusStudiranja');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.statusStudiranja');
        $response->assertViewHas('statusStudiranja');
    }

    public function test_index_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/statusStudiranja');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // ADD METHOD TESTS
    // ============================================================

    public function test_add_displays_form_for_authenticated_user(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $response = $this->get('/statusStudiranja/add');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.addStatusStudiranja');
    }

    public function test_add_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/statusStudiranja/add');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // UNOS METHOD TESTS
    // ============================================================

    public function test_unos_creates_new_status_studiranja_with_all_required_fields(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $response = $this->post('/statusStudiranja/unos', [
            'naziv' => 'Активан студент',
        ]);

        $this->assertDatabaseHas('status_studiranja', [
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_unos_sets_indikator_aktivan_to_one_on_creation(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $this->post('/statusStudiranja/unos', [
            'naziv' => 'Неактиван студент',
        ]);

        $statusStudiranja = StatusStudiranja::where('naziv', 'Неактиван студент')->first();
        $this->assertEquals(1, $statusStudiranja->indikatorAktivan);
    }

    public function test_unos_redirects_to_index_after_creation(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $response = $this->post('/statusStudiranja/unos', [
            'naziv' => 'Активан студент',
        ]);

        $response->assertRedirect('/statusStudiranja');
    }

    public function test_unos_creates_multiple_records_with_all_active(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $this->post('/statusStudiranja/unos', [
            'naziv' => 'Status 1',
        ]);

        $this->post('/statusStudiranja/unos', [
            'naziv' => 'Status 2',
        ]);

        $this->assertDatabaseHas('status_studiranja', [
            'naziv' => 'Status 1',
            'indikatorAktivan' => 1,
        ]);

        $this->assertDatabaseHas('status_studiranja', [
            'naziv' => 'Status 2',
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_unos_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->post('/statusStudiranja/unos', [
            'naziv' => 'Test',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // EDIT METHOD TESTS
    // ============================================================

    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/statusStudiranja/{$statusStudiranja->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.editStatusStudiranja');
        $response->assertViewHas('statusStudiranja', $statusStudiranja);
    }

    public function test_edit_redirects_unauthenticated_user_to_login(): void
    {
        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/statusStudiranja/{$statusStudiranja->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // UPDATE METHOD TESTS
    // ============================================================

    public function test_update_updates_existing_status_studiranja(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/statusStudiranja/{$statusStudiranja->id}", [
            'naziv' => 'Активан студент 2.0',
            'indikatorAktivan' => 1,
        ]);

        $this->assertDatabaseHas('status_studiranja', [
            'id' => $statusStudiranja->id,
            'naziv' => 'Активан студент 2.0',
        ]);
    }

    public function test_update_sets_indikator_aktivan_to_one_when_checkbox_on(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Неактиван студент',
            'indikatorAktivan' => 0,
        ]);

        $this->patch("/statusStudiranja/{$statusStudiranja->id}", [
            'naziv' => 'Неактиван студент',
            'indikatorAktivan' => 'on',
        ]);

        $statusStudiranja->refresh();
        $this->assertEquals(1, $statusStudiranja->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_one_when_value_numeric_one(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Одложено',
            'indikatorAktivan' => 0,
        ]);

        $this->patch("/statusStudiranja/{$statusStudiranja->id}", [
            'naziv' => 'Одложено',
            'indikatorAktivan' => 1,
        ]);

        $statusStudiranja->refresh();
        $this->assertEquals(1, $statusStudiranja->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_zero_when_checkbox_unchecked(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);

        $this->patch("/statusStudiranja/{$statusStudiranja->id}", [
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 0,
        ]);

        $statusStudiranja->refresh();
        $this->assertEquals(0, $statusStudiranja->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_zero_when_missing(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);

        $this->patch("/statusStudiranja/{$statusStudiranja->id}", [
            'naziv' => 'Активан студент',
            // indikatorAktivan not included
        ]);

        $statusStudiranja->refresh();
        $this->assertEquals(0, $statusStudiranja->indikatorAktivan);
    }

    public function test_update_redirects_to_index_after_success(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/statusStudiranja/{$statusStudiranja->id}", [
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);

        $response->assertRedirect('/statusStudiranja');
    }

    public function test_update_redirects_unauthenticated_user_to_login(): void
    {
        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Неактиван студент',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/statusStudiranja/{$statusStudiranja->id}", [
            'naziv' => 'Test',
            'indikatorAktivan' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // DELETE METHOD TESTS
    // ============================================================

    public function test_delete_removes_status_studiranja_from_database(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);

        $statusStudiranjaId = $statusStudiranja->id;

        $this->get("/statusStudiranja/{$statusStudiranja->id}/delete");

        $this->assertDatabaseMissing('status_studiranja', [
            'id' => $statusStudiranjaId,
        ]);
    }

    public function test_delete_redirects_back_after_success(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Неактиван студент',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/statusStudiranja/{$statusStudiranja->id}/delete");

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    public function test_delete_redirects_unauthenticated_user_to_login(): void
    {
        $statusStudiranja = StatusStudiranja::create([
            'naziv' => 'Активан студент',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/statusStudiranja/{$statusStudiranja->id}/delete");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
