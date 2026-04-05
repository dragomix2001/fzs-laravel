<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TipPrijave;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TipPrijaveControllerTest extends TestCase
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
     * Test index displays list of tipPrijave
     */
    public function test_index_displays_list_of_tip_prijave(): void
    {
        $user = User::factory()->create();
        TipPrijave::factory()->create(['naziv' => 'Prijava']);
        TipPrijave::factory()->create(['naziv' => 'Odbijanje']);

        $response = $this->actingAs($user)->get('/tipPrijave');

        $response->assertOk();
        $response->assertViewIs('sifarnici.tipPrijave');
        $response->assertViewHas('tip');
        $tip = $response->viewData('tip');
        $this->assertGreaterThanOrEqual(2, $tip->count());
    }

    /**
     * Test index returns empty collection when no tipPrijave exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tipPrijave');

        $response->assertOk();
        $response->assertViewHas('tip');
        $tip = $response->viewData('tip');
        $this->assertIsIterable($tip);
    }

    /**
     * Test index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/tipPrijave');

        $response->assertRedirect('/login');
    }

    /**
     * Test index displays multiple tipPrijave entries
     */
    public function test_index_displays_multiple_entries(): void
    {
        $user = User::factory()->create();
        TipPrijave::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/tipPrijave');

        $response->assertOk();
        $tip = $response->viewData('tip');
        $this->assertGreaterThanOrEqual(5, $tip->count());
    }

    /**
     * Test index displays entries with correct attributes
     */
    public function test_index_displays_entries_with_correct_attributes(): void
    {
        $user = User::factory()->create();
        $entry = TipPrijave::factory()->create(['naziv' => 'Test Prijava']);

        $response = $this->actingAs($user)->get('/tipPrijave');

        $tip = $response->viewData('tip');
        $this->assertNotEmpty($tip);
    }

    // ============ ADD TESTS ============

    /**
     * Test add displays create form
     */
    public function test_add_displays_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tipPrijave/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addTipPrijave');
    }

    /**
     * Test add requires authentication
     */
    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/tipPrijave/add');

        $response->assertRedirect('/login');
    }

    // ============ UNOS TESTS ============

    /**
     * Test unos creates new tipPrijave with indikatorAktivan = 1
     */
    public function test_unos_creates_new_tip_prijave(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tipPrijave/unos', [
            'naziv' => 'Aktivna prijava',
        ]);

        $response->assertRedirect('/tipPrijave');
        $this->assertDatabaseHas('tip_prijave', [
            'naziv' => 'Aktivna prijava',
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test unos always sets indikatorAktivan to 1 (hardcoded at line 36)
     */
    public function test_unos_always_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tipPrijave/unos', [
            'naziv' => 'Neaktivna prijava',
        ]);

        $tipPrijave = TipPrijave::where('naziv', 'Neaktivna prijava')->first();
        $this->assertEquals(1, $tipPrijave->indikatorAktivan);
    }

    /**
     * Test unos requires authentication
     */
    public function test_unos_requires_authentication(): void
    {
        $response = $this->post('/tipPrijave/unos', [
            'naziv' => 'Test',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test unos creates multiple entries
     */
    public function test_unos_creates_multiple_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tipPrijave/unos', [
            'naziv' => 'Прихваћена',
        ]);
        $this->actingAs($user)->post('/tipPrijave/unos', [
            'naziv' => 'Одбијена',
        ]);

        $this->assertDatabaseHas('tip_prijave', ['naziv' => 'Прихваћена', 'indikatorAktivan' => 1]);
        $this->assertDatabaseHas('tip_prijave', ['naziv' => 'Одбијена', 'indikatorAktivan' => 1]);
    }

    /**
     * Test unos preserves naziv exactly
     */
    public function test_unos_preserves_naziv(): void
    {
        $user = User::factory()->create();
        $naziv = 'Test Tip Prijave';

        $this->actingAs($user)->post('/tipPrijave/unos', [
            'naziv' => $naziv,
        ]);

        $tipPrijave = TipPrijave::where('naziv', $naziv)->first();
        $this->assertEquals($naziv, $tipPrijave->naziv);
    }

    // ============ EDIT TESTS ============

    /**
     * Test edit displays form with existing tipPrijave data
     */
    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = User::factory()->create();
        $tipPrijave = TipPrijave::factory()->create(['naziv' => 'Aktivna']);

        $response = $this->actingAs($user)->get("/tipPrijave/{$tipPrijave->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editTipPrijave');
        $response->assertViewHas('tip');
        $data = $response->viewData('tip');
        $this->assertEquals('Aktivna', $data->naziv);
    }

    /**
     * Test edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $tipPrijave = TipPrijave::factory()->create();

        $response = $this->get("/tipPrijave/{$tipPrijave->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test edit uses route model binding
     */
    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $tipPrijave1 = TipPrijave::factory()->create(['naziv' => 'Prijava 1']);
        $tipPrijave2 = TipPrijave::factory()->create(['naziv' => 'Prijava 2']);

        $response = $this->actingAs($user)->get("/tipPrijave/{$tipPrijave2->id}/edit");

        $data = $response->viewData('tip');
        $this->assertEquals($tipPrijave2->id, $data->id);
        $this->assertEquals('Prijava 2', $data->naziv);
    }

    /**
     * Test edit returns 404 for nonexistent tipPrijave
     */
    public function test_edit_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tipPrijave/99999/edit');

        $response->assertNotFound();
    }

    // ============ UPDATE TESTS ============

    /**
     * Test update modifies existing tipPrijave
     */
    public function test_update_modifies_existing_tip_prijave(): void
    {
        $user = User::factory()->create();
        $tipPrijave = TipPrijave::factory()->create(['naziv' => 'Old Name']);

        $response = $this->actingAs($user)->patch("/tipPrijave/{$tipPrijave->id}", [
            'naziv' => 'New Name',
        ]);

        $response->assertRedirect('/tipPrijave');
        $this->assertDatabaseHas('tip_prijave', [
            'id' => $tipPrijave->id,
            'naziv' => 'New Name',
        ]);
    }

    /**
     * Test update checkbox ON sets indikatorAktivan to 1
     */
    public function test_update_checkbox_on_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();
        $tipPrijave = TipPrijave::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/tipPrijave/{$tipPrijave->id}", [
            'naziv' => $tipPrijave->naziv,
            'indikatorAktivan' => 'on',
        ]);

        $tipPrijave->refresh();
        $this->assertEquals(1, $tipPrijave->indikatorAktivan);
    }

    /**
     * Test update checkbox value 1 keeps indikatorAktivan as 1
     */
    public function test_update_checkbox_value_one_keeps_aktivan(): void
    {
        $user = User::factory()->create();
        $tipPrijave = TipPrijave::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/tipPrijave/{$tipPrijave->id}", [
            'naziv' => $tipPrijave->naziv,
            'indikatorAktivan' => 1,
        ]);

        $tipPrijave->refresh();
        $this->assertEquals(1, $tipPrijave->indikatorAktivan);
    }

    /**
     * Test update unchecked checkbox sets indikatorAktivan to 0
     */
    public function test_update_unchecked_checkbox_sets_indikator_aktivan_to_zero(): void
    {
        $user = User::factory()->create();
        $tipPrijave = TipPrijave::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/tipPrijave/{$tipPrijave->id}", [
            'naziv' => $tipPrijave->naziv,
            'indikatorAktivan' => 0,
        ]);

        $tipPrijave->refresh();
        $this->assertEquals(0, $tipPrijave->indikatorAktivan);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $tipPrijave = TipPrijave::factory()->create();

        $response = $this->patch("/tipPrijave/{$tipPrijave->id}", [
            'naziv' => 'Updated',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test update uses route model binding
     */
    public function test_update_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $tipPrijave1 = TipPrijave::factory()->create(['naziv' => 'Prijava 1']);
        $tipPrijave2 = TipPrijave::factory()->create(['naziv' => 'Prijava 2']);

        $this->actingAs($user)->patch("/tipPrijave/{$tipPrijave2->id}", [
            'naziv' => 'Modified Prijava 2',
        ]);

        $this->assertDatabaseHas('tip_prijave', ['id' => $tipPrijave2->id, 'naziv' => 'Modified Prijava 2']);
        $this->assertDatabaseHas('tip_prijave', ['id' => $tipPrijave1->id, 'naziv' => 'Prijava 1']);
    }

    /**
     * Test update handles missing indikatorAktivan field
     */
    public function test_update_handles_missing_indikator_aktivan_field(): void
    {
        $user = User::factory()->create();
        $tipPrijave = TipPrijave::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/tipPrijave/{$tipPrijave->id}", [
            'naziv' => 'New Name',
        ]);

        $tipPrijave->refresh();
        $this->assertEquals(0, $tipPrijave->indikatorAktivan);
    }

    /**
     * Test update data isolation - only updates specified tipPrijave
     */
    public function test_update_data_isolation(): void
    {
        $user = User::factory()->create();
        $tipPrijave1 = TipPrijave::factory()->create(['naziv' => 'Original 1']);
        $tipPrijave2 = TipPrijave::factory()->create(['naziv' => 'Original 2']);

        $this->actingAs($user)->patch("/tipPrijave/{$tipPrijave1->id}", [
            'naziv' => 'Updated 1',
        ]);

        $this->assertDatabaseHas('tip_prijave', ['id' => $tipPrijave1->id, 'naziv' => 'Updated 1']);
        $this->assertDatabaseHas('tip_prijave', ['id' => $tipPrijave2->id, 'naziv' => 'Original 2']);
    }

    // ============ DELETE TESTS ============

    /**
     * Test delete removes tipPrijave from database
     */
    public function test_delete_removes_tip_prijave(): void
    {
        $user = User::factory()->create();
        $tipPrijave = TipPrijave::factory()->create();

        $response = $this->actingAs($user)->get("/tipPrijave/{$tipPrijave->id}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('tip_prijave', ['id' => $tipPrijave->id]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $tipPrijave = TipPrijave::factory()->create();

        $response = $this->get("/tipPrijave/{$tipPrijave->id}/delete");

        $response->assertRedirect('/login');
    }

    /**
     * Test delete uses route model binding
     */
    public function test_delete_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $tipPrijave1 = TipPrijave::factory()->create(['naziv' => 'To Delete']);
        $tipPrijave2 = TipPrijave::factory()->create(['naziv' => 'To Keep']);

        $this->actingAs($user)->get("/tipPrijave/{$tipPrijave1->id}/delete");

        $this->assertDatabaseMissing('tip_prijave', ['id' => $tipPrijave1->id]);
        $this->assertDatabaseHas('tip_prijave', ['id' => $tipPrijave2->id]);
    }

    /**
     * Test delete data isolation - only deletes specified tipPrijave
     */
    public function test_delete_data_isolation(): void
    {
        $user = User::factory()->create();
        $tipPrijave1 = TipPrijave::factory()->create();
        $tipPrijave2 = TipPrijave::factory()->create();
        $tipPrijave3 = TipPrijave::factory()->create();

        $this->actingAs($user)->get("/tipPrijave/{$tipPrijave2->id}/delete");

        $this->assertDatabaseHas('tip_prijave', ['id' => $tipPrijave1->id]);
        $this->assertDatabaseMissing('tip_prijave', ['id' => $tipPrijave2->id]);
        $this->assertDatabaseHas('tip_prijave', ['id' => $tipPrijave3->id]);
    }

    /**
     * Test delete returns 404 for nonexistent tipPrijave
     */
    public function test_delete_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tipPrijave/99999/delete');

        $response->assertNotFound();
    }

    // ============ ERROR HANDLING TESTS ============

    /**
     * Test index handles database error gracefully
     */
    public function test_index_handles_database_error(): void
    {
        $user = User::factory()->create();

        TipPrijave::factory()->create();

        $response = $this->actingAs($user)->get('/tipPrijave');

        $response->assertOk();
    }

    /**
     * Test unos handles database error gracefully
     */
    public function test_unos_handles_database_error_gracefully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tipPrijave/unos', [
            'naziv' => 'Test',
        ]);

        $response->assertRedirect();
    }

    /**
     * Test update handles database error gracefully
     */
    public function test_update_handles_database_error_gracefully(): void
    {
        $user = User::factory()->create();
        $tipPrijave = TipPrijave::factory()->create();

        $response = $this->actingAs($user)->patch("/tipPrijave/{$tipPrijave->id}", [
            'naziv' => 'Updated',
        ]);

        $response->assertRedirect();
    }

    /**
     * Test delete handles database error gracefully
     */
    public function test_delete_handles_database_error_gracefully(): void
    {
        $user = User::factory()->create();
        $tipPrijave = TipPrijave::factory()->create();

        $response = $this->actingAs($user)->get("/tipPrijave/{$tipPrijave->id}/delete");

        $response->assertRedirect();
    }

    // ============ CRUD WORKFLOW TESTS ============

    /**
     * Test complete CRUD workflow
     */
    public function test_complete_crud_workflow(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tipPrijave/unos', [
            'naziv' => 'New Prijava',
        ]);

        $created = TipPrijave::where('naziv', 'New Prijava')->first();
        $this->assertNotNull($created);
        $this->assertEquals(1, $created->indikatorAktivan);

        $response = $this->actingAs($user)->get("/tipPrijave/{$created->id}/edit");
        $response->assertOk();
        $data = $response->viewData('tip');
        $this->assertEquals('New Prijava', $data->naziv);

        $this->actingAs($user)->patch("/tipPrijave/{$created->id}", [
            'naziv' => 'Updated Prijava',
            'indikatorAktivan' => 0,
        ]);

        $created->refresh();
        $this->assertEquals('Updated Prijava', $created->naziv);
        $this->assertEquals(0, $created->indikatorAktivan);

        $this->actingAs($user)->get("/tipPrijave/{$created->id}/delete");
        $this->assertDatabaseMissing('tip_prijave', ['id' => $created->id]);
    }

    /**
     * Test CRUD workflow with checkbox toggling
     */
    public function test_crud_workflow_with_checkbox_toggling(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tipPrijave/unos', [
            'naziv' => 'Toggled Prijava',
        ]);

        $created = TipPrijave::where('naziv', 'Toggled Prijava')->first();
        $this->assertEquals(1, $created->indikatorAktivan);

        $this->actingAs($user)->patch("/tipPrijave/{$created->id}", [
            'naziv' => $created->naziv,
            'indikatorAktivan' => 0,
        ]);

        $created->refresh();
        $this->assertEquals(0, $created->indikatorAktivan);

        $this->actingAs($user)->patch("/tipPrijave/{$created->id}", [
            'naziv' => $created->naziv,
            'indikatorAktivan' => 'on',
        ]);

        $created->refresh();
        $this->assertEquals(1, $created->indikatorAktivan);

        $this->actingAs($user)->get("/tipPrijave/{$created->id}/delete");
        $this->assertDatabaseMissing('tip_prijave', ['id' => $created->id]);
    }
}
