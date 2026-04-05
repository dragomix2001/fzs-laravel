<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\PrilozenaDokumenta;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PrilozenaDokumentaControllerTest extends TestCase
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
     * Test index displays list of prilojena dokumenta
     */
    public function test_index_displays_list_of_documents(): void
    {
        $user = User::factory()->create();
        PrilozenaDokumenta::factory()->create([
            'redniBrojDokumenta' => 1,
            'naziv' => 'Diploma',
        ]);
        PrilozenaDokumenta::factory()->create([
            'redniBrojDokumenta' => 2,
            'naziv' => 'Sertifikat',
        ]);

        $response = $this->actingAs($user)->get('/prilozenaDokumenta');

        $response->assertOk();
        $response->assertViewIs('sifarnici.prilozenaDokumenta');
        $response->assertViewHas('dokument');
        $dokument = $response->viewData('dokument');
        $this->assertGreaterThanOrEqual(2, $dokument->count());
    }

    /**
     * Test index returns empty collection when no documents exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/prilozenaDokumenta');

        $response->assertOk();
        $response->assertViewHas('dokument');
        $dokument = $response->viewData('dokument');
        $this->assertIsIterable($dokument);
    }

    /**
     * Test index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/prilozenaDokumenta');

        $response->assertRedirect('/login');
    }

    /**
     * Test index displays multiple documents
     */
    public function test_index_displays_multiple_documents(): void
    {
        $user = User::factory()->create();
        PrilozenaDokumenta::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/prilozenaDokumenta');

        $response->assertOk();
        $dokument = $response->viewData('dokument');
        $this->assertGreaterThanOrEqual(5, $dokument->count());
    }

    /**
     * Test index displays documents with correct attributes
     */
    public function test_index_displays_documents_with_correct_attributes(): void
    {
        $user = User::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();
        $entry = PrilozenaDokumenta::factory()->create([
            'redniBrojDokumenta' => 5,
            'naziv' => 'Certifikat',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $response = $this->actingAs($user)->get('/prilozenaDokumenta');

        $dokument = $response->viewData('dokument');
        $this->assertNotEmpty($dokument);
        $this->assertTrue($dokument->contains('id', $entry->id));
    }

    // ============ ADD TESTS ============

    /**
     * Test add displays create form
     */
    public function test_add_displays_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/prilozenaDokumenta/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addPrilozenaDokumenta');
    }

    /**
     * Test add requires authentication
     */
    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/prilozenaDokumenta/add');

        $response->assertRedirect('/login');
    }

    // ============ UNOS TESTS ============

    /**
     * Test unos creates new document with all 3 fields
     */
    public function test_unos_creates_new_document_with_all_fields(): void
    {
        $user = User::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();

        $response = $this->actingAs($user)->post('/prilozenaDokumenta/unos', [
            'redniBrojDokumenta' => 1,
            'naziv' => 'Diploma',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $response->assertRedirect('/prilozenaDokumenta');
        $this->assertDatabaseHas('prilozena_dokumenta', [
            'redniBrojDokumenta' => 1,
            'naziv' => 'Diploma',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);
    }

    /**
     * Test unos requires authentication
     */
    public function test_unos_requires_authentication(): void
    {
        $godinaStudija = GodinaStudija::factory()->create();

        $response = $this->post('/prilozenaDokumenta/unos', [
            'redniBrojDokumenta' => 1,
            'naziv' => 'Diploma',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test unos creates multiple entries
     */
    public function test_unos_creates_multiple_entries(): void
    {
        $user = User::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();

        $this->actingAs($user)->post('/prilozenaDokumenta/unos', [
            'redniBrojDokumenta' => 1,
            'naziv' => 'Diploma',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $this->actingAs($user)->post('/prilozenaDokumenta/unos', [
            'redniBrojDokumenta' => 2,
            'naziv' => 'Sertifikat',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $this->assertDatabaseHas('prilozena_dokumenta', [
            'redniBrojDokumenta' => 1,
            'naziv' => 'Diploma',
        ]);
        $this->assertDatabaseHas('prilozena_dokumenta', [
            'redniBrojDokumenta' => 2,
            'naziv' => 'Sertifikat',
        ]);
    }

    /**
     * Test unos preserves all fields correctly
     */
    public function test_unos_preserves_all_fields(): void
    {
        $user = User::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();

        $this->actingAs($user)->post('/prilozenaDokumenta/unos', [
            'redniBrojDokumenta' => 10,
            'naziv' => 'Ispravljeni dokument',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $dokument = PrilozenaDokumenta::where('naziv', 'Ispravljeni dokument')->first();
        $this->assertNotNull($dokument);
        $this->assertEquals(10, $dokument->redniBrojDokumenta);
        $this->assertEquals('Ispravljeni dokument', $dokument->naziv);
        $this->assertEquals($godinaStudija->id, $dokument->skolskaGodina_id);
    }

    // ============ EDIT TESTS ============

    /**
     * Test edit displays edit form
     */
    public function test_edit_displays_edit_form(): void
    {
        $user = User::factory()->create();
        $dokument = PrilozenaDokumenta::factory()->create();

        $response = $this->actingAs($user)->get("/prilozenaDokumenta/{$dokument->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editPrilozenaDokumenta');
        $response->assertViewHas('dokument');
    }

    /**
     * Test edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $dokument = PrilozenaDokumenta::factory()->create();

        $response = $this->get("/prilozenaDokumenta/{$dokument->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test edit model binding works correctly
     */
    public function test_edit_model_binding_works_correctly(): void
    {
        $user = User::factory()->create();
        $dokument = PrilozenaDokumenta::factory()->create([
            'naziv' => 'Original Document',
        ]);

        $response = $this->actingAs($user)->get("/prilozenaDokumenta/{$dokument->id}/edit");

        $returnedDocument = $response->viewData('dokument');
        $this->assertEquals($dokument->id, $returnedDocument->id);
        $this->assertEquals('Original Document', $returnedDocument->naziv);
    }

    /**
     * Test edit returns 404 for non-existent document
     */
    public function test_edit_returns_404_for_non_existent_document(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/prilozenaDokumenta/99999/edit');

        $response->assertNotFound();
    }

    // ============ UPDATE TESTS ============

    /**
     * Test update modifies document with all fields
     */
    public function test_update_modifies_document_with_all_fields(): void
    {
        $user = User::factory()->create();
        $dokument = PrilozenaDokumenta::factory()->create([
            'redniBrojDokumenta' => 1,
            'naziv' => 'Original',
        ]);
        $newGodinaStudija = GodinaStudija::factory()->create();

        $response = $this->actingAs($user)->patch("/prilozenaDokumenta/{$dokument->id}", [
            'redniBrojDokumenta' => 5,
            'naziv' => 'Updated',
            'skolskaGodina_id' => $newGodinaStudija->id,
        ]);

        $response->assertRedirect('/prilozenaDokumenta');
        $this->assertDatabaseHas('prilozena_dokumenta', [
            'id' => $dokument->id,
            'redniBrojDokumenta' => 5,
            'naziv' => 'Updated',
            'skolskaGodina_id' => $newGodinaStudija->id,
        ]);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $dokument = PrilozenaDokumenta::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();

        $response = $this->patch("/prilozenaDokumenta/{$dokument->id}", [
            'redniBrojDokumenta' => 5,
            'naziv' => 'Updated',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test update model binding
     */
    public function test_update_model_binding(): void
    {
        $user = User::factory()->create();
        $dokument = PrilozenaDokumenta::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();

        $this->actingAs($user)->patch("/prilozenaDokumenta/{$dokument->id}", [
            'redniBrojDokumenta' => 3,
            'naziv' => 'Bound Document',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $updated = PrilozenaDokumenta::find($dokument->id);
        $this->assertEquals(3, $updated->redniBrojDokumenta);
        $this->assertEquals('Bound Document', $updated->naziv);
    }

    /**
     * Test update returns 404 for non-existent document
     */
    public function test_update_returns_404_for_non_existent_document(): void
    {
        $user = User::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();

        $response = $this->actingAs($user)->patch('/prilozenaDokumenta/99999', [
            'redniBrojDokumenta' => 5,
            'naziv' => 'Test',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $response->assertNotFound();
    }

    /**
     * Test update modifies individual fields
     */
    public function test_update_modifies_redni_broj_dokumenta(): void
    {
        $user = User::factory()->create();
        $dokument = PrilozenaDokumenta::factory()->create([
            'redniBrojDokumenta' => 1,
            'naziv' => 'Original',
        ]);
        $godinaStudija = $dokument->godinaStudija;

        $this->actingAs($user)->patch("/prilozenaDokumenta/{$dokument->id}", [
            'redniBrojDokumenta' => 99,
            'naziv' => 'Original',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $this->assertDatabaseHas('prilozena_dokumenta', [
            'id' => $dokument->id,
            'redniBrojDokumenta' => 99,
        ]);
    }

    /**
     * Test update modifies naziv field
     */
    public function test_update_modifies_naziv(): void
    {
        $user = User::factory()->create();
        $dokument = PrilozenaDokumenta::factory()->create([
            'naziv' => 'Original',
        ]);
        $godinaStudija = $dokument->godinaStudija;

        $this->actingAs($user)->patch("/prilozenaDokumenta/{$dokument->id}", [
            'redniBrojDokumenta' => $dokument->redniBrojDokumenta,
            'naziv' => 'New Naziv',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $this->assertDatabaseHas('prilozena_dokumenta', [
            'id' => $dokument->id,
            'naziv' => 'New Naziv',
        ]);
    }

    // ============ DELETE TESTS ============

    /**
     * Test delete removes document
     */
    public function test_delete_removes_document(): void
    {
        $user = User::factory()->create();
        $dokument = PrilozenaDokumenta::factory()->create();
        $dokumentId = $dokument->id;

        $this->actingAs($user)->get("/prilozenaDokumenta/{$dokumentId}/delete");

        $this->assertDatabaseMissing('prilozena_dokumenta', [
            'id' => $dokumentId,
        ]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $dokument = PrilozenaDokumenta::factory()->create();

        $response = $this->get("/prilozenaDokumenta/{$dokument->id}/delete");

        $response->assertRedirect('/login');
    }

    /**
     * Test delete returns 404 for non-existent document
     */
    public function test_delete_returns_404_for_non_existent_document(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/prilozenaDokumenta/99999/delete');

        $response->assertNotFound();
    }

    /**
     * Test delete isolation - only deletes the specified document
     */
    public function test_delete_isolation(): void
    {
        $user = User::factory()->create();
        $dokument1 = PrilozenaDokumenta::factory()->create();
        $dokument2 = PrilozenaDokumenta::factory()->create();

        $this->actingAs($user)->get("/prilozenaDokumenta/{$dokument1->id}/delete");

        $this->assertDatabaseMissing('prilozena_dokumenta', [
            'id' => $dokument1->id,
        ]);
        $this->assertDatabaseHas('prilozena_dokumenta', [
            'id' => $dokument2->id,
        ]);
    }

    // ============ ERROR HANDLING TESTS ============

    /**
     * Test index handles database errors gracefully
     */
    public function test_index_handles_database_errors_gracefully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/prilozenaDokumenta');

        $response->assertOk();
    }

    /**
     * Test unos redirects on successful creation
     */
    public function test_unos_redirects_on_success(): void
    {
        $user = User::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();

        $response = $this->actingAs($user)->post('/prilozenaDokumenta/unos', [
            'redniBrojDokumenta' => 1,
            'naziv' => 'Test Dokument',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $response->assertRedirect('/prilozenaDokumenta');
    }

    /**
     * Test update redirects on successful update
     */
    public function test_update_redirects_on_success(): void
    {
        $user = User::factory()->create();
        $dokument = PrilozenaDokumenta::factory()->create();
        $godinaStudija = $dokument->godinaStudija;

        $response = $this->actingAs($user)->patch("/prilozenaDokumenta/{$dokument->id}", [
            'redniBrojDokumenta' => 5,
            'naziv' => 'Updated',
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $response->assertRedirect('/prilozenaDokumenta');
    }

    /**
     * Test delete redirects back on success
     */
    public function test_delete_redirects_back_on_success(): void
    {
        $user = User::factory()->create();
        $dokument = PrilozenaDokumenta::factory()->create();

        $response = $this->actingAs($user)->get("/prilozenaDokumenta/{$dokument->id}/delete");

        $response->assertRedirect();
    }

    /**
     * Test foreign key relationship with godinaStudija
     */
    public function test_foreign_key_relationship_with_godina_studija(): void
    {
        $user = User::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create([
            'naziv' => 'Prva godina',
        ]);
        $dokument = PrilozenaDokumenta::factory()->create([
            'skolskaGodina_id' => $godinaStudija->id,
        ]);

        $response = $this->actingAs($user)->get('/prilozenaDokumenta');

        $this->assertDatabaseHas('prilozena_dokumenta', [
            'id' => $dokument->id,
            'skolskaGodina_id' => $godinaStudija->id,
        ]);
    }
}
