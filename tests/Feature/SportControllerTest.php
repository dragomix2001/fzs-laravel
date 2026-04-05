<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class SportControllerTest extends TestCase
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

    public function test_index_displays_all_sport_for_authenticated_user(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        Sport::create([
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/sport');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.sport');
        $response->assertViewHas('sport');
    }

    public function test_index_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/sport');

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

        $response = $this->get('/sport/add');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.addSport');
    }

    public function test_add_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/sport/add');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // UNOS METHOD TESTS
    // ============================================================

    public function test_unos_creates_new_sport_with_all_required_fields(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $response = $this->post('/sport/unos', [
            'naziv' => 'Фудбал',
        ]);

        $this->assertDatabaseHas('sport', [
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_unos_sets_indikator_aktivan_to_one_on_creation(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $this->post('/sport/unos', [
            'naziv' => 'Кошарка',
        ]);

        $sport = Sport::where('naziv', 'Кошарка')->first();
        $this->assertEquals(1, $sport->indikatorAktivan);
    }

    public function test_unos_redirects_to_index_after_creation(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $response = $this->post('/sport/unos', [
            'naziv' => 'Фудбал',
        ]);

        $response->assertRedirect('/sport');
    }

    public function test_unos_creates_multiple_records_with_all_active(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $this->post('/sport/unos', [
            'naziv' => 'Sport 1',
        ]);

        $this->post('/sport/unos', [
            'naziv' => 'Sport 2',
        ]);

        $this->assertDatabaseHas('sport', [
            'naziv' => 'Sport 1',
            'indikatorAktivan' => 1,
        ]);

        $this->assertDatabaseHas('sport', [
            'naziv' => 'Sport 2',
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_unos_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->post('/sport/unos', [
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

        $sport = Sport::create([
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/sport/{$sport->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.editSport');
        $response->assertViewHas('sport', $sport);
    }

    public function test_edit_redirects_unauthenticated_user_to_login(): void
    {
        $sport = Sport::create([
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/sport/{$sport->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // UPDATE METHOD TESTS
    // ============================================================

    public function test_update_updates_existing_sport(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $sport = Sport::create([
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/sport/{$sport->id}", [
            'naziv' => 'Фудбал 2.0',
            'indikatorAktivan' => 1,
        ]);

        $this->assertDatabaseHas('sport', [
            'id' => $sport->id,
            'naziv' => 'Фудбал 2.0',
        ]);
    }

    public function test_update_sets_indikator_aktivan_to_one_when_checkbox_on(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $sport = Sport::create([
            'naziv' => 'Кошарка',
            'indikatorAktivan' => 0,
        ]);

        $this->patch("/sport/{$sport->id}", [
            'naziv' => 'Кошарка',
            'indikatorAktivan' => 'on',
        ]);

        $sport->refresh();
        $this->assertEquals(1, $sport->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_one_when_value_numeric_one(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $sport = Sport::create([
            'naziv' => 'Волеј',
            'indikatorAktivan' => 0,
        ]);

        $this->patch("/sport/{$sport->id}", [
            'naziv' => 'Волеј',
            'indikatorAktivan' => 1,
        ]);

        $sport->refresh();
        $this->assertEquals(1, $sport->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_zero_when_checkbox_unchecked(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $sport = Sport::create([
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);

        $this->patch("/sport/{$sport->id}", [
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 0,
        ]);

        $sport->refresh();
        $this->assertEquals(0, $sport->indikatorAktivan);
    }

    public function test_update_sets_indikator_aktivan_to_zero_when_missing(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $sport = Sport::create([
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);

        $this->patch("/sport/{$sport->id}", [
            'naziv' => 'Фудбал',
            // indikatorAktivan not included
        ]);

        $sport->refresh();
        $this->assertEquals(0, $sport->indikatorAktivan);
    }

    public function test_update_redirects_to_index_after_success(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $sport = Sport::create([
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/sport/{$sport->id}", [
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);

        $response->assertRedirect('/sport');
    }

    public function test_update_redirects_unauthenticated_user_to_login(): void
    {
        $sport = Sport::create([
            'naziv' => 'Кошарка',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch("/sport/{$sport->id}", [
            'naziv' => 'Test',
            'indikatorAktivan' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    // ============================================================
    // DELETE METHOD TESTS
    // ============================================================

    public function test_delete_removes_sport_from_database(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $sport = Sport::create([
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);

        $sportId = $sport->id;

        $this->get("/sport/{$sport->id}/delete");

        $this->assertDatabaseMissing('sport', [
            'id' => $sportId,
        ]);
    }

    public function test_delete_redirects_back_after_success(): void
    {
        $user = $this->authenticatedUser();
        $this->actingAs($user);

        $sport = Sport::create([
            'naziv' => 'Кошарка',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/sport/{$sport->id}/delete");

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    public function test_delete_redirects_unauthenticated_user_to_login(): void
    {
        $sport = Sport::create([
            'naziv' => 'Фудбал',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get("/sport/{$sport->id}/delete");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
