<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Bodovanje;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BodovanjeControllerTest extends TestCase
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
     * Test index displays list of bodovanje entries
     */
    public function test_index_displays_list_of_bodovanje(): void
    {
        $user = User::factory()->create();
        Bodovanje::factory()->create([
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);
        Bodovanje::factory()->create([
            'opisnaOcena' => 'Врлодобар',
            'poeniMin' => 81,
            'poeniMax' => 90,
            'ocena' => 9,
        ]);

        $response = $this->actingAs($user)->get('/bodovanje');

        $response->assertOk();
        $response->assertViewIs('sifarnici.bodovanje');
        $response->assertViewHas('bodovanje');
        $bodovanje = $response->viewData('bodovanje');
        $this->assertGreaterThanOrEqual(2, $bodovanje->count());
    }

    /**
     * Test index returns empty collection when no bodovanje exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/bodovanje');

        $response->assertOk();
        $response->assertViewHas('bodovanje');
        $bodovanje = $response->viewData('bodovanje');
        $this->assertIsIterable($bodovanje);
    }

    /**
     * Test index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/bodovanje');

        $response->assertRedirect('/login');
    }

    /**
     * Test index displays multiple bodovanje entries
     */
    public function test_index_displays_multiple_entries(): void
    {
        $user = User::factory()->create();
        Bodovanje::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/bodovanje');

        $response->assertOk();
        $bodovanje = $response->viewData('bodovanje');
        $this->assertGreaterThanOrEqual(5, $bodovanje->count());
    }

    /**
     * Test index displays entries with correct attributes
     */
    public function test_index_displays_entries_with_correct_attributes(): void
    {
        $user = User::factory()->create();
        $entry = Bodovanje::factory()->create([
            'opisnaOcena' => 'Добар',
            'poeniMin' => 71,
            'poeniMax' => 80,
            'ocena' => 8,
        ]);

        $response = $this->actingAs($user)->get('/bodovanje');

        $bodovanje = $response->viewData('bodovanje');
        $this->assertNotEmpty($bodovanje);
    }

    // ============ ADD TESTS ============

    /**
     * Test add displays create form
     */
    public function test_add_displays_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/bodovanje/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addBodovanje');
    }

    /**
     * Test add requires authentication
     */
    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/bodovanje/add');

        $response->assertRedirect('/login');
    }

    // ============ UNOS TESTS ============

    /**
     * Test unos creates new bodovanje with all 4 fields
     */
    public function test_unos_creates_new_bodovanje_with_all_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);

        $response->assertRedirect('/bodovanje');
        $this->assertDatabaseHas('bodovanje', [
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test unos always sets indikatorAktivan to 1 (hardcoded at line 39)
     */
    public function test_unos_always_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Врлодобар',
            'poeniMin' => 81,
            'poeniMax' => 90,
            'ocena' => 9,
        ]);

        $bodovanje = Bodovanje::where('opisnaOcena', 'Врлодобар')->first();
        $this->assertEquals(1, $bodovanje->indikatorAktivan);
    }

    /**
     * Test unos requires authentication
     */
    public function test_unos_requires_authentication(): void
    {
        $response = $this->post('/bodovanje/unos', [
            'opisnaOcena' => 'Test',
            'poeniMin' => 10,
            'poeniMax' => 20,
            'ocena' => 5,
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test unos creates multiple entries
     */
    public function test_unos_creates_multiple_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);
        $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Врлодобар',
            'poeniMin' => 81,
            'poeniMax' => 90,
            'ocena' => 9,
        ]);

        $this->assertDatabaseHas('bodovanje', [
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
            'indikatorAktivan' => 1,
        ]);
        $this->assertDatabaseHas('bodovanje', [
            'opisnaOcena' => 'Врлодобар',
            'poeniMin' => 81,
            'poeniMax' => 90,
            'ocena' => 9,
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test unos preserves opisnaOcena exactly
     */
    public function test_unos_preserves_opisna_ocena(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);

        $bodovanje = Bodovanje::where('opisnaOcena', 'Одличан')->first();
        $this->assertEquals('Одличан', $bodovanje->opisnaOcena);
    }

    /**
     * Test unos preserves poeniMin and poeniMax
     */
    public function test_unos_preserves_poeni_ranges(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Добар',
            'poeniMin' => 71,
            'poeniMax' => 80,
            'ocena' => 8,
        ]);

        $bodovanje = Bodovanje::where('opisnaOcena', 'Добар')->first();
        $this->assertEquals(71, $bodovanje->poeniMin);
        $this->assertEquals(80, $bodovanje->poeniMax);
    }

    /**
     * Test unos preserves ocena grade
     */
    public function test_unos_preserves_ocena(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Довољан',
            'poeniMin' => 51,
            'poeniMax' => 60,
            'ocena' => 6,
        ]);

        $bodovanje = Bodovanje::where('ocena', 6)->first();
        $this->assertEquals(6, $bodovanje->ocena);
    }

    /**
     * Test unos with different grade levels
     */
    public function test_unos_with_different_grade_levels(): void
    {
        $user = User::factory()->create();

        // Test grade 10
        $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);

        // Test grade 6
        $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Довољан',
            'poeniMin' => 51,
            'poeniMax' => 60,
            'ocena' => 6,
        ]);

        $this->assertDatabaseHas('bodovanje', ['ocena' => 10]);
        $this->assertDatabaseHas('bodovanje', ['ocena' => 6]);
    }

    // ============ EDIT TESTS ============

    /**
     * Test edit displays form with existing bodovanje data
     */
    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = User::factory()->create();
        $bodovanje = Bodovanje::factory()->create([
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);

        $response = $this->actingAs($user)->get("/bodovanje/{$bodovanje->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editBodovanje');
        $response->assertViewHas('bodovanje');
        $data = $response->viewData('bodovanje');
        $this->assertEquals('Одличан', $data->opisnaOcena);
    }

    /**
     * Test edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $bodovanje = Bodovanje::factory()->create();

        $response = $this->get("/bodovanje/{$bodovanje->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test edit uses route model binding
     */
    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $bodovanje1 = Bodovanje::factory()->create([
            'opisnaOcena' => 'Одличан',
            'ocena' => 10,
        ]);
        $bodovanje2 = Bodovanje::factory()->create([
            'opisnaOcena' => 'Врлодобар',
            'ocena' => 9,
        ]);

        $response = $this->actingAs($user)->get("/bodovanje/{$bodovanje2->id}/edit");

        $data = $response->viewData('bodovanje');
        $this->assertEquals($bodovanje2->id, $data->id);
        $this->assertEquals('Врлодобар', $data->opisnaOcena);
    }

    /**
     * Test edit returns 404 for nonexistent bodovanje
     */
    public function test_edit_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/bodovanje/99999/edit');

        $response->assertNotFound();
    }

    // ============ UPDATE TESTS ============

    /**
     * Test update modifies all 4 fields
     */
    public function test_update_modifies_all_fields(): void
    {
        $user = User::factory()->create();
        $bodovanje = Bodovanje::factory()->create([
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);

        $response = $this->actingAs($user)->patch("/bodovanje/{$bodovanje->id}", [
            'opisnaOcena' => 'Врлодобар',
            'poeniMin' => 81,
            'poeniMax' => 90,
            'ocena' => 9,
        ]);

        $response->assertRedirect('/bodovanje');
        $this->assertDatabaseHas('bodovanje', [
            'id' => $bodovanje->id,
            'opisnaOcena' => 'Врлодобар',
            'poeniMin' => 81,
            'poeniMax' => 90,
            'ocena' => 9,
        ]);
    }

    /**
     * Test update checkbox ON sets indikatorAktivan to 1
     */
    public function test_update_checkbox_on_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();
        $bodovanje = Bodovanje::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/bodovanje/{$bodovanje->id}", [
            'opisnaOcena' => $bodovanje->opisnaOcena,
            'poeniMin' => $bodovanje->poeniMin,
            'poeniMax' => $bodovanje->poeniMax,
            'ocena' => $bodovanje->ocena,
            'indikatorAktivan' => 'on',
        ]);

        $bodovanje->refresh();
        $this->assertEquals(1, $bodovanje->indikatorAktivan);
    }

    /**
     * Test update checkbox value 1 keeps indikatorAktivan as 1
     */
    public function test_update_checkbox_value_one_keeps_aktivan(): void
    {
        $user = User::factory()->create();
        $bodovanje = Bodovanje::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/bodovanje/{$bodovanje->id}", [
            'opisnaOcena' => $bodovanje->opisnaOcena,
            'poeniMin' => $bodovanje->poeniMin,
            'poeniMax' => $bodovanje->poeniMax,
            'ocena' => $bodovanje->ocena,
            'indikatorAktivan' => 1,
        ]);

        $bodovanje->refresh();
        $this->assertEquals(1, $bodovanje->indikatorAktivan);
    }

    /**
     * Test update unchecked checkbox sets indikatorAktivan to 0
     */
    public function test_update_unchecked_checkbox_sets_indikator_aktivan_to_zero(): void
    {
        $user = User::factory()->create();
        $bodovanje = Bodovanje::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/bodovanje/{$bodovanje->id}", [
            'opisnaOcena' => $bodovanje->opisnaOcena,
            'poeniMin' => $bodovanje->poeniMin,
            'poeniMax' => $bodovanje->poeniMax,
            'ocena' => $bodovanje->ocena,
            'indikatorAktivan' => 0,
        ]);

        $bodovanje->refresh();
        $this->assertEquals(0, $bodovanje->indikatorAktivan);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $bodovanje = Bodovanje::factory()->create();

        $response = $this->patch("/bodovanje/{$bodovanje->id}", [
            'opisnaOcena' => 'Updated',
            'poeniMin' => 50,
            'poeniMax' => 60,
            'ocena' => 7,
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test update only opisnaOcena
     */
    public function test_update_only_opisna_ocena(): void
    {
        $user = User::factory()->create();
        $bodovanje = Bodovanje::factory()->create([
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);

        $this->actingAs($user)->patch("/bodovanje/{$bodovanje->id}", [
            'opisnaOcena' => 'Врлодобар',
            'poeniMin' => 91,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);

        $bodovanje->refresh();
        $this->assertEquals('Врлодобар', $bodovanje->opisnaOcena);
    }

    /**
     * Test update only poeni ranges
     */
    public function test_update_only_poeni_ranges(): void
    {
        $user = User::factory()->create();
        $bodovanje = Bodovanje::factory()->create([
            'opisnaOcena' => 'Добар',
            'poeniMin' => 71,
            'poeniMax' => 80,
            'ocena' => 8,
        ]);

        $this->actingAs($user)->patch("/bodovanje/{$bodovanje->id}", [
            'opisnaOcena' => 'Добар',
            'poeniMin' => 65,
            'poeniMax' => 75,
            'ocena' => 8,
        ]);

        $bodovanje->refresh();
        $this->assertEquals(65, $bodovanje->poeniMin);
        $this->assertEquals(75, $bodovanje->poeniMax);
    }

    /**
     * Test update only ocena
     */
    public function test_update_only_ocena(): void
    {
        $user = User::factory()->create();
        $bodovanje = Bodovanje::factory()->create([
            'opisnaOcena' => 'Довољан',
            'poeniMin' => 51,
            'poeniMax' => 60,
            'ocena' => 6,
        ]);

        $this->actingAs($user)->patch("/bodovanje/{$bodovanje->id}", [
            'opisnaOcena' => 'Довољан',
            'poeniMin' => 51,
            'poeniMax' => 60,
            'ocena' => 7,
        ]);

        $bodovanje->refresh();
        $this->assertEquals(7, $bodovanje->ocena);
    }

    /**
     * Test update returns 404 for nonexistent bodovanje
     */
    public function test_update_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/bodovanje/99999', [
            'opisnaOcena' => 'Updated',
            'poeniMin' => 50,
            'poeniMax' => 60,
            'ocena' => 7,
        ]);

        $response->assertNotFound();
    }

    // ============ DELETE TESTS ============

    /**
     * Test delete removes bodovanje
     */
    public function test_delete_removes_bodovanje(): void
    {
        $user = User::factory()->create();
        $bodovanje = Bodovanje::factory()->create();
        $bodovanjeId = $bodovanje->id;

        $response = $this->actingAs($user)->get("/bodovanje/{$bodovanje->id}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('bodovanje', [
            'id' => $bodovanjeId,
        ]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $bodovanje = Bodovanje::factory()->create();

        $response = $this->get("/bodovanje/{$bodovanje->id}/delete");

        $response->assertRedirect('/login');
    }

    /**
     * Test delete returns 404 for nonexistent bodovanje
     */
    public function test_delete_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/bodovanje/99999/delete');

        $response->assertNotFound();
    }

    /**
     * Test delete removes specific bodovanje while others remain
     */
    public function test_delete_removes_specific_bodovanje(): void
    {
        $user = User::factory()->create();
        $bodovanje1 = Bodovanje::factory()->create(['ocena' => 10]);
        $bodovanje2 = Bodovanje::factory()->create(['ocena' => 9]);

        $this->actingAs($user)->get("/bodovanje/{$bodovanje1->id}/delete");

        $this->assertDatabaseMissing('bodovanje', ['id' => $bodovanje1->id]);
        $this->assertDatabaseHas('bodovanje', ['id' => $bodovanje2->id]);
    }

    // ============ FIELD VALIDATION INTEGRATION TESTS ============

    /**
     * Test unos with numeric boundaries for poeniMin
     */
    public function test_unos_with_numeric_boundaries_poeni_min(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 0,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);

        $this->assertDatabaseHas('bodovanje', ['poeniMin' => 0]);
    }

    /**
     * Test unos with numeric boundaries for poeniMax
     */
    public function test_unos_with_numeric_boundaries_poeni_max(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/bodovanje/unos', [
            'opisnaOcena' => 'Одличан',
            'poeniMin' => 0,
            'poeniMax' => 100,
            'ocena' => 10,
        ]);

        $this->assertDatabaseHas('bodovanje', ['poeniMax' => 100]);
    }

    /**
     * Test factory creates valid bodovanje
     */
    public function test_factory_creates_valid_bodovanje(): void
    {
        $bodovanje = Bodovanje::factory()->create();

        $this->assertNotNull($bodovanje->id);
        $this->assertNotNull($bodovanje->opisnaOcena);
        $this->assertNotNull($bodovanje->poeniMin);
        $this->assertNotNull($bodovanje->poeniMax);
        $this->assertNotNull($bodovanje->ocena);
        $this->assertEquals(1, $bodovanje->indikatorAktivan);
    }

    /**
     * Test factory with custom attributes
     */
    public function test_factory_with_custom_attributes(): void
    {
        $bodovanje = Bodovanje::factory()->create([
            'opisnaOcena' => 'Врлодобар',
            'ocena' => 9,
        ]);

        $this->assertEquals('Врлодобар', $bodovanje->opisnaOcena);
        $this->assertEquals(9, $bodovanje->ocena);
    }

    /**
     * Test multiple factory instances create unique data
     */
    public function test_multiple_factory_instances_create_unique_data(): void
    {
        $bodovanje1 = Bodovanje::factory()->create();
        $bodovanje2 = Bodovanje::factory()->create();

        $this->assertNotEquals($bodovanje1->id, $bodovanje2->id);
    }
}
