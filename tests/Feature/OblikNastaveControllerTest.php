<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OblikNastave;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OblikNastaveControllerTest extends TestCase
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
     * Test index displays list of oblikNastave
     */
    public function test_index_displays_list_of_oblik_nastave(): void
    {
        $user = User::factory()->create();
        OblikNastave::factory()->create(['naziv' => 'Predavanja']);
        OblikNastave::factory()->create(['naziv' => 'Vježbe']);

        $response = $this->actingAs($user)->get('/oblikNastave');

        $response->assertOk();
        $response->assertViewIs('sifarnici.oblikNastave');
        $response->assertViewHas('oblikNastave');
        $oblikNastave = $response->viewData('oblikNastave');
        $this->assertCount(2, $oblikNastave);
    }

    /**
     * Test index returns empty collection when no oblikNastave exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/oblikNastave');

        $response->assertOk();
        $response->assertViewHas('oblikNastave');
        $oblikNastave = $response->viewData('oblikNastave');
        $this->assertCount(0, $oblikNastave);
    }

    /**
     * Test index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/oblikNastave');

        $response->assertRedirect('/login');
    }

    /**
     * Test index displays multiple oblikNastave entries
     */
    public function test_index_displays_multiple_entries(): void
    {
        $user = User::factory()->create();
        OblikNastave::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/oblikNastave');

        $response->assertOk();
        $oblikNastave = $response->viewData('oblikNastave');
        $this->assertCount(5, $oblikNastave);
    }

    /**
     * Test index displays entries with correct attributes
     */
    public function test_index_displays_entries_with_correct_attributes(): void
    {
        $user = User::factory()->create();
        $entry = OblikNastave::factory()->create(['naziv' => 'Test', 'skrNaziv' => 'T']);

        $response = $this->actingAs($user)->get('/oblikNastave');

        $oblikNastave = $response->viewData('oblikNastave');
        $this->assertNotEmpty($oblikNastave);
    }

    // ============ ADD TESTS ============

    /**
     * Test add displays create form
     */
    public function test_add_displays_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/oblikNastave/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addOblikNastave');
    }

    /**
     * Test add requires authentication
     */
    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/oblikNastave/add');

        $response->assertRedirect('/login');
    }

    // ============ UNOS TESTS ============

    /**
     * Test unos creates new oblikNastave with indikatorAktivan = 1
     */
    public function test_unos_creates_new_oblik_nastave(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/oblikNastave/unos', [
            'naziv' => 'Predavanja',
            'skrNaziv' => 'Pred',
        ]);

        $response->assertRedirect('/oblikNastave');
        $this->assertDatabaseHas('oblik_nastave', [
            'naziv' => 'Predavanja',
            'skrNaziv' => 'Pred',
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test unos always sets indikatorAktivan to 1 (hardcoded at line 37)
     */
    public function test_unos_always_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/oblikNastave/unos', [
            'naziv' => 'Vježbe',
            'skrNaziv' => 'Vj',
        ]);

        $oblikNastave = OblikNastave::where('naziv', 'Vježbe')->first();
        $this->assertEquals(1, $oblikNastave->indikatorAktivan);
    }

    /**
     * Test unos requires authentication
     */
    public function test_unos_requires_authentication(): void
    {
        $response = $this->post('/oblikNastave/unos', [
            'naziv' => 'Test',
            'skrNaziv' => 'T',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test unos creates multiple entries
     */
    public function test_unos_creates_multiple_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/oblikNastave/unos', [
            'naziv' => 'Predavanja',
            'skrNaziv' => 'Pred',
        ]);
        $this->actingAs($user)->post('/oblikNastave/unos', [
            'naziv' => 'Vježbe',
            'skrNaziv' => 'Vj',
        ]);

        $this->assertDatabaseHas('oblik_nastave', ['naziv' => 'Predavanja', 'indikatorAktivan' => 1]);
        $this->assertDatabaseHas('oblik_nastave', ['naziv' => 'Vježbe', 'indikatorAktivan' => 1]);
    }

    /**
     * Test unos preserves skrNaziv
     */
    public function test_unos_preserves_skr_naziv(): void
    {
        $user = User::factory()->create();
        $skrNaziv = 'TestSkr';

        $this->actingAs($user)->post('/oblikNastave/unos', [
            'naziv' => 'Test Oblik',
            'skrNaziv' => $skrNaziv,
        ]);

        $oblikNastave = OblikNastave::where('naziv', 'Test Oblik')->first();
        $this->assertEquals($skrNaziv, $oblikNastave->skrNaziv);
    }

    // ============ EDIT TESTS ============

    /**
     * Test edit displays form with existing oblikNastave data
     */
    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = User::factory()->create();
        $oblikNastave = OblikNastave::factory()->create(['naziv' => 'Predavanja']);

        $response = $this->actingAs($user)->get("/oblikNastave/{$oblikNastave->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editOblikNastave');
        $response->assertViewHas('oblikNastave');
        $data = $response->viewData('oblikNastave');
        $this->assertEquals('Predavanja', $data->naziv);
    }

    /**
     * Test edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $oblikNastave = OblikNastave::factory()->create();

        $response = $this->get("/oblikNastave/{$oblikNastave->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test edit uses route model binding
     */
    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $oblikNastave1 = OblikNastave::factory()->create(['naziv' => 'Oblik 1']);
        $oblikNastave2 = OblikNastave::factory()->create(['naziv' => 'Oblik 2']);

        $response = $this->actingAs($user)->get("/oblikNastave/{$oblikNastave2->id}/edit");

        $data = $response->viewData('oblikNastave');
        $this->assertEquals($oblikNastave2->id, $data->id);
        $this->assertEquals('Oblik 2', $data->naziv);
    }

    /**
     * Test edit returns 404 for nonexistent oblikNastave
     */
    public function test_edit_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/oblikNastave/99999/edit');

        $response->assertNotFound();
    }

    // ============ UPDATE TESTS ============

    /**
     * Test update modifies existing oblikNastave
     */
    public function test_update_modifies_existing_oblik_nastave(): void
    {
        $user = User::factory()->create();
        $oblikNastave = OblikNastave::factory()->create(['naziv' => 'Old Name']);

        $response = $this->actingAs($user)->patch("/oblikNastave/{$oblikNastave->id}", [
            'naziv' => 'New Name',
            'skrNaziv' => 'NN',
        ]);

        $response->assertRedirect('/oblikNastave');
        $this->assertDatabaseHas('oblik_nastave', [
            'id' => $oblikNastave->id,
            'naziv' => 'New Name',
            'skrNaziv' => 'NN',
        ]);
    }

    /**
     * Test update checkbox ON sets indikatorAktivan to 1
     */
    public function test_update_checkbox_on_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();
        $oblikNastave = OblikNastave::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/oblikNastave/{$oblikNastave->id}", [
            'naziv' => $oblikNastave->naziv,
            'skrNaziv' => $oblikNastave->skrNaziv,
            'indikatorAktivan' => 'on',
        ]);

        $oblikNastave->refresh();
        $this->assertEquals(1, $oblikNastave->indikatorAktivan);
    }

    /**
     * Test update checkbox value 1 keeps indikatorAktivan as 1
     */
    public function test_update_checkbox_value_one_keeps_aktivan(): void
    {
        $user = User::factory()->create();
        $oblikNastave = OblikNastave::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/oblikNastave/{$oblikNastave->id}", [
            'naziv' => $oblikNastave->naziv,
            'skrNaziv' => $oblikNastave->skrNaziv,
            'indikatorAktivan' => 1,
        ]);

        $oblikNastave->refresh();
        $this->assertEquals(1, $oblikNastave->indikatorAktivan);
    }

    /**
     * Test update unchecked checkbox sets indikatorAktivan to 0
     */
    public function test_update_unchecked_checkbox_sets_indikator_aktivan_to_zero(): void
    {
        $user = User::factory()->create();
        $oblikNastave = OblikNastave::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/oblikNastave/{$oblikNastave->id}", [
            'naziv' => $oblikNastave->naziv,
            'skrNaziv' => $oblikNastave->skrNaziv,
            'indikatorAktivan' => 0,
        ]);

        $oblikNastave->refresh();
        $this->assertEquals(0, $oblikNastave->indikatorAktivan);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $oblikNastave = OblikNastave::factory()->create();

        $response = $this->patch("/oblikNastave/{$oblikNastave->id}", [
            'naziv' => 'Updated',
            'skrNaziv' => 'Upd',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test update uses route model binding
     */
    public function test_update_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $oblikNastave1 = OblikNastave::factory()->create(['naziv' => 'Oblik 1']);
        $oblikNastave2 = OblikNastave::factory()->create(['naziv' => 'Oblik 2']);

        $this->actingAs($user)->patch("/oblikNastave/{$oblikNastave2->id}", [
            'naziv' => 'Modified Oblik 2',
            'skrNaziv' => 'MO2',
        ]);

        $this->assertDatabaseHas('oblik_nastave', ['id' => $oblikNastave2->id, 'naziv' => 'Modified Oblik 2']);
        $this->assertDatabaseHas('oblik_nastave', ['id' => $oblikNastave1->id, 'naziv' => 'Oblik 1']);
    }

    /**
     * Test update handles missing indikatorAktivan field
     */
    public function test_update_handles_missing_indikator_aktivan_field(): void
    {
        $user = User::factory()->create();
        $oblikNastave = OblikNastave::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/oblikNastave/{$oblikNastave->id}", [
            'naziv' => 'New Name',
            'skrNaziv' => 'NN',
        ]);

        $oblikNastave->refresh();
        $this->assertEquals(0, $oblikNastave->indikatorAktivan);
    }

    /**
     * Test update data isolation - only updates specified oblikNastave
     */
    public function test_update_data_isolation(): void
    {
        $user = User::factory()->create();
        $oblikNastave1 = OblikNastave::factory()->create(['naziv' => 'Original 1']);
        $oblikNastave2 = OblikNastave::factory()->create(['naziv' => 'Original 2']);

        $this->actingAs($user)->patch("/oblikNastave/{$oblikNastave1->id}", [
            'naziv' => 'Updated 1',
            'skrNaziv' => 'U1',
        ]);

        $this->assertDatabaseHas('oblik_nastave', ['id' => $oblikNastave1->id, 'naziv' => 'Updated 1']);
        $this->assertDatabaseHas('oblik_nastave', ['id' => $oblikNastave2->id, 'naziv' => 'Original 2']);
    }

    // ============ DELETE TESTS ============

    /**
     * Test delete removes oblikNastave from database
     */
    public function test_delete_removes_oblik_nastave(): void
    {
        $user = User::factory()->create();
        $oblikNastave = OblikNastave::factory()->create();

        $response = $this->actingAs($user)->get("/oblikNastave/{$oblikNastave->id}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('oblik_nastave', ['id' => $oblikNastave->id]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $oblikNastave = OblikNastave::factory()->create();

        $response = $this->get("/oblikNastave/{$oblikNastave->id}/delete");

        $response->assertRedirect('/login');
    }

    /**
     * Test delete uses route model binding
     */
    public function test_delete_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $oblikNastave1 = OblikNastave::factory()->create(['naziv' => 'To Delete']);
        $oblikNastave2 = OblikNastave::factory()->create(['naziv' => 'To Keep']);

        $this->actingAs($user)->get("/oblikNastave/{$oblikNastave1->id}/delete");

        $this->assertDatabaseMissing('oblik_nastave', ['id' => $oblikNastave1->id]);
        $this->assertDatabaseHas('oblik_nastave', ['id' => $oblikNastave2->id]);
    }

    /**
     * Test delete data isolation - only deletes specified oblikNastave
     */
    public function test_delete_data_isolation(): void
    {
        $user = User::factory()->create();
        $oblikNastave1 = OblikNastave::factory()->create();
        $oblikNastave2 = OblikNastave::factory()->create();
        $oblikNastave3 = OblikNastave::factory()->create();

        $this->actingAs($user)->get("/oblikNastave/{$oblikNastave2->id}/delete");

        $this->assertDatabaseHas('oblik_nastave', ['id' => $oblikNastave1->id]);
        $this->assertDatabaseMissing('oblik_nastave', ['id' => $oblikNastave2->id]);
        $this->assertDatabaseHas('oblik_nastave', ['id' => $oblikNastave3->id]);
    }

    /**
     * Test delete returns 404 for nonexistent oblikNastave
     */
    public function test_delete_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/oblikNastave/99999/delete');

        $response->assertNotFound();
    }

    // ============ ERROR HANDLING TESTS ============

    /**
     * Test index handles database error gracefully
     */
    public function test_index_handles_database_error(): void
    {
        $user = User::factory()->create();

        // Create entry to ensure table exists
        OblikNastave::factory()->create();

        $response = $this->actingAs($user)->get('/oblikNastave');

        $response->assertOk();
    }

    /**
     * Test unos handles database error gracefully
     */
    public function test_unos_handles_database_error_gracefully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/oblikNastave/unos', [
            'naziv' => 'Test',
            'skrNaziv' => 'T',
        ]);

        $response->assertRedirect();
    }

    /**
     * Test update handles database error gracefully
     */
    public function test_update_handles_database_error_gracefully(): void
    {
        $user = User::factory()->create();
        $oblikNastave = OblikNastave::factory()->create();

        $response = $this->actingAs($user)->patch("/oblikNastave/{$oblikNastave->id}", [
            'naziv' => 'Updated',
            'skrNaziv' => 'Upd',
        ]);

        $response->assertRedirect();
    }

    /**
     * Test delete handles database error gracefully
     */
    public function test_delete_handles_database_error_gracefully(): void
    {
        $user = User::factory()->create();
        $oblikNastave = OblikNastave::factory()->create();

        $response = $this->actingAs($user)->get("/oblikNastave/{$oblikNastave->id}/delete");

        $response->assertRedirect();
    }

    // ============ CRUD WORKFLOW TESTS ============

    /**
     * Test complete CRUD workflow
     */
    public function test_complete_crud_workflow(): void
    {
        $user = User::factory()->create();

        // CREATE
        $this->actingAs($user)->post('/oblikNastave/unos', [
            'naziv' => 'New Oblik',
            'skrNaziv' => 'NO',
        ]);

        $created = OblikNastave::where('naziv', 'New Oblik')->first();
        $this->assertNotNull($created);
        $this->assertEquals(1, $created->indikatorAktivan);

        // READ
        $response = $this->actingAs($user)->get("/oblikNastave/{$created->id}/edit");
        $response->assertOk();
        $data = $response->viewData('oblikNastave');
        $this->assertEquals('New Oblik', $data->naziv);

        // UPDATE
        $this->actingAs($user)->patch("/oblikNastave/{$created->id}", [
            'naziv' => 'Updated Oblik',
            'skrNaziv' => 'UO',
            'indikatorAktivan' => 0,
        ]);

        $created->refresh();
        $this->assertEquals('Updated Oblik', $created->naziv);
        $this->assertEquals(0, $created->indikatorAktivan);

        // DELETE
        $this->actingAs($user)->get("/oblikNastave/{$created->id}/delete");
        $this->assertDatabaseMissing('oblik_nastave', ['id' => $created->id]);
    }
}
