<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TipStudija;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class TipStudijaControllerTest extends TestCase
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

    public function test_index_displays_all_tip_studija_for_authenticated_user(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/tipStudija');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.tipStudija');
        $response->assertViewHas('tipStudija');
    }

    public function test_index_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/tipStudija');

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

        $response = $this->get('/tipStudija/add');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.addTipStudija');
    }

    public function test_add_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/tipStudija/add');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // UNOS METHOD TESTS
    // ============================================================

    public function test_unos_creates_new_tip_studija_with_all_required_fields(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $response = $this->post('/tipStudija/unos', [
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
        ]);

        $this->assertDatabaseHas('tip_studija', [
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_unos_sets_indikator_aktivan_to_one_on_creation(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $this->post('/tipStudija/unos', [
            'naziv' => 'Postdiplomske studije',
            'skrNaziv' => 'PS',
        ]);

        $tipStudija = TipStudija::where('naziv', 'Postdiplomske studije')->first();
        $this->assertEquals(1, $tipStudija->indikatorAktivan);
    }

    public function test_unos_redirects_to_index_after_creation(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $response = $this->post('/tipStudija/unos', [
            'naziv' => 'Doktorske studije',
            'skrNaziv' => 'DS',
        ]);

        $response->assertRedirect('/tipStudija');
    }

    public function test_unos_creates_multiple_records_with_all_active(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $this->post('/tipStudija/unos', [
            'naziv' => 'Tip 1',
            'skrNaziv' => 'T1',
        ]);

        $this->post('/tipStudija/unos', [
            'naziv' => 'Tip 2',
            'skrNaziv' => 'T2',
        ]);

        $this->assertDatabaseHas('tip_studija', [
            'naziv' => 'Tip 1',
            'indikatorAktivan' => 1,
        ]);

        $this->assertDatabaseHas('tip_studija', [
            'naziv' => 'Tip 2',
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_unos_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->post('/tipStudija/unos', [
            'naziv' => 'Test',
            'skrNaziv' => 'T',
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

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/tipStudija/{$tipStudija->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.editTipStudija');
        $response->assertViewHas('tipStudija', $tipStudija);
    }

    public function test_edit_redirects_unauthenticated_user_to_login(): void
    {
        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/tipStudija/{$tipStudija->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // UPDATE METHOD TESTS
    // ============================================================

    public function test_update_updates_existing_tip_studija(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/tipStudija/{$tipStudija->id}", [
            'naziv' => 'Osnovne studije 2.0',
            'skrNaziv' => 'OS2',
            'indikatorAktivan' => 1,
        ]);

        $this->assertDatabaseHas('tip_studija', [
            'id' => $tipStudija->id,
            'naziv' => 'Osnovne studije 2.0',
            'skrNaziv' => 'OS2',
        ]);
    }

    public function test_update_sets_indikator_aktivan_to_one_when_checkbox_on(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Postdiplomske studije',
            'skrNaziv' => 'PS',
            'indikatorAktivan' => 0,
        ]);

        $this->patch("/tipStudija/{$tipStudija->id}", [
            'naziv' => 'Postdiplomske studije',
            'skrNaziv' => 'PS',
            'indikatorAktivan' => 'on',
        ]);

        $tipStudija->refresh();
        $this->assertEquals(1, $tipStudija->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_one_when_value_numeric_one(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Doktorske studije',
            'skrNaziv' => 'DS',
            'indikatorAktivan' => 0,
        ]);

        $this->patch("/tipStudija/{$tipStudija->id}", [
            'naziv' => 'Doktorske studije',
            'skrNaziv' => 'DS',
            'indikatorAktivan' => 1,
        ]);

        $tipStudija->refresh();
        $this->assertEquals(1, $tipStudija->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_zero_when_checkbox_unchecked(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Specijalisticke studije',
            'skrNaziv' => 'SP',
            'indikatorAktivan' => 1,
        ]);

        $this->patch("/tipStudija/{$tipStudija->id}", [
            'naziv' => 'Specijalisticke studije',
            'skrNaziv' => 'SP',
            'indikatorAktivan' => 0,
        ]);

        $tipStudija->refresh();
        $this->assertEquals(0, $tipStudija->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_zero_when_missing(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Kraće studije',
            'skrNaziv' => 'KS',
            'indikatorAktivan' => 1,
        ]);

        $this->patch("/tipStudija/{$tipStudija->id}", [
            'naziv' => 'Kraće studije',
            'skrNaziv' => 'KS',
            // indikatorAktivan not included
        ]);

        $tipStudija->refresh();
        $this->assertEquals(0, $tipStudija->indikatorAktivan);
    }

    public function test_update_redirects_to_index_after_success(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/tipStudija/{$tipStudija->id}", [
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $response->assertRedirect('/tipStudija');
    }

    public function test_update_redirects_unauthenticated_user_to_login(): void
    {
        $tipStudija = TipStudija::create([
            'naziv' => 'Postdiplomske studije',
            'skrNaziv' => 'PS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/tipStudija/{$tipStudija->id}", [
            'naziv' => 'Test',
            'skrNaziv' => 'T',
            'indikatorAktivan' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // DELETE METHOD TESTS
    // ============================================================

    public function test_delete_removes_tip_studija_from_database(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $tipStudijaId = $tipStudija->id;

        $this->get("/tipStudija/{$tipStudija->id}/delete");

        $this->assertDatabaseMissing('tip_studija', [
            'id' => $tipStudijaId,
        ]);
    }

    public function test_delete_redirects_back_after_success(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Postdiplomske studije',
            'skrNaziv' => 'PS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/tipStudija/{$tipStudija->id}/delete");

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    public function test_delete_redirects_unauthenticated_user_to_login(): void
    {
        $tipStudija = TipStudija::create([
            'naziv' => 'Doktorske studije',
            'skrNaziv' => 'DS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/tipStudija/{$tipStudija->id}/delete");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
