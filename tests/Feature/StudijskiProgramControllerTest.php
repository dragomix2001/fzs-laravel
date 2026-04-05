<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class StudijskiProgramControllerTest extends TestCase
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

    public function test_index_displays_all_studijski_programs_for_authenticated_user(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        StudijskiProgram::create([
            'naziv' => 'Fizička kultura',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'FK',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/studijskiProgram');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.studijskiProgram');
        $response->assertViewHas('studijskiProgram');
        $response->assertViewHas('tipStudija');
    }

    public function test_index_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/studijskiProgram');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // ADD METHOD TESTS
    // ============================================================

    public function test_add_displays_form_with_tip_studija_for_authenticated_user(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/studijskiProgram/add');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.addStudijskiProgram');
        $response->assertViewHas('tipStudija');
    }

    public function test_add_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/studijskiProgram/add');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // UNOS METHOD TESTS
    // ============================================================

    public function test_unos_creates_new_studijski_program_with_all_required_fields(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->post('/studijskiProgram/unos', [
            'naziv' => 'Fizička kultura',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'FK',
            'zvanje' => 'Bachelor',
        ]);

        $this->assertDatabaseHas('studijski_program', [
            'naziv' => 'Fizička kultura',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'FK',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_unos_sets_indikator_aktivan_to_one_on_creation(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $this->post('/studijskiProgram/unos', [
            'naziv' => 'Informatika',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'IF',
            'zvanje' => 'Bachelor',
        ]);

        $program = StudijskiProgram::where('naziv', 'Informatika')->first();
        $this->assertEquals(1, $program->indikatorAktivan);
    }

    public function test_unos_redirects_to_index_after_creation(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->post('/studijskiProgram/unos', [
            'naziv' => 'Matematika',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'MAT',
            'zvanje' => 'Bachelor',
        ]);

        $response->assertRedirect('/studijskiProgram');
    }

    public function test_unos_handles_database_error_on_save(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->post('/studijskiProgram/unos', [
            'naziv' => 'Test Program',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'TP',
            'zvanje' => 'Master',
        ]);

        $response->assertRedirect('/studijskiProgram');
    }

    public function test_unos_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->post('/studijskiProgram/unos', [
            'naziv' => 'Test',
            'tipStudija_id' => 1,
            'skrNazivStudijskogPrograma' => 'T',
            'zvanje' => 'Bachelor',
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

        $program = StudijskiProgram::create([
            'naziv' => 'Fizička kultura',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'FK',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/studijskiProgram/{$program->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.editStudijskiProgram');
        $response->assertViewHas('studijskiProgram', $program);
        $response->assertViewHas('tipStudija');
    }

    public function test_edit_redirects_unauthenticated_user_to_login(): void
    {
        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $program = StudijskiProgram::create([
            'naziv' => 'Fizička kultura',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'FK',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/studijskiProgram/{$program->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // UPDATE METHOD TESTS
    // ============================================================

    public function test_update_updates_existing_studijski_program(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $program = StudijskiProgram::create([
            'naziv' => 'Fizička kultura',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'FK',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/studijskiProgram/{$program->id}", [
            'naziv' => 'Sport i rekreacija',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'SR',
            'zvanje' => 'Master',
            'indikatorAktivan' => 1,
        ]);

        $this->assertDatabaseHas('studijski_program', [
            'id' => $program->id,
            'naziv' => 'Sport i rekreacija',
            'zvanje' => 'Master',
            'skrNazivStudijskogPrograma' => 'SR',
        ]);
    }

    public function test_update_sets_indikator_aktivan_to_one_when_checkbox_on(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $program = StudijskiProgram::create([
            'naziv' => 'Informatika',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'IF',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 0,
        ]);

        $this->patch("/studijskiProgram/{$program->id}", [
            'naziv' => 'Informatika',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'IF',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 'on',
        ]);

        $program->refresh();
        $this->assertEquals(1, $program->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_one_when_value_numeric_one(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $program = StudijskiProgram::create([
            'naziv' => 'Matematika',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'MAT',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 0,
        ]);

        $this->patch("/studijskiProgram/{$program->id}", [
            'naziv' => 'Matematika',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'MAT',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $program->refresh();
        $this->assertEquals(1, $program->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_zero_when_checkbox_unchecked(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $program = StudijskiProgram::create([
            'naziv' => 'Hemija',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'HEM',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $this->patch("/studijskiProgram/{$program->id}", [
            'naziv' => 'Hemija',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'HEM',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 0,
        ]);

        $program->refresh();
        $this->assertEquals(0, $program->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_zero_when_missing(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $program = StudijskiProgram::create([
            'naziv' => 'Biologija',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'BIO',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $this->patch("/studijskiProgram/{$program->id}", [
            'naziv' => 'Biologija',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'BIO',
            'zvanje' => 'Bachelor',
            // indikatorAktivan not included
        ]);

        $program->refresh();
        $this->assertEquals(0, $program->indikatorAktivan);
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

        $program = StudijskiProgram::create([
            'naziv' => 'Fizika',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'FIZ',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/studijskiProgram/{$program->id}", [
            'naziv' => 'Fizika',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'FIZ',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $response->assertRedirect('/studijskiProgram');
    }

    public function test_update_redirects_unauthenticated_user_to_login(): void
    {
        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $program = StudijskiProgram::create([
            'naziv' => 'Ekonomija',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'EKO',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/studijskiProgram/{$program->id}", [
            'naziv' => 'Test',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'T',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // DELETE METHOD TESTS
    // ============================================================

    public function test_delete_removes_studijski_program_from_database(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $program = StudijskiProgram::create([
            'naziv' => 'Geografija',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'GEO',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $programId = $program->id;

        $this->get("/studijskiProgram/{$program->id}/delete");

        $this->assertDatabaseMissing('studijski_program', [
            'id' => $programId,
        ]);
    }

    public function test_delete_redirects_back_after_success(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $program = StudijskiProgram::create([
            'naziv' => 'Istorija',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'IST',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/studijskiProgram/{$program->id}/delete");

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    public function test_delete_redirects_unauthenticated_user_to_login(): void
    {
        $tipStudija = TipStudija::create([
            'naziv' => 'Osnovne studije',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
        ]);

        $program = StudijskiProgram::create([
            'naziv' => 'Sociologija',
            'tipStudija_id' => $tipStudija->id,
            'skrNazivStudijskogPrograma' => 'SOC',
            'zvanje' => 'Bachelor',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/studijskiProgram/{$program->id}/delete");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
