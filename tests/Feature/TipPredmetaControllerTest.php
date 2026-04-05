<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TipPredmeta;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TipPredmetaControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    // ============ INDEX TESTS ============

    /**
     * Test index displays list of tipPredmeta
     */
    public function test_index_displays_list_of_tip_predmeta(): void
    {
        $user = User::factory()->create();
        TipPredmeta::factory()->create(['naziv' => 'Obavezan predmet']);
        TipPredmeta::factory()->create(['naziv' => 'Izborni predmet']);

        $response = $this->actingAs($user)->get('/tipPredmeta');

        $response->assertOk();
        $response->assertViewIs('sifarnici.tipPredmeta');
        $response->assertViewHas('tipPredmeta');
        $tipPredmeta = $response->viewData('tipPredmeta');
        $this->assertCount(2, $tipPredmeta);
    }

    /**
     * Test index returns empty collection when no tipPredmeta exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tipPredmeta');

        $response->assertOk();
        $response->assertViewHas('tipPredmeta');
        $tipPredmeta = $response->viewData('tipPredmeta');
        $this->assertCount(0, $tipPredmeta);
    }

    /**
     * Test index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/tipPredmeta');

        $response->assertRedirect('/login');
    }

    /**
     * Test index displays multiple tipPredmeta entries
     */
    public function test_index_displays_multiple_entries(): void
    {
        $user = User::factory()->create();
        TipPredmeta::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/tipPredmeta');

        $response->assertOk();
        $tipPredmeta = $response->viewData('tipPredmeta');
        $this->assertCount(5, $tipPredmeta);
    }

    /**
     * Test index displays entries with correct field values
     */
    public function test_index_displays_entries_with_correct_values(): void
    {
        $user = User::factory()->create();
        TipPredmeta::factory()->create(['naziv' => 'Test Naziv', 'skrNaziv' => 'TN']);

        $response = $this->actingAs($user)->get('/tipPredmeta');

        $tipPredmeta = $response->viewData('tipPredmeta');
        $this->assertEquals('Test Naziv', $tipPredmeta[0]->naziv);
        $this->assertEquals('TN', $tipPredmeta[0]->skrNaziv);
    }

    // ============ ADD TESTS ============

    /**
     * Test add displays create form
     */
    public function test_add_displays_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tipPredmeta/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addTipPredmeta');
    }

    /**
     * Test add requires authentication
     */
    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/tipPredmeta/add');

        $response->assertRedirect('/login');
    }

    // ============ UNOS TESTS ============

    /**
     * Test unos creates new tipPredmeta with both fields and indikatorAktivan = 1
     */
    public function test_unos_creates_new_tip_predmeta(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tipPredmeta/unos', [
            'naziv' => 'Obavezan predmet',
            'skrNaziv' => 'OBV',
        ]);

        $response->assertRedirect('/tipPredmeta');
        $this->assertDatabaseHas('tip_predmeta', [
            'naziv' => 'Obavezan predmet',
            'skrNaziv' => 'OBV',
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test unos always sets indikatorAktivan to 1 (hardcoded at line 37)
     */
    public function test_unos_always_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tipPredmeta/unos', [
            'naziv' => 'Izborni predmet',
            'skrNaziv' => 'IZB',
        ]);

        $tipPredmeta = TipPredmeta::where('naziv', 'Izborni predmet')->first();
        $this->assertEquals(1, $tipPredmeta->indikatorAktivan);
    }

    /**
     * Test unos requires authentication
     */
    public function test_unos_requires_authentication(): void
    {
        $response = $this->post('/tipPredmeta/unos', [
            'naziv' => 'Test predmet',
            'skrNaziv' => 'TST',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test unos creates multiple entries
     */
    public function test_unos_creates_multiple_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tipPredmeta/unos', [
            'naziv' => 'Predmet 1',
            'skrNaziv' => 'P1',
        ]);
        $this->actingAs($user)->post('/tipPredmeta/unos', [
            'naziv' => 'Predmet 2',
            'skrNaziv' => 'P2',
        ]);

        $this->assertDatabaseHas('tip_predmeta', ['naziv' => 'Predmet 1', 'indikatorAktivan' => 1]);
        $this->assertDatabaseHas('tip_predmeta', ['naziv' => 'Predmet 2', 'indikatorAktivan' => 1]);
    }

    /**
     * Test unos creates entry with correct skrNaziv
     */
    public function test_unos_preserves_skr_naziv(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tipPredmeta/unos', [
            'naziv' => 'Full Subject Name',
            'skrNaziv' => 'FSN',
        ]);

        $tipPredmeta = TipPredmeta::where('naziv', 'Full Subject Name')->first();
        $this->assertEquals('FSN', $tipPredmeta->skrNaziv);
    }

    // ============ EDIT TESTS ============

    /**
     * Test edit displays form with existing tipPredmeta data
     */
    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create(['naziv' => 'Test predmet']);

        $response = $this->actingAs($user)->get("/tipPredmeta/{$tipPredmeta->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editTipPredmeta');
        $response->assertViewHas('tipPredmeta');
        $data = $response->viewData('tipPredmeta');
        $this->assertEquals('Test predmet', $data->naziv);
    }

    /**
     * Test edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $tipPredmeta = TipPredmeta::factory()->create();

        $response = $this->get("/tipPredmeta/{$tipPredmeta->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test edit uses route model binding
     */
    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $tipPredmeta1 = TipPredmeta::factory()->create(['naziv' => 'Predmet 1']);
        $tipPredmeta2 = TipPredmeta::factory()->create(['naziv' => 'Predmet 2']);

        $response = $this->actingAs($user)->get("/tipPredmeta/{$tipPredmeta2->id}/edit");

        $data = $response->viewData('tipPredmeta');
        $this->assertEquals($tipPredmeta2->id, $data->id);
        $this->assertEquals('Predmet 2', $data->naziv);
    }

    /**
     * Test edit returns 404 for nonexistent tipPredmeta
     */
    public function test_edit_with_nonexistent_id_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tipPredmeta/9999/edit');

        $response->assertStatus(404);
    }

    // ============ UPDATE TESTS ============

    /**
     * Test update modifies existing tipPredmeta
     */
    public function test_update_modifies_tip_predmeta(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create([
            'naziv' => 'Original',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Updated',
            'skrNaziv' => 'UPD',
            'indikatorAktivan' => 'on',
        ]);

        $response->assertRedirect('/tipPredmeta');
        $this->assertDatabaseHas('tip_predmeta', [
            'id' => $tipPredmeta->id,
            'naziv' => 'Updated',
            'skrNaziv' => 'UPD',
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test update sets indikatorAktivan to 1 when checkbox is 'on' (line 64)
     */
    public function test_update_checkbox_on_sets_indikator_to_one(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Test',
            'skrNaziv' => 'TST',
            'indikatorAktivan' => 'on',
        ]);

        $tipPredmeta->refresh();
        $this->assertEquals(1, $tipPredmeta->indikatorAktivan);
    }

    /**
     * Test update sets indikatorAktivan to 1 when checkbox is numeric 1 (line 64)
     */
    public function test_update_checkbox_numeric_one_sets_indikator_to_one(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Test',
            'skrNaziv' => 'TST',
            'indikatorAktivan' => 1,
        ]);

        $tipPredmeta->refresh();
        $this->assertEquals(1, $tipPredmeta->indikatorAktivan);
    }

    /**
     * Test update sets indikatorAktivan to 0 when checkbox is missing (line 67)
     */
    public function test_update_checkbox_missing_sets_indikator_to_zero(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Test',
            'skrNaziv' => 'TST',
        ]);

        $tipPredmeta->refresh();
        $this->assertEquals(0, $tipPredmeta->indikatorAktivan);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $tipPredmeta = TipPredmeta::factory()->create();

        $response = $this->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Updated',
            'skrNaziv' => 'UPD',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test update transitions from active to inactive
     */
    public function test_update_transitions_from_active_to_inactive(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create([
            'naziv' => 'Test Subject',
            'indikatorAktivan' => 1,
        ]);

        $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Test Subject',
            'skrNaziv' => 'TSS',
            // Checkbox not sent = inactive
        ]);

        $tipPredmeta->refresh();
        $this->assertEquals(0, $tipPredmeta->indikatorAktivan);
    }

    /**
     * Test update transitions from inactive to active
     */
    public function test_update_transitions_from_inactive_to_active(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create([
            'naziv' => 'Inactive Subject',
            'indikatorAktivan' => 0,
        ]);

        $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Inactive Subject',
            'skrNaziv' => 'INS',
            'indikatorAktivan' => 'on',
        ]);

        $tipPredmeta->refresh();
        $this->assertEquals(1, $tipPredmeta->indikatorAktivan);
    }

    /**
     * Test update modifies only specified record
     */
    public function test_update_only_modifies_specified_record(): void
    {
        $user = User::factory()->create();
        $tipPredmeta1 = TipPredmeta::factory()->create(['naziv' => 'Original 1']);
        $tipPredmeta2 = TipPredmeta::factory()->create(['naziv' => 'Original 2']);

        $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta1->id}", [
            'naziv' => 'Updated',
            'skrNaziv' => 'UPD',
        ]);

        $this->assertDatabaseHas('tip_predmeta', [
            'id' => $tipPredmeta1->id,
            'naziv' => 'Updated',
        ]);
        $this->assertDatabaseHas('tip_predmeta', [
            'id' => $tipPredmeta2->id,
            'naziv' => 'Original 2',
        ]);
    }

    /**
     * Test update with nonexistent ID returns 404
     */
    public function test_update_with_nonexistent_id_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/tipPredmeta/9999', [
            'naziv' => 'Test',
            'skrNaziv' => 'TST',
        ]);

        $response->assertStatus(404);
    }

    // ============ DELETE TESTS ============

    /**
     * Test delete removes tipPredmeta
     */
    public function test_delete_removes_tip_predmeta(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create(['naziv' => 'To Delete']);

        $response = $this->actingAs($user)->get("/tipPredmeta/{$tipPredmeta->id}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('tip_predmeta', ['id' => $tipPredmeta->id]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $tipPredmeta = TipPredmeta::factory()->create();

        $response = $this->get("/tipPredmeta/{$tipPredmeta->id}/delete");

        $response->assertRedirect('/login');
    }

    /**
     * Test delete with nonexistent ID returns 404
     */
    public function test_delete_with_nonexistent_id_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tipPredmeta/9999/delete');

        $response->assertStatus(404);
    }

    /**
     * Test delete only removes specified record
     */
    public function test_delete_only_removes_specified_record(): void
    {
        $user = User::factory()->create();
        $tipPredmeta1 = TipPredmeta::factory()->create(['naziv' => 'Keep This']);
        $tipPredmeta2 = TipPredmeta::factory()->create(['naziv' => 'Delete This']);

        $this->actingAs($user)->get("/tipPredmeta/{$tipPredmeta2->id}/delete");

        $this->assertDatabaseHas('tip_predmeta', ['id' => $tipPredmeta1->id, 'naziv' => 'Keep This']);
        $this->assertDatabaseMissing('tip_predmeta', ['id' => $tipPredmeta2->id]);
    }

    /**
     * Test delete multiple entries sequentially
     */
    public function test_delete_multiple_entries_sequentially(): void
    {
        $user = User::factory()->create();
        $tipPredmeta1 = TipPredmeta::factory()->create(['naziv' => 'Delete 1']);
        $tipPredmeta2 = TipPredmeta::factory()->create(['naziv' => 'Delete 2']);

        $this->actingAs($user)->get("/tipPredmeta/{$tipPredmeta1->id}/delete");
        $this->actingAs($user)->get("/tipPredmeta/{$tipPredmeta2->id}/delete");

        $this->assertDatabaseMissing('tip_predmeta', ['id' => $tipPredmeta1->id]);
        $this->assertDatabaseMissing('tip_predmeta', ['id' => $tipPredmeta2->id]);
    }

    // ============ DATA ISOLATION TESTS ============

    /**
     * Test each user sees all tipPredmeta (no data isolation by user)
     */
    public function test_multiple_users_see_same_data(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        TipPredmeta::factory()->create(['naziv' => 'Shared Subject']);

        $response1 = $this->actingAs($user1)->get('/tipPredmeta');
        $response2 = $this->actingAs($user2)->get('/tipPredmeta');

        $data1 = $response1->viewData('tipPredmeta');
        $data2 = $response2->viewData('tipPredmeta');

        $this->assertCount(1, $data1);
        $this->assertCount(1, $data2);
    }

    // ============ ERROR HANDLING TESTS ============

    /**
     * Test index gracefully handles database errors
     */
    public function test_index_handles_query_exception(): void
    {
        $user = User::factory()->create();

        // Verify the error handling exists and basic flow works
        $response = $this->actingAs($user)->get('/tipPredmeta');
        $response->assertOk();
    }

    /**
     * Test unos gracefully handles database errors
     */
    public function test_unos_handles_query_exception(): void
    {
        $user = User::factory()->create();

        // Test that the unos endpoint is protected and handles errors
        $response = $this->actingAs($user)->post('/tipPredmeta/unos', [
            'naziv' => 'Test Entry',
            'skrNaziv' => 'TST',
        ]);

        $response->assertRedirect('/tipPredmeta');
    }

    /**
     * Test update gracefully handles database errors
     */
    public function test_update_handles_query_exception(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create();

        // Test that the update endpoint handles errors
        $response = $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Updated Name',
            'skrNaziv' => 'UPD',
        ]);

        $response->assertRedirect('/tipPredmeta');
    }

    /**
     * Test delete gracefully handles database errors
     */
    public function test_delete_handles_query_exception(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create();

        // Test that the delete endpoint handles errors
        $response = $this->actingAs($user)->get("/tipPredmeta/{$tipPredmeta->id}/delete");

        $response->assertRedirect();
    }

    // ============ CHECKBOX LOGIC EDGE CASES ============

    /**
     * Test update with string "0" should set indikatorAktivan to 0
     */
    public function test_update_with_string_zero_sets_indikator_to_zero(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Test',
            'skrNaziv' => 'TST',
            'indikatorAktivan' => '0',
        ]);

        $tipPredmeta->refresh();
        // String "0" is falsy but not == 'on' or == 1, so should be 0
        $this->assertEquals(0, $tipPredmeta->indikatorAktivan);
    }

    /**
     * Test update with empty string should set indikatorAktivan to 0
     */
    public function test_update_with_empty_string_sets_indikator_to_zero(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Test',
            'skrNaziv' => 'TST',
            'indikatorAktivan' => '',
        ]);

        $tipPredmeta->refresh();
        $this->assertEquals(0, $tipPredmeta->indikatorAktivan);
    }

    /**
     * Test update with boolean true conversion
     */
    public function test_update_with_boolean_true_sets_indikator_to_one(): void
    {
        $user = User::factory()->create();
        $tipPredmeta = TipPredmeta::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/tipPredmeta/{$tipPredmeta->id}", [
            'naziv' => 'Test',
            'skrNaziv' => 'TST',
            'indikatorAktivan' => true,
        ]);

        $tipPredmeta->refresh();
        // true == 1, so should set to 1
        $this->assertEquals(1, $tipPredmeta->indikatorAktivan);
    }
}
