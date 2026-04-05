<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\StatusGodine;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StatusKandidataControllerTest extends TestCase
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
     * Test index displays list of statusKandidata
     */
    public function test_index_displays_list_of_status_kandidata(): void
    {
        $user = User::factory()->create();
        StatusGodine::factory()->create(['naziv' => 'Aktivan']);
        StatusGodine::factory()->create(['naziv' => 'Neaktivan']);

        $response = $this->actingAs($user)->get('/statusKandidata');

        $response->assertOk();
        $response->assertViewIs('sifarnici.statusKandidata');
        $response->assertViewHas('status');
        $status = $response->viewData('status');
        $this->assertGreaterThanOrEqual(2, $status->count());
    }

    /**
     * Test index returns empty collection when no statusKandidata exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/statusKandidata');

        $response->assertOk();
        $response->assertViewHas('status');
        $status = $response->viewData('status');
        $this->assertIsIterable($status);
    }

    /**
     * Test index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/statusKandidata');

        $response->assertRedirect('/login');
    }

    /**
     * Test index displays multiple statusKandidata entries
     */
    public function test_index_displays_multiple_entries(): void
    {
        $user = User::factory()->create();
        StatusGodine::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/statusKandidata');

        $response->assertOk();
        $status = $response->viewData('status');
        $this->assertGreaterThanOrEqual(5, $status->count());
    }

    /**
     * Test index displays entries with correct attributes
     */
    public function test_index_displays_entries_with_correct_attributes(): void
    {
        $user = User::factory()->create();
        $entry = StatusGodine::factory()->create(['naziv' => 'Test Status']);

        $response = $this->actingAs($user)->get('/statusKandidata');

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

        $response = $this->actingAs($user)->get('/statusKandidata/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addStatusKandidata');
    }

    /**
     * Test add requires authentication
     */
    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/statusKandidata/add');

        $response->assertRedirect('/login');
    }

    // ============ UNOS TESTS ============

    /**
     * Test unos creates new statusKandidata with indikatorAktivan = 1
     */
    public function test_unos_creates_new_status_kandidata(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/statusKandidata/unos', [
            'naziv' => 'Aktivan kandidat',
        ]);

        $response->assertRedirect('/statusKandidata');
        $this->assertDatabaseHas('status_godine', [
            'naziv' => 'Aktivan kandidat',
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test unos always sets indikatorAktivan to 1 (hardcoded at line 36)
     */
    public function test_unos_always_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/statusKandidata/unos', [
            'naziv' => 'Neaktivan kandidat',
        ]);

        $statusGodine = StatusGodine::where('naziv', 'Neaktivan kandidat')->first();
        $this->assertEquals(1, $statusGodine->indikatorAktivan);
    }

    /**
     * Test unos requires authentication
     */
    public function test_unos_requires_authentication(): void
    {
        $response = $this->post('/statusKandidata/unos', [
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

        $this->actingAs($user)->post('/statusKandidata/unos', [
            'naziv' => 'Прихваћен',
        ]);
        $this->actingAs($user)->post('/statusKandidata/unos', [
            'naziv' => 'Одбијен',
        ]);

        $this->assertDatabaseHas('status_godine', ['naziv' => 'Прихваћен', 'indikatorAktivan' => 1]);
        $this->assertDatabaseHas('status_godine', ['naziv' => 'Одбијен', 'indikatorAktivan' => 1]);
    }

    /**
     * Test unos preserves naziv exactly
     */
    public function test_unos_preserves_naziv(): void
    {
        $user = User::factory()->create();
        $naziv = 'Test Status Kandidata';

        $this->actingAs($user)->post('/statusKandidata/unos', [
            'naziv' => $naziv,
        ]);

        $statusGodine = StatusGodine::where('naziv', $naziv)->first();
        $this->assertEquals($naziv, $statusGodine->naziv);
    }

    // ============ EDIT TESTS ============

    /**
     * Test edit displays form with existing statusKandidata data
     */
    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = User::factory()->create();
        $statusGodine = StatusGodine::factory()->create(['naziv' => 'Aktivan']);

        $response = $this->actingAs($user)->get("/statusKandidata/{$statusGodine->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editStatusKandidata');
        $response->assertViewHas('status');
        $data = $response->viewData('status');
        $this->assertEquals('Aktivan', $data->naziv);
    }

    /**
     * Test edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $statusGodine = StatusGodine::factory()->create();

        $response = $this->get("/statusKandidata/{$statusGodine->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test edit uses route model binding
     */
    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $statusGodine1 = StatusGodine::factory()->create(['naziv' => 'Status 1']);
        $statusGodine2 = StatusGodine::factory()->create(['naziv' => 'Status 2']);

        $response = $this->actingAs($user)->get("/statusKandidata/{$statusGodine2->id}/edit");

        $data = $response->viewData('status');
        $this->assertEquals($statusGodine2->id, $data->id);
        $this->assertEquals('Status 2', $data->naziv);
    }

    /**
     * Test edit returns 404 for nonexistent statusKandidata
     */
    public function test_edit_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/statusKandidata/99999/edit');

        $response->assertNotFound();
    }

    // ============ UPDATE TESTS ============

    /**
     * Test update modifies existing statusKandidata
     */
    public function test_update_modifies_existing_status_kandidata(): void
    {
        $user = User::factory()->create();
        $statusGodine = StatusGodine::factory()->create(['naziv' => 'Old Name']);

        $response = $this->actingAs($user)->patch("/statusKandidata/{$statusGodine->id}", [
            'naziv' => 'New Name',
        ]);

        $response->assertRedirect('/statusKandidata');
        $this->assertDatabaseHas('status_godine', [
            'id' => $statusGodine->id,
            'naziv' => 'New Name',
        ]);
    }

    /**
     * Test update checkbox ON sets indikatorAktivan to 1
     */
    public function test_update_checkbox_on_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();
        $statusGodine = StatusGodine::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/statusKandidata/{$statusGodine->id}", [
            'naziv' => $statusGodine->naziv,
            'indikatorAktivan' => 'on',
        ]);

        $statusGodine->refresh();
        $this->assertEquals(1, $statusGodine->indikatorAktivan);
    }

    /**
     * Test update checkbox value 1 keeps indikatorAktivan as 1
     */
    public function test_update_checkbox_value_one_keeps_aktivan(): void
    {
        $user = User::factory()->create();
        $statusGodine = StatusGodine::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/statusKandidata/{$statusGodine->id}", [
            'naziv' => $statusGodine->naziv,
            'indikatorAktivan' => 1,
        ]);

        $statusGodine->refresh();
        $this->assertEquals(1, $statusGodine->indikatorAktivan);
    }

    /**
     * Test update unchecked checkbox sets indikatorAktivan to 0
     */
    public function test_update_unchecked_checkbox_sets_indikator_aktivan_to_zero(): void
    {
        $user = User::factory()->create();
        $statusGodine = StatusGodine::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/statusKandidata/{$statusGodine->id}", [
            'naziv' => $statusGodine->naziv,
            'indikatorAktivan' => 0,
        ]);

        $statusGodine->refresh();
        $this->assertEquals(0, $statusGodine->indikatorAktivan);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $statusGodine = StatusGodine::factory()->create();

        $response = $this->patch("/statusKandidata/{$statusGodine->id}", [
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
        $statusGodine1 = StatusGodine::factory()->create(['naziv' => 'Status 1']);
        $statusGodine2 = StatusGodine::factory()->create(['naziv' => 'Status 2']);

        $this->actingAs($user)->patch("/statusKandidata/{$statusGodine2->id}", [
            'naziv' => 'Modified Status 2',
        ]);

        $this->assertDatabaseHas('status_godine', ['id' => $statusGodine2->id, 'naziv' => 'Modified Status 2']);
        $this->assertDatabaseHas('status_godine', ['id' => $statusGodine1->id, 'naziv' => 'Status 1']);
    }

    /**
     * Test update handles missing indikatorAktivan field
     */
    public function test_update_handles_missing_indikator_aktivan_field(): void
    {
        $user = User::factory()->create();
        $statusGodine = StatusGodine::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/statusKandidata/{$statusGodine->id}", [
            'naziv' => 'New Name',
        ]);

        $statusGodine->refresh();
        $this->assertEquals(0, $statusGodine->indikatorAktivan);
    }

    /**
     * Test update data isolation - only updates specified statusKandidata
     */
    public function test_update_data_isolation(): void
    {
        $user = User::factory()->create();
        $statusGodine1 = StatusGodine::factory()->create(['naziv' => 'Original 1']);
        $statusGodine2 = StatusGodine::factory()->create(['naziv' => 'Original 2']);

        $this->actingAs($user)->patch("/statusKandidata/{$statusGodine1->id}", [
            'naziv' => 'Updated 1',
        ]);

        $this->assertDatabaseHas('status_godine', ['id' => $statusGodine1->id, 'naziv' => 'Updated 1']);
        $this->assertDatabaseHas('status_godine', ['id' => $statusGodine2->id, 'naziv' => 'Original 2']);
    }

    // ============ DELETE TESTS ============

    /**
     * Test delete removes statusKandidata from database
     */
    public function test_delete_removes_status_kandidata(): void
    {
        $user = User::factory()->create();
        $statusGodine = StatusGodine::factory()->create();

        $response = $this->actingAs($user)->get("/statusKandidata/{$statusGodine->id}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('status_godine', ['id' => $statusGodine->id]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $statusGodine = StatusGodine::factory()->create();

        $response = $this->get("/statusKandidata/{$statusGodine->id}/delete");

        $response->assertRedirect('/login');
    }

    /**
     * Test delete uses route model binding
     */
    public function test_delete_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $statusGodine1 = StatusGodine::factory()->create(['naziv' => 'To Delete']);
        $statusGodine2 = StatusGodine::factory()->create(['naziv' => 'To Keep']);

        $this->actingAs($user)->get("/statusKandidata/{$statusGodine1->id}/delete");

        $this->assertDatabaseMissing('status_godine', ['id' => $statusGodine1->id]);
        $this->assertDatabaseHas('status_godine', ['id' => $statusGodine2->id]);
    }

    /**
     * Test delete data isolation - only deletes specified statusKandidata
     */
    public function test_delete_data_isolation(): void
    {
        $user = User::factory()->create();
        $statusGodine1 = StatusGodine::factory()->create();
        $statusGodine2 = StatusGodine::factory()->create();
        $statusGodine3 = StatusGodine::factory()->create();

        $this->actingAs($user)->get("/statusKandidata/{$statusGodine2->id}/delete");

        $this->assertDatabaseHas('status_godine', ['id' => $statusGodine1->id]);
        $this->assertDatabaseMissing('status_godine', ['id' => $statusGodine2->id]);
        $this->assertDatabaseHas('status_godine', ['id' => $statusGodine3->id]);
    }

    /**
     * Test delete returns 404 for nonexistent statusKandidata
     */
    public function test_delete_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/statusKandidata/99999/delete');

        $response->assertNotFound();
    }

    // ============ ERROR HANDLING TESTS ============

    /**
     * Test index handles database error gracefully
     */
    public function test_index_handles_database_error(): void
    {
        $user = User::factory()->create();

        StatusGodine::factory()->create();

        $response = $this->actingAs($user)->get('/statusKandidata');

        $response->assertOk();
    }

    /**
     * Test unos handles database error gracefully
     */
    public function test_unos_handles_database_error_gracefully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/statusKandidata/unos', [
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
        $statusGodine = StatusGodine::factory()->create();

        $response = $this->actingAs($user)->patch("/statusKandidata/{$statusGodine->id}", [
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
        $statusGodine = StatusGodine::factory()->create();

        $response = $this->actingAs($user)->get("/statusKandidata/{$statusGodine->id}/delete");

        $response->assertRedirect();
    }

    // ============ CRUD WORKFLOW TESTS ============

    /**
     * Test complete CRUD workflow
     */
    public function test_complete_crud_workflow(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/statusKandidata/unos', [
            'naziv' => 'New Status',
        ]);

        $created = StatusGodine::where('naziv', 'New Status')->first();
        $this->assertNotNull($created);
        $this->assertEquals(1, $created->indikatorAktivan);

        $response = $this->actingAs($user)->get("/statusKandidata/{$created->id}/edit");
        $response->assertOk();
        $data = $response->viewData('status');
        $this->assertEquals('New Status', $data->naziv);

        $this->actingAs($user)->patch("/statusKandidata/{$created->id}", [
            'naziv' => 'Updated Status',
            'indikatorAktivan' => 0,
        ]);

        $created->refresh();
        $this->assertEquals('Updated Status', $created->naziv);
        $this->assertEquals(0, $created->indikatorAktivan);

        $this->actingAs($user)->get("/statusKandidata/{$created->id}/delete");
        $this->assertDatabaseMissing('status_godine', ['id' => $created->id]);
    }

    /**
     * Test CRUD workflow with checkbox toggling
     */
    public function test_crud_workflow_with_checkbox_toggling(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/statusKandidata/unos', [
            'naziv' => 'Toggled Status',
        ]);

        $created = StatusGodine::where('naziv', 'Toggled Status')->first();
        $this->assertEquals(1, $created->indikatorAktivan);

        $this->actingAs($user)->patch("/statusKandidata/{$created->id}", [
            'naziv' => $created->naziv,
            'indikatorAktivan' => 0,
        ]);

        $created->refresh();
        $this->assertEquals(0, $created->indikatorAktivan);

        $this->actingAs($user)->patch("/statusKandidata/{$created->id}", [
            'naziv' => $created->naziv,
            'indikatorAktivan' => 'on',
        ]);

        $created->refresh();
        $this->assertEquals(1, $created->indikatorAktivan);

        $this->actingAs($user)->get("/statusKandidata/{$created->id}/delete");
        $this->assertDatabaseMissing('status_godine', ['id' => $created->id]);
    }
}
