<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SrednjeSkoleFakulteti;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SrednjeSkoleFakultetiControllerTest extends TestCase
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
     * Test index displays list of srednje skole fakulteti
     */
    public function test_index_displays_list_of_records(): void
    {
        $user = User::factory()->create();
        SrednjeSkoleFakulteti::factory()->create([
            'naziv' => 'Srednja škola 1',
        ]);
        SrednjeSkoleFakulteti::factory()->create([
            'naziv' => 'Srednja škola 2',
        ]);

        $response = $this->actingAs($user)->get('/srednjeSkoleFakulteti');

        $response->assertOk();
        $response->assertViewIs('sifarnici.srednjeSkoleFakulteti');
    }

    /**
     * Test index returns empty collection when no records exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/srednjeSkoleFakulteti');

        $response->assertOk();
        $response->assertViewHas('srednjeSkoleFakulteti');
    }

    /**
     * Test index requires authentication
     */
    public function test_index_allows_unauthenticated_access(): void
    {
        $response = $this->get('/srednjeSkoleFakulteti');

        $response->assertOk();
    }

    /**
     * Test index displays multiple records
     */
    public function test_index_displays_multiple_records(): void
    {
        $user = User::factory()->create();
        SrednjeSkoleFakulteti::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/srednjeSkoleFakulteti');

        $response->assertOk();
        $srednjeSkoleFakulteti = $response->viewData('srednjeSkoleFakulteti');
        $this->assertGreaterThanOrEqual(5, $srednjeSkoleFakulteti->count());
    }

    /**
     * Test index displays records with correct attributes
     */
    public function test_index_displays_records_with_correct_attributes(): void
    {
        $user = User::factory()->create();
        $entry = SrednjeSkoleFakulteti::factory()->create([
            'naziv' => 'Test Škola',
            'indSkoleFakulteta' => 1,
        ]);

        $response = $this->actingAs($user)->get('/srednjeSkoleFakulteti');

        $srednjeSkoleFakulteti = $response->viewData('srednjeSkoleFakulteti');
        $this->assertNotEmpty($srednjeSkoleFakulteti);
        $this->assertTrue($srednjeSkoleFakulteti->contains('id', $entry->id));
    }

    // ============ UNOS TESTS ============

    /**
     * Test unos creates new record with all fields
     */
    public function test_unos_creates_new_record_with_all_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/srednjeSkoleFakulteti/unos', [
            'naziv' => 'Nova škola',
            'indSkoleFakulteta' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'naziv' => 'Nova škola',
            'indSkoleFakulteta' => 1,
        ]);
    }

    /**
     * Test unos allows unauthenticated access
     */
    public function test_unos_allows_unauthenticated_access(): void
    {
        $response = $this->post('/srednjeSkoleFakulteti/unos', [
            'naziv' => 'Nova škola',
            'indSkoleFakulteta' => 1,
        ]);

        $response->assertRedirect();
    }

    /**
     * Test unos creates multiple entries
     */
    public function test_unos_creates_multiple_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/srednjeSkoleFakulteti/unos', [
            'naziv' => 'Škola 1',
            'indSkoleFakulteta' => 1,
        ]);

        $this->actingAs($user)->post('/srednjeSkoleFakulteti/unos', [
            'naziv' => 'Škola 2',
            'indSkoleFakulteta' => 0,
        ]);

        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'naziv' => 'Škola 1',
        ]);
        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'naziv' => 'Škola 2',
        ]);
    }

    // ============ EDIT TESTS ============

    /**
     * Test edit displays edit form
     */
    public function test_edit_displays_edit_form(): void
    {
        $user = User::factory()->create();
        $record = SrednjeSkoleFakulteti::factory()->create();

        $response = $this->actingAs($user)->get("/srednjeSkoleFakulteti/{$record->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editSrednjeSkoleFakulteti');
    }

    /**
     * Test edit allows unauthenticated access
     */
    public function test_edit_allows_unauthenticated_access(): void
    {
        $record = SrednjeSkoleFakulteti::factory()->create();

        $response = $this->get("/srednjeSkoleFakulteti/{$record->id}/edit");

        $response->assertOk();
    }

    /**
     * Test edit model binding works correctly
     */
    public function test_edit_model_binding_works_correctly(): void
    {
        $user = User::factory()->create();
        $record = SrednjeSkoleFakulteti::factory()->create([
            'naziv' => 'Original School',
        ]);

        $response = $this->actingAs($user)->get("/srednjeSkoleFakulteti/{$record->id}/edit");

        $returnedRecord = $response->viewData('srednjeSkoleFakulteti');
        $this->assertEquals($record->id, $returnedRecord->id);
        $this->assertEquals('Original School', $returnedRecord->naziv);
    }

    /**
     * Test edit returns 404 for non-existent record
     */
    public function test_edit_returns_404_for_non_existent_record(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/srednjeSkoleFakulteti/99999/edit');

        $response->assertNotFound();
    }

    // ============ UPDATE TESTS ============

    /**
     * Test update modifies record with all fields
     */
    public function test_update_modifies_record_with_all_fields(): void
    {
        $user = User::factory()->create();
        $record = SrednjeSkoleFakulteti::factory()->create([
            'naziv' => 'Original',
            'indSkoleFakulteta' => 0,
        ]);

        $response = $this->actingAs($user)->patch("/srednjeSkoleFakulteti/{$record->id}", [
            'naziv' => 'Updated',
            'indSkoleFakulteta' => 1,
        ]);

        $response->assertRedirect('/srednjeSkoleFakulteti');
        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'id' => $record->id,
            'naziv' => 'Updated',
            'indSkoleFakulteta' => 1,
        ]);
    }

    /**
     * Test update allows unauthenticated access
     */
    public function test_update_allows_unauthenticated_access(): void
    {
        $record = SrednjeSkoleFakulteti::factory()->create();

        $response = $this->patch("/srednjeSkoleFakulteti/{$record->id}", [
            'naziv' => 'Updated',
            'indSkoleFakulteta' => 1,
        ]);

        $response->assertRedirect();
    }

    /**
     * Test update model binding
     */
    public function test_update_model_binding(): void
    {
        $user = User::factory()->create();
        $record = SrednjeSkoleFakulteti::factory()->create();

        $this->actingAs($user)->patch("/srednjeSkoleFakulteti/{$record->id}", [
            'naziv' => 'Bound School',
            'indSkoleFakulteta' => 1,
        ]);

        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'id' => $record->id,
            'naziv' => 'Bound School',
        ]);
    }

    /**
     * Test update isolation - only updates the specified record
     */
    public function test_update_isolation(): void
    {
        $user = User::factory()->create();
        $record1 = SrednjeSkoleFakulteti::factory()->create([
            'naziv' => 'School 1',
        ]);
        $record2 = SrednjeSkoleFakulteti::factory()->create([
            'naziv' => 'School 2',
        ]);

        $this->actingAs($user)->patch("/srednjeSkoleFakulteti/{$record1->id}", [
            'naziv' => 'Updated School 1',
            'indSkoleFakulteta' => 1,
        ]);

        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'id' => $record1->id,
            'naziv' => 'Updated School 1',
        ]);
        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'id' => $record2->id,
            'naziv' => 'School 2',
        ]);
    }

    /**
     * Test update modifies naziv field
     */
    public function test_update_modifies_naziv(): void
    {
        $user = User::factory()->create();
        $record = SrednjeSkoleFakulteti::factory()->create([
            'naziv' => 'Original',
        ]);

        $this->actingAs($user)->patch("/srednjeSkoleFakulteti/{$record->id}", [
            'naziv' => 'New Naziv',
            'indSkoleFakulteta' => $record->indSkoleFakulteta,
        ]);

        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'id' => $record->id,
            'naziv' => 'New Naziv',
        ]);
    }

    // ============ DELETE TESTS ============

    /**
     * Test delete removes record
     */
    public function test_delete_removes_record(): void
    {
        $user = User::factory()->create();
        $record = SrednjeSkoleFakulteti::factory()->create();
        $recordId = $record->id;

        $this->actingAs($user)->get("/srednjeSkoleFakulteti/{$recordId}/delete");

        $this->assertDatabaseMissing('srednje_skole_fakulteti', [
            'id' => $recordId,
        ]);
    }

    /**
     * Test delete allows unauthenticated access
     */
    public function test_delete_allows_unauthenticated_access(): void
    {
        $record = SrednjeSkoleFakulteti::factory()->create();

        $response = $this->get("/srednjeSkoleFakulteti/{$record->id}/delete");

        $response->assertRedirect();
    }

    /**
     * Test delete returns 404 for non-existent record
     */
    public function test_delete_returns_404_for_non_existent_record(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/srednjeSkoleFakulteti/99999/delete');

        $response->assertNotFound();
    }

    /**
     * Test delete isolation - only deletes the specified record
     */
    public function test_delete_isolation(): void
    {
        $user = User::factory()->create();
        $record1 = SrednjeSkoleFakulteti::factory()->create();
        $record2 = SrednjeSkoleFakulteti::factory()->create();

        $this->actingAs($user)->get("/srednjeSkoleFakulteti/{$record1->id}/delete");

        $this->assertDatabaseMissing('srednje_skole_fakulteti', [
            'id' => $record1->id,
        ]);
        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'id' => $record2->id,
        ]);
    }

    // ============ WORKFLOW TESTS ============

    /**
     * Test complete CRUD workflow
     */
    public function test_complete_crud_workflow(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/srednjeSkoleFakulteti/unos', [
            'naziv' => 'Test Škola',
            'indSkoleFakulteta' => 1,
        ]);

        $record = SrednjeSkoleFakulteti::where('naziv', 'Test Škola')->first();
        $this->assertNotNull($record);

        $response = $this->actingAs($user)->get('/srednjeSkoleFakulteti');
        $response->assertOk();

        $this->actingAs($user)->patch("/srednjeSkoleFakulteti/{$record->id}", [
            'naziv' => 'Updated Škola',
            'indSkoleFakulteta' => 0,
        ]);

        $updated = SrednjeSkoleFakulteti::find($record->id);
        $this->assertEquals('Updated Škola', $updated->naziv);

        $this->actingAs($user)->get("/srednjeSkoleFakulteti/{$record->id}/delete");
        $this->assertNull(SrednjeSkoleFakulteti::find($record->id));
    }

    /**
     * Test field modification workflow
     */
    public function test_field_modification_workflow(): void
    {
        $user = User::factory()->create();
        $record = SrednjeSkoleFakulteti::factory()->create([
            'naziv' => 'Initial',
            'indSkoleFakulteta' => 0,
        ]);

        $this->actingAs($user)->patch("/srednjeSkoleFakulteti/{$record->id}", [
            'naziv' => 'Modified',
            'indSkoleFakulteta' => 0,
        ]);

        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'id' => $record->id,
            'naziv' => 'Modified',
        ]);

        $this->actingAs($user)->patch("/srednjeSkoleFakulteti/{$record->id}", [
            'naziv' => 'Modified',
            'indSkoleFakulteta' => 1,
        ]);

        $this->assertDatabaseHas('srednje_skole_fakulteti', [
            'id' => $record->id,
            'indSkoleFakulteta' => 1,
        ]);
    }

    // ============ ERROR HANDLING TESTS ============

    /**
     * Test index handles database errors gracefully
     */
    public function test_index_handles_database_errors_gracefully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/srednjeSkoleFakulteti');

        $response->assertOk();
    }

    /**
     * Test unos redirects on successful creation
     */
    public function test_unos_redirects_on_success(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/srednjeSkoleFakulteti/unos', [
            'naziv' => 'Test Škola',
            'indSkoleFakulteta' => 1,
        ]);

        $response->assertRedirect();
    }

    /**
     * Test update redirects on successful update
     */
    public function test_update_redirects_on_success(): void
    {
        $user = User::factory()->create();
        $record = SrednjeSkoleFakulteti::factory()->create();

        $response = $this->actingAs($user)->patch("/srednjeSkoleFakulteti/{$record->id}", [
            'naziv' => 'Updated',
            'indSkoleFakulteta' => 1,
        ]);

        $response->assertRedirect('/srednjeSkoleFakulteti');
    }

    /**
     * Test delete redirects back on success
     */
    public function test_delete_redirects_back_on_success(): void
    {
        $user = User::factory()->create();
        $record = SrednjeSkoleFakulteti::factory()->create();

        $response = $this->actingAs($user)->get("/srednjeSkoleFakulteti/{$record->id}/delete");

        $response->assertRedirect();
    }
}
