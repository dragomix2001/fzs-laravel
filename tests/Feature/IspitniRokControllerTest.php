<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\IspitniRok;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IspitniRokControllerTest extends TestCase
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

    /**
     * Test index displays list of ispitni rokovi
     */
    public function test_index_displays_list_of_ispitni_rokovi(): void
    {
        $user = User::factory()->create();
        IspitniRok::factory()->create(['naziv' => 'Redovni rok']);
        IspitniRok::factory()->create(['naziv' => 'Septembarski rok']);

        $response = $this->actingAs($user)->get('/ispitniRok');

        $response->assertOk();
        $response->assertViewIs('sifarnici.ispitniRok');
        $response->assertViewHas('ispitniRok');
        $ispitniRok = $response->viewData('ispitniRok');
        $this->assertCount(2, $ispitniRok);
    }

    /**
     * Test index returns empty collection when no ispitni rokovi exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ispitniRok');

        $response->assertOk();
        $response->assertViewHas('ispitniRok');
        $ispitniRok = $response->viewData('ispitniRok');
        $this->assertCount(0, $ispitniRok);
    }

    /**
     * Test index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/ispitniRok');

        $response->assertRedirect('/login');
    }

    /**
     * Test add displays create form
     */
    public function test_add_displays_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ispitniRok/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addIspitniRok');
    }

    /**
     * Test add requires authentication
     */
    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/ispitniRok/add');

        $response->assertRedirect('/login');
    }

    /**
     * Test unos creates new ispitni rok with indikatorAktivan = 1
     */
    public function test_unos_creates_new_ispitni_rok(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/ispitniRok/unos', [
            'naziv' => 'Februarski rok',
        ]);

        $response->assertRedirect('/ispitniRok');
        $this->assertDatabaseHas('ispitni_rok', [
            'naziv' => 'Februarski rok',
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test unos always sets indikatorAktivan to 1 (hardcoded)
     */
    public function test_unos_always_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/ispitniRok/unos', [
            'naziv' => 'Testni rok',
        ]);

        $ispitniRok = IspitniRok::where('naziv', 'Testni rok')->first();
        $this->assertEquals(1, $ispitniRok->indikatorAktivan);
    }

    /**
     * Test unos requires authentication
     */
    public function test_unos_requires_authentication(): void
    {
        $response = $this->post('/ispitniRok/unos', [
            'naziv' => 'Novi rok',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test unos creates ispitni rok with multiple entries
     */
    public function test_unos_creates_multiple_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/ispitniRok/unos', ['naziv' => 'Rok 1']);
        $this->actingAs($user)->post('/ispitniRok/unos', ['naziv' => 'Rok 2']);

        $this->assertDatabaseHas('ispitni_rok', ['naziv' => 'Rok 1', 'indikatorAktivan' => 1]);
        $this->assertDatabaseHas('ispitni_rok', ['naziv' => 'Rok 2', 'indikatorAktivan' => 1]);
    }

    /**
     * Test edit displays form with existing ispitni rok data
     */
    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = User::factory()->create();
        $ispitniRok = IspitniRok::factory()->create(['naziv' => 'Redovni rok']);

        $response = $this->actingAs($user)->get("/ispitniRok/{$ispitniRok->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editIspitniRok');
        $response->assertViewHas('ispitniRok');
        $data = $response->viewData('ispitniRok');
        $this->assertEquals('Redovni rok', $data->naziv);
    }

    /**
     * Test edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $ispitniRok = IspitniRok::factory()->create();

        $response = $this->get("/ispitniRok/{$ispitniRok->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test edit returns correct ispitni rok data via route model binding
     */
    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $rok1 = IspitniRok::factory()->create(['naziv' => 'Rok 1']);
        $rok2 = IspitniRok::factory()->create(['naziv' => 'Rok 2']);

        $response = $this->actingAs($user)->get("/ispitniRok/{$rok2->id}/edit");

        $data = $response->viewData('ispitniRok');
        $this->assertEquals($rok2->id, $data->id);
        $this->assertEquals('Rok 2', $data->naziv);
    }

    /**
     * Test update modifies existing ispitni rok
     */
    public function test_update_modifies_ispitni_rok(): void
    {
        $user = User::factory()->create();
        $ispitniRok = IspitniRok::factory()->create(['naziv' => 'Original', 'indikatorAktivan' => 1]);

        $response = $this->actingAs($user)->patch("/ispitniRok/{$ispitniRok->id}", [
            'naziv' => 'Updated',
            'indikatorAktivan' => 'on',
        ]);

        $response->assertRedirect('/ispitniRok');
        $this->assertDatabaseHas('ispitni_rok', [
            'id' => $ispitniRok->id,
            'naziv' => 'Updated',
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test update sets indikatorAktivan to 1 when checkbox is 'on'
     */
    public function test_update_checkbox_on_sets_indikator_to_one(): void
    {
        $user = User::factory()->create();
        $ispitniRok = IspitniRok::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/ispitniRok/{$ispitniRok->id}", [
            'naziv' => 'Test',
            'indikatorAktivan' => 'on',
        ]);

        $ispitniRok->refresh();
        $this->assertEquals(1, $ispitniRok->indikatorAktivan);
    }

    /**
     * Test update sets indikatorAktivan to 0 when checkbox is missing
     */
    public function test_update_checkbox_missing_sets_indikator_to_zero(): void
    {
        $user = User::factory()->create();
        $ispitniRok = IspitniRok::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/ispitniRok/{$ispitniRok->id}", [
            'naziv' => 'Test',
        ]);

        $ispitniRok->refresh();
        $this->assertEquals(0, $ispitniRok->indikatorAktivan);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $ispitniRok = IspitniRok::factory()->create();

        $response = $this->patch("/ispitniRok/{$ispitniRok->id}", [
            'naziv' => 'Updated',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test update with checkbox logic transitions from active to inactive
     */
    public function test_update_transitions_from_active_to_inactive(): void
    {
        $user = User::factory()->create();
        $ispitniRok = IspitniRok::factory()->create([
            'naziv' => 'Redovni rok',
            'indikatorAktivan' => 1,
        ]);

        $this->actingAs($user)->patch("/ispitniRok/{$ispitniRok->id}", [
            'naziv' => 'Redovni rok',
            // Checkbox not sent = inactive
        ]);

        $ispitniRok->refresh();
        $this->assertEquals(0, $ispitniRok->indikatorAktivan);
    }

    /**
     * Test update with checkbox logic transitions from inactive to active
     */
    public function test_update_transitions_from_inactive_to_active(): void
    {
        $user = User::factory()->create();
        $ispitniRok = IspitniRok::factory()->create([
            'naziv' => 'Redovni rok',
            'indikatorAktivan' => 0,
        ]);

        $this->actingAs($user)->patch("/ispitniRok/{$ispitniRok->id}", [
            'naziv' => 'Redovni rok',
            'indikatorAktivan' => 'on',
        ]);

        $ispitniRok->refresh();
        $this->assertEquals(1, $ispitniRok->indikatorAktivan);
    }

    /**
     * Test delete removes ispitni rok from database
     */
    public function test_delete_removes_ispitni_rok(): void
    {
        $user = User::factory()->create();
        $ispitniRok = IspitniRok::factory()->create(['naziv' => 'Rok za brisanje']);
        $rokId = $ispitniRok->id;

        $response = $this->actingAs($user)->get("/ispitniRok/{$ispitniRok->id}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('ispitni_rok', ['id' => $rokId]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $ispitniRok = IspitniRok::factory()->create();

        $response = $this->get("/ispitniRok/{$ispitniRok->id}/delete");

        $response->assertRedirect('/login');
    }

    /**
     * Test delete only removes specified record
     */
    public function test_delete_only_removes_specified_record(): void
    {
        $user = User::factory()->create();
        $rok1 = IspitniRok::factory()->create(['naziv' => 'Rok 1']);
        $rok2 = IspitniRok::factory()->create(['naziv' => 'Rok 2']);

        $this->actingAs($user)->get("/ispitniRok/{$rok1->id}/delete");

        $this->assertDatabaseMissing('ispitni_rok', ['id' => $rok1->id]);
        $this->assertDatabaseHas('ispitni_rok', ['id' => $rok2->id]);
    }

    /**
     * Test delete returns back redirect
     */
    public function test_delete_returns_back_redirect(): void
    {
        $user = User::factory()->create();
        $ispitniRok = IspitniRok::factory()->create();

        $response = $this->actingAs($user)->get("/ispitniRok/{$ispitniRok->id}/delete");

        $response->assertRedirect();
    }

    /**
     * Test multiple successive deletes
     */
    public function test_multiple_delete_operations(): void
    {
        $user = User::factory()->create();
        $rok1 = IspitniRok::factory()->create(['naziv' => 'Rok 1']);
        $rok2 = IspitniRok::factory()->create(['naziv' => 'Rok 2']);
        $rok3 = IspitniRok::factory()->create(['naziv' => 'Rok 3']);

        $this->actingAs($user)->get("/ispitniRok/{$rok1->id}/delete");
        $this->actingAs($user)->get("/ispitniRok/{$rok2->id}/delete");

        $this->assertDatabaseMissing('ispitni_rok', ['id' => $rok1->id]);
        $this->assertDatabaseMissing('ispitni_rok', ['id' => $rok2->id]);
        $this->assertDatabaseHas('ispitni_rok', ['id' => $rok3->id]);
    }

    /**
     * Test index with database containing many records
     */
    public function test_index_displays_all_records(): void
    {
        $user = User::factory()->create();
        IspitniRok::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/ispitniRok');

        $ispitniRok = $response->viewData('ispitniRok');
        $this->assertCount(5, $ispitniRok);
    }

    /**
     * Test unos with specific field values
     */
    public function test_unos_preserves_naziv(): void
    {
        $user = User::factory()->create();
        $naziv = 'Junski ispitni rok 2026';

        $this->actingAs($user)->post('/ispitniRok/unos', [
            'naziv' => $naziv,
        ]);

        $ispitniRok = IspitniRok::where('naziv', $naziv)->first();
        $this->assertNotNull($ispitniRok);
        $this->assertEquals($naziv, $ispitniRok->naziv);
    }

    /**
     * Test edit with nonexistent ID returns 404
     */
    public function test_edit_with_nonexistent_id_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ispitniRok/9999/edit');

        $response->assertStatus(404);
    }

    /**
     * Test update with nonexistent ID returns 404
     */
    public function test_update_with_nonexistent_id_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/ispitniRok/9999', [
            'naziv' => 'Test',
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test delete with nonexistent ID returns 404
     */
    public function test_delete_with_nonexistent_id_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ispitniRok/9999/delete');

        $response->assertStatus(404);
    }

    /**
     * Test update only modifies specified record
     */
    public function test_update_only_modifies_specified_record(): void
    {
        $user = User::factory()->create();
        $rok1 = IspitniRok::factory()->create(['naziv' => 'Original 1']);
        $rok2 = IspitniRok::factory()->create(['naziv' => 'Original 2']);

        $this->actingAs($user)->patch("/ispitniRok/{$rok1->id}", [
            'naziv' => 'Updated',
        ]);

        $this->assertDatabaseHas('ispitni_rok', [
            'id' => $rok1->id,
            'naziv' => 'Updated',
        ]);
        $this->assertDatabaseHas('ispitni_rok', [
            'id' => $rok2->id,
            'naziv' => 'Original 2',
        ]);
    }
}
