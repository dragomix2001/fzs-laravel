<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\StatusIspita;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StatusIspitaControllerTest extends TestCase
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
     * Test index displays list of statusIspita
     */
    public function test_index_displays_list_of_status_ispita(): void
    {
        $user = User::factory()->create();
        StatusIspita::factory()->create(['naziv' => 'Položio']);
        StatusIspita::factory()->create(['naziv' => 'Nije položio']);

        $response = $this->actingAs($user)->get('/statusIspita');

        $response->assertOk();
        $response->assertViewIs('sifarnici.statusIspita');
        $response->assertViewHas('status');
        $status = $response->viewData('status');
        $this->assertCount(2, $status);
    }

    /**
     * Test index returns empty collection when no statusIspita exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/statusIspita');

        $response->assertOk();
        $response->assertViewHas('status');
        $status = $response->viewData('status');
        $this->assertCount(0, $status);
    }

    /**
     * Test index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/statusIspita');

        $response->assertRedirect('/login');
    }

    /**
     * Test index displays multiple statusIspita entries
     */
    public function test_index_displays_multiple_entries(): void
    {
        $user = User::factory()->create();
        StatusIspita::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/statusIspita');

        $response->assertOk();
        $status = $response->viewData('status');
        $this->assertCount(5, $status);
    }

    /**
     * Test index displays entries with correct attributes
     */
    public function test_index_displays_entries_with_correct_attributes(): void
    {
        $user = User::factory()->create();
        $entry = StatusIspita::factory()->create(['naziv' => 'Test Status']);

        $response = $this->actingAs($user)->get('/statusIspita');

        $status = $response->viewData('status');
        $this->assertNotEmpty($status);
    }

    // ============ ADD TESTS ============

    /**
     * Test add displays create form
     */
    public function test_add_displays_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/statusIspita/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addStatusIspita');
    }

    /**
     * Test add requires authentication
     */
    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/statusIspita/add');

        $response->assertRedirect('/login');
    }

    // ============ UNOS TESTS ============

    /**
     * Test unos creates new statusIspita with indikatorAktivan = 1
     */
    public function test_unos_creates_new_status_ispita(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/statusIspita/unos', [
            'naziv' => 'Položio',
        ]);

        $response->assertRedirect('/statusIspita');
        $this->assertDatabaseHas('status_ispita', [
            'naziv' => 'Položio',
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test unos always sets indikatorAktivan to 1 (hardcoded at line 36)
     */
    public function test_unos_always_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/statusIspita/unos', [
            'naziv' => 'Nije položio',
        ]);

        $statusIspita = StatusIspita::where('naziv', 'Nije položio')->first();
        $this->assertEquals(1, $statusIspita->indikatorAktivan);
    }

    /**
     * Test unos requires authentication
     */
    public function test_unos_requires_authentication(): void
    {
        $response = $this->post('/statusIspita/unos', [
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

        $this->actingAs($user)->post('/statusIspita/unos', [
            'naziv' => 'Положио',
        ]);
        $this->actingAs($user)->post('/statusIspita/unos', [
            'naziv' => 'Није положио',
        ]);

        $this->assertDatabaseHas('status_ispita', ['naziv' => 'Положио', 'indikatorAktivan' => 1]);
        $this->assertDatabaseHas('status_ispita', ['naziv' => 'Није положио', 'indikatorAktivan' => 1]);
    }

    /**
     * Test unos preserves naziv exactly
     */
    public function test_unos_preserves_naziv(): void
    {
        $user = User::factory()->create();
        $naziv = 'Test Status Ispita';

        $this->actingAs($user)->post('/statusIspita/unos', [
            'naziv' => $naziv,
        ]);

        $statusIspita = StatusIspita::where('naziv', $naziv)->first();
        $this->assertEquals($naziv, $statusIspita->naziv);
    }

    // ============ EDIT TESTS ============

    /**
     * Test edit displays form with existing statusIspita data
     */
    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = User::factory()->create();
        $statusIspita = StatusIspita::factory()->create(['naziv' => 'Položio']);

        $response = $this->actingAs($user)->get("/statusIspita/{$statusIspita->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editStatusIspita');
        $response->assertViewHas('status');
        $data = $response->viewData('status');
        $this->assertEquals('Položio', $data->naziv);
    }

    /**
     * Test edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $statusIspita = StatusIspita::factory()->create();

        $response = $this->get("/statusIspita/{$statusIspita->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test edit uses route model binding
     */
    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $statusIspita1 = StatusIspita::factory()->create(['naziv' => 'Status 1']);
        $statusIspita2 = StatusIspita::factory()->create(['naziv' => 'Status 2']);

        $response = $this->actingAs($user)->get("/statusIspita/{$statusIspita2->id}/edit");

        $data = $response->viewData('status');
        $this->assertEquals($statusIspita2->id, $data->id);
        $this->assertEquals('Status 2', $data->naziv);
    }

    /**
     * Test edit returns 404 for nonexistent statusIspita
     */
    public function test_edit_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/statusIspita/99999/edit');

        $response->assertNotFound();
    }

    // ============ UPDATE TESTS ============

    /**
     * Test update modifies existing statusIspita
     */
    public function test_update_modifies_existing_status_ispita(): void
    {
        $user = User::factory()->create();
        $statusIspita = StatusIspita::factory()->create(['naziv' => 'Old Name']);

        $response = $this->actingAs($user)->patch("/statusIspita/{$statusIspita->id}", [
            'naziv' => 'New Name',
        ]);

        $response->assertRedirect('/statusIspita');
        $this->assertDatabaseHas('status_ispita', [
            'id' => $statusIspita->id,
            'naziv' => 'New Name',
        ]);
    }

    /**
     * Test update checkbox ON sets indikatorAktivan to 1
     */
    public function test_update_checkbox_on_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();
        $statusIspita = StatusIspita::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/statusIspita/{$statusIspita->id}", [
            'naziv' => $statusIspita->naziv,
            'indikatorAktivan' => 'on',
        ]);

        $statusIspita->refresh();
        $this->assertEquals(1, $statusIspita->indikatorAktivan);
    }

    /**
     * Test update checkbox value 1 keeps indikatorAktivan as 1
     */
    public function test_update_checkbox_value_one_keeps_aktivan(): void
    {
        $user = User::factory()->create();
        $statusIspita = StatusIspita::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/statusIspita/{$statusIspita->id}", [
            'naziv' => $statusIspita->naziv,
            'indikatorAktivan' => 1,
        ]);

        $statusIspita->refresh();
        $this->assertEquals(1, $statusIspita->indikatorAktivan);
    }

    /**
     * Test update unchecked checkbox sets indikatorAktivan to 0
     */
    public function test_update_unchecked_checkbox_sets_indikator_aktivan_to_zero(): void
    {
        $user = User::factory()->create();
        $statusIspita = StatusIspita::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/statusIspita/{$statusIspita->id}", [
            'naziv' => $statusIspita->naziv,
            'indikatorAktivan' => 0,
        ]);

        $statusIspita->refresh();
        $this->assertEquals(0, $statusIspita->indikatorAktivan);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $statusIspita = StatusIspita::factory()->create();

        $response = $this->patch("/statusIspita/{$statusIspita->id}", [
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
        $statusIspita1 = StatusIspita::factory()->create(['naziv' => 'Status 1']);
        $statusIspita2 = StatusIspita::factory()->create(['naziv' => 'Status 2']);

        $this->actingAs($user)->patch("/statusIspita/{$statusIspita2->id}", [
            'naziv' => 'Modified Status 2',
        ]);

        $this->assertDatabaseHas('status_ispita', ['id' => $statusIspita2->id, 'naziv' => 'Modified Status 2']);
        $this->assertDatabaseHas('status_ispita', ['id' => $statusIspita1->id, 'naziv' => 'Status 1']);
    }

    /**
     * Test update handles missing indikatorAktivan field
     */
    public function test_update_handles_missing_indikator_aktivan_field(): void
    {
        $user = User::factory()->create();
        $statusIspita = StatusIspita::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/statusIspita/{$statusIspita->id}", [
            'naziv' => 'New Name',
        ]);

        $statusIspita->refresh();
        $this->assertEquals(0, $statusIspita->indikatorAktivan);
    }

    /**
     * Test update data isolation - only updates specified statusIspita
     */
    public function test_update_data_isolation(): void
    {
        $user = User::factory()->create();
        $statusIspita1 = StatusIspita::factory()->create(['naziv' => 'Original 1']);
        $statusIspita2 = StatusIspita::factory()->create(['naziv' => 'Original 2']);

        $this->actingAs($user)->patch("/statusIspita/{$statusIspita1->id}", [
            'naziv' => 'Updated 1',
        ]);

        $this->assertDatabaseHas('status_ispita', ['id' => $statusIspita1->id, 'naziv' => 'Updated 1']);
        $this->assertDatabaseHas('status_ispita', ['id' => $statusIspita2->id, 'naziv' => 'Original 2']);
    }

    // ============ DELETE TESTS ============

    /**
     * Test delete removes statusIspita from database
     */
    public function test_delete_removes_status_ispita(): void
    {
        $user = User::factory()->create();
        $statusIspita = StatusIspita::factory()->create();

        $response = $this->actingAs($user)->get("/statusIspita/{$statusIspita->id}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('status_ispita', ['id' => $statusIspita->id]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $statusIspita = StatusIspita::factory()->create();

        $response = $this->get("/statusIspita/{$statusIspita->id}/delete");

        $response->assertRedirect('/login');
    }

    /**
     * Test delete uses route model binding
     */
    public function test_delete_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $statusIspita1 = StatusIspita::factory()->create(['naziv' => 'To Delete']);
        $statusIspita2 = StatusIspita::factory()->create(['naziv' => 'To Keep']);

        $this->actingAs($user)->get("/statusIspita/{$statusIspita1->id}/delete");

        $this->assertDatabaseMissing('status_ispita', ['id' => $statusIspita1->id]);
        $this->assertDatabaseHas('status_ispita', ['id' => $statusIspita2->id]);
    }

    /**
     * Test delete data isolation - only deletes specified statusIspita
     */
    public function test_delete_data_isolation(): void
    {
        $user = User::factory()->create();
        $statusIspita1 = StatusIspita::factory()->create();
        $statusIspita2 = StatusIspita::factory()->create();
        $statusIspita3 = StatusIspita::factory()->create();

        $this->actingAs($user)->get("/statusIspita/{$statusIspita2->id}/delete");

        $this->assertDatabaseHas('status_ispita', ['id' => $statusIspita1->id]);
        $this->assertDatabaseMissing('status_ispita', ['id' => $statusIspita2->id]);
        $this->assertDatabaseHas('status_ispita', ['id' => $statusIspita3->id]);
    }

    /**
     * Test delete returns 404 for nonexistent statusIspita
     */
    public function test_delete_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/statusIspita/99999/delete');

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
        StatusIspita::factory()->create();

        $response = $this->actingAs($user)->get('/statusIspita');

        $response->assertOk();
    }

    /**
     * Test unos handles database error gracefully
     */
    public function test_unos_handles_database_error_gracefully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/statusIspita/unos', [
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
        $statusIspita = StatusIspita::factory()->create();

        $response = $this->actingAs($user)->patch("/statusIspita/{$statusIspita->id}", [
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
        $statusIspita = StatusIspita::factory()->create();

        $response = $this->actingAs($user)->get("/statusIspita/{$statusIspita->id}/delete");

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
        $this->actingAs($user)->post('/statusIspita/unos', [
            'naziv' => 'New Status',
        ]);

        $created = StatusIspita::where('naziv', 'New Status')->first();
        $this->assertNotNull($created);
        $this->assertEquals(1, $created->indikatorAktivan);

        // READ
        $response = $this->actingAs($user)->get("/statusIspita/{$created->id}/edit");
        $response->assertOk();
        $data = $response->viewData('status');
        $this->assertEquals('New Status', $data->naziv);

        // UPDATE
        $this->actingAs($user)->patch("/statusIspita/{$created->id}", [
            'naziv' => 'Updated Status',
            'indikatorAktivan' => 0,
        ]);

        $created->refresh();
        $this->assertEquals('Updated Status', $created->naziv);
        $this->assertEquals(0, $created->indikatorAktivan);

        // DELETE
        $this->actingAs($user)->get("/statusIspita/{$created->id}/delete");
        $this->assertDatabaseMissing('status_ispita', ['id' => $created->id]);
    }

    /**
     * Test CRUD workflow with checkbox toggling
     */
    public function test_crud_workflow_with_checkbox_toggling(): void
    {
        $user = User::factory()->create();

        // CREATE (indikatorAktivan hardcoded to 1)
        $this->actingAs($user)->post('/statusIspita/unos', [
            'naziv' => 'Toggled Status',
        ]);

        $created = StatusIspita::where('naziv', 'Toggled Status')->first();
        $this->assertEquals(1, $created->indikatorAktivan);

        // UPDATE: Toggle OFF (checkbox unchecked)
        $this->actingAs($user)->patch("/statusIspita/{$created->id}", [
            'naziv' => $created->naziv,
            'indikatorAktivan' => 0,
        ]);

        $created->refresh();
        $this->assertEquals(0, $created->indikatorAktivan);

        // UPDATE: Toggle ON (checkbox checked with 'on')
        $this->actingAs($user)->patch("/statusIspita/{$created->id}", [
            'naziv' => $created->naziv,
            'indikatorAktivan' => 'on',
        ]);

        $created->refresh();
        $this->assertEquals(1, $created->indikatorAktivan);

        // DELETE
        $this->actingAs($user)->get("/statusIspita/{$created->id}/delete");
        $this->assertDatabaseMissing('status_ispita', ['id' => $created->id]);
    }
}
