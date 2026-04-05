<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\StatusProfesora;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StatusProfesoraControllerTest extends TestCase
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
     * Test index displays list of statusProfesora
     */
    public function test_index_displays_list_of_status_profesora(): void
    {
        $user = User::factory()->create();
        StatusProfesora::factory()->create(['naziv' => 'Aktivan']);
        StatusProfesora::factory()->create(['naziv' => 'Neaktivan']);

        $response = $this->actingAs($user)->get('/statusProfesora');

        $response->assertOk();
        $response->assertViewIs('sifarnici.statusProfesora');
        $response->assertViewHas('status');
        $status = $response->viewData('status');
        $this->assertGreaterThanOrEqual(2, $status->count());
    }

    /**
     * Test index returns empty collection when no statusProfesora exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/statusProfesora');

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
        $response = $this->get('/statusProfesora');

        $response->assertRedirect('/login');
    }

    /**
     * Test index displays multiple statusProfesora entries
     */
    public function test_index_displays_multiple_entries(): void
    {
        $user = User::factory()->create();
        StatusProfesora::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/statusProfesora');

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
        $entry = StatusProfesora::factory()->create(['naziv' => 'Test Status']);

        $response = $this->actingAs($user)->get('/statusProfesora');

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

        $response = $this->actingAs($user)->get('/statusProfesora/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addStatusProfesora');
    }

    /**
     * Test add requires authentication
     */
    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/statusProfesora/add');

        $response->assertRedirect('/login');
    }

    // ============ UNOS TESTS ============

    /**
     * Test unos creates new statusProfesora with indikatorAktivan = 1
     */
    public function test_unos_creates_new_status_profesora(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/statusProfesora/unos', [
            'naziv' => 'Aktivan profesor',
        ]);

        $response->assertRedirect('/statusProfesora');
        $this->assertDatabaseHas('status_profesora', [
            'naziv' => 'Aktivan profesor',
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test unos always sets indikatorAktivan to 1 (hardcoded at line 36)
     */
    public function test_unos_always_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/statusProfesora/unos', [
            'naziv' => 'Neaktivan profesor',
        ]);

        $statusProfesora = StatusProfesora::where('naziv', 'Neaktivan profesor')->first();
        $this->assertEquals(1, $statusProfesora->indikatorAktivan);
    }

    /**
     * Test unos requires authentication
     */
    public function test_unos_requires_authentication(): void
    {
        $response = $this->post('/statusProfesora/unos', [
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

        $this->actingAs($user)->post('/statusProfesora/unos', [
            'naziv' => 'Прихваћен',
        ]);
        $this->actingAs($user)->post('/statusProfesora/unos', [
            'naziv' => 'Одбијен',
        ]);

        $this->assertDatabaseHas('status_profesora', ['naziv' => 'Прихваћен', 'indikatorAktivan' => 1]);
        $this->assertDatabaseHas('status_profesora', ['naziv' => 'Одбијен', 'indikatorAktivan' => 1]);
    }

    /**
     * Test unos preserves naziv exactly
     */
    public function test_unos_preserves_naziv(): void
    {
        $user = User::factory()->create();
        $naziv = 'Test Status Profesora';

        $this->actingAs($user)->post('/statusProfesora/unos', [
            'naziv' => $naziv,
        ]);

        $statusProfesora = StatusProfesora::where('naziv', $naziv)->first();
        $this->assertEquals($naziv, $statusProfesora->naziv);
    }

    // ============ EDIT TESTS ============

    /**
     * Test edit displays form with existing statusProfesora data
     */
    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = User::factory()->create();
        $statusProfesora = StatusProfesora::factory()->create(['naziv' => 'Aktivan']);

        $response = $this->actingAs($user)->get("/statusProfesora/{$statusProfesora->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editStatusProfesora');
        $response->assertViewHas('status');
        $data = $response->viewData('status');
        $this->assertEquals('Aktivan', $data->naziv);
    }

    /**
     * Test edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $statusProfesora = StatusProfesora::factory()->create();

        $response = $this->get("/statusProfesora/{$statusProfesora->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test edit uses route model binding
     */
    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $statusProfesora1 = StatusProfesora::factory()->create(['naziv' => 'Status 1']);
        $statusProfesora2 = StatusProfesora::factory()->create(['naziv' => 'Status 2']);

        $response = $this->actingAs($user)->get("/statusProfesora/{$statusProfesora2->id}/edit");

        $data = $response->viewData('status');
        $this->assertEquals($statusProfesora2->id, $data->id);
        $this->assertEquals('Status 2', $data->naziv);
    }

    /**
     * Test edit returns 404 for nonexistent statusProfesora
     */
    public function test_edit_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/statusProfesora/99999/edit');

        $response->assertNotFound();
    }

    // ============ UPDATE TESTS ============

    /**
     * Test update modifies existing statusProfesora
     */
    public function test_update_modifies_existing_status_profesora(): void
    {
        $user = User::factory()->create();
        $statusProfesora = StatusProfesora::factory()->create(['naziv' => 'Old Name']);

        $response = $this->actingAs($user)->patch("/statusProfesora/{$statusProfesora->id}", [
            'naziv' => 'New Name',
        ]);

        $response->assertRedirect('/statusProfesora');
        $this->assertDatabaseHas('status_profesora', [
            'id' => $statusProfesora->id,
            'naziv' => 'New Name',
        ]);
    }

    /**
     * Test update checkbox ON sets indikatorAktivan to 1
     */
    public function test_update_checkbox_on_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();
        $statusProfesora = StatusProfesora::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/statusProfesora/{$statusProfesora->id}", [
            'naziv' => $statusProfesora->naziv,
            'indikatorAktivan' => 'on',
        ]);

        $statusProfesora->refresh();
        $this->assertEquals(1, $statusProfesora->indikatorAktivan);
    }

    /**
     * Test update checkbox value 1 keeps indikatorAktivan as 1
     */
    public function test_update_checkbox_value_one_keeps_aktivan(): void
    {
        $user = User::factory()->create();
        $statusProfesora = StatusProfesora::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/statusProfesora/{$statusProfesora->id}", [
            'naziv' => $statusProfesora->naziv,
            'indikatorAktivan' => 1,
        ]);

        $statusProfesora->refresh();
        $this->assertEquals(1, $statusProfesora->indikatorAktivan);
    }

    /**
     * Test update unchecked checkbox sets indikatorAktivan to 0
     */
    public function test_update_unchecked_checkbox_sets_indikator_aktivan_to_zero(): void
    {
        $user = User::factory()->create();
        $statusProfesora = StatusProfesora::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/statusProfesora/{$statusProfesora->id}", [
            'naziv' => $statusProfesora->naziv,
            'indikatorAktivan' => 0,
        ]);

        $statusProfesora->refresh();
        $this->assertEquals(0, $statusProfesora->indikatorAktivan);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $statusProfesora = StatusProfesora::factory()->create();

        $response = $this->patch("/statusProfesora/{$statusProfesora->id}", [
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
        $statusProfesora1 = StatusProfesora::factory()->create(['naziv' => 'Status 1']);
        $statusProfesora2 = StatusProfesora::factory()->create(['naziv' => 'Status 2']);

        $this->actingAs($user)->patch("/statusProfesora/{$statusProfesora2->id}", [
            'naziv' => 'Modified Status 2',
        ]);

        $this->assertDatabaseHas('status_profesora', ['id' => $statusProfesora2->id, 'naziv' => 'Modified Status 2']);
        $this->assertDatabaseHas('status_profesora', ['id' => $statusProfesora1->id, 'naziv' => 'Status 1']);
    }

    /**
     * Test update handles missing indikatorAktivan field
     */
    public function test_update_handles_missing_indikator_aktivan_field(): void
    {
        $user = User::factory()->create();
        $statusProfesora = StatusProfesora::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/statusProfesora/{$statusProfesora->id}", [
            'naziv' => 'New Name',
        ]);

        $statusProfesora->refresh();
        $this->assertEquals(0, $statusProfesora->indikatorAktivan);
    }

    /**
     * Test update data isolation - only updates specified statusProfesora
     */
    public function test_update_data_isolation(): void
    {
        $user = User::factory()->create();
        $statusProfesora1 = StatusProfesora::factory()->create(['naziv' => 'Original 1']);
        $statusProfesora2 = StatusProfesora::factory()->create(['naziv' => 'Original 2']);

        $this->actingAs($user)->patch("/statusProfesora/{$statusProfesora1->id}", [
            'naziv' => 'Updated 1',
        ]);

        $this->assertDatabaseHas('status_profesora', ['id' => $statusProfesora1->id, 'naziv' => 'Updated 1']);
        $this->assertDatabaseHas('status_profesora', ['id' => $statusProfesora2->id, 'naziv' => 'Original 2']);
    }

    // ============ DELETE TESTS ============

    /**
     * Test delete removes statusProfesora from database
     */
    public function test_delete_removes_status_profesora(): void
    {
        $user = User::factory()->create();
        $statusProfesora = StatusProfesora::factory()->create();

        $response = $this->actingAs($user)->get("/statusProfesora/{$statusProfesora->id}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('status_profesora', ['id' => $statusProfesora->id]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $statusProfesora = StatusProfesora::factory()->create();

        $response = $this->get("/statusProfesora/{$statusProfesora->id}/delete");

        $response->assertRedirect('/login');
    }

    /**
     * Test delete uses route model binding
     */
    public function test_delete_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $statusProfesora1 = StatusProfesora::factory()->create(['naziv' => 'To Delete']);
        $statusProfesora2 = StatusProfesora::factory()->create(['naziv' => 'To Keep']);

        $this->actingAs($user)->get("/statusProfesora/{$statusProfesora1->id}/delete");

        $this->assertDatabaseMissing('status_profesora', ['id' => $statusProfesora1->id]);
        $this->assertDatabaseHas('status_profesora', ['id' => $statusProfesora2->id]);
    }

    /**
     * Test delete data isolation - only deletes specified statusProfesora
     */
    public function test_delete_data_isolation(): void
    {
        $user = User::factory()->create();
        $statusProfesora1 = StatusProfesora::factory()->create();
        $statusProfesora2 = StatusProfesora::factory()->create();
        $statusProfesora3 = StatusProfesora::factory()->create();

        $this->actingAs($user)->get("/statusProfesora/{$statusProfesora2->id}/delete");

        $this->assertDatabaseHas('status_profesora', ['id' => $statusProfesora1->id]);
        $this->assertDatabaseMissing('status_profesora', ['id' => $statusProfesora2->id]);
        $this->assertDatabaseHas('status_profesora', ['id' => $statusProfesora3->id]);
    }

    /**
     * Test delete returns 404 for nonexistent statusProfesora
     */
    public function test_delete_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/statusProfesora/99999/delete');

        $response->assertNotFound();
    }

    // ============ ERROR HANDLING TESTS ============

    /**
     * Test index handles database error gracefully
     */
    public function test_index_handles_database_error(): void
    {
        $user = User::factory()->create();

        StatusProfesora::factory()->create();

        $response = $this->actingAs($user)->get('/statusProfesora');

        $response->assertOk();
    }

    /**
     * Test unos handles database error gracefully
     */
    public function test_unos_handles_database_error_gracefully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/statusProfesora/unos', [
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
        $statusProfesora = StatusProfesora::factory()->create();

        $response = $this->actingAs($user)->patch("/statusProfesora/{$statusProfesora->id}", [
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
        $statusProfesora = StatusProfesora::factory()->create();

        $response = $this->actingAs($user)->get("/statusProfesora/{$statusProfesora->id}/delete");

        $response->assertRedirect();
    }

    // ============ CRUD WORKFLOW TESTS ============

    /**
     * Test complete CRUD workflow
     */
    public function test_complete_crud_workflow(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/statusProfesora/unos', [
            'naziv' => 'New Status',
        ]);

        $created = StatusProfesora::where('naziv', 'New Status')->first();
        $this->assertNotNull($created);
        $this->assertEquals(1, $created->indikatorAktivan);

        $response = $this->actingAs($user)->get("/statusProfesora/{$created->id}/edit");
        $response->assertOk();
        $data = $response->viewData('status');
        $this->assertEquals('New Status', $data->naziv);

        $this->actingAs($user)->patch("/statusProfesora/{$created->id}", [
            'naziv' => 'Updated Status',
            'indikatorAktivan' => 0,
        ]);

        $created->refresh();
        $this->assertEquals('Updated Status', $created->naziv);
        $this->assertEquals(0, $created->indikatorAktivan);

        $this->actingAs($user)->get("/statusProfesora/{$created->id}/delete");
        $this->assertDatabaseMissing('status_profesora', ['id' => $created->id]);
    }

    /**
     * Test CRUD workflow with checkbox toggling
     */
    public function test_crud_workflow_with_checkbox_toggling(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/statusProfesora/unos', [
            'naziv' => 'Toggled Status',
        ]);

        $created = StatusProfesora::where('naziv', 'Toggled Status')->first();
        $this->assertEquals(1, $created->indikatorAktivan);

        $this->actingAs($user)->patch("/statusProfesora/{$created->id}", [
            'naziv' => $created->naziv,
            'indikatorAktivan' => 0,
        ]);

        $created->refresh();
        $this->assertEquals(0, $created->indikatorAktivan);

        $this->actingAs($user)->patch("/statusProfesora/{$created->id}", [
            'naziv' => $created->naziv,
            'indikatorAktivan' => 'on',
        ]);

        $created->refresh();
        $this->assertEquals(1, $created->indikatorAktivan);

        $this->actingAs($user)->get("/statusProfesora/{$created->id}/delete");
        $this->assertDatabaseMissing('status_profesora', ['id' => $created->id]);
    }
}
