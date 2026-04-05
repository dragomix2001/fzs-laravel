<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Semestar;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SemestarControllerTest extends TestCase
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
     * Test index displays list of semestri
     */
    public function test_index_displays_list_of_semestri(): void
    {
        $user = User::factory()->create();
        Semestar::factory()->create(['naziv' => 'Prvi semestar']);
        Semestar::factory()->create(['naziv' => 'Drugi semestar']);

        $response = $this->actingAs($user)->get('/semestar');

        $response->assertOk();
        $response->assertViewIs('sifarnici.semestar');
        $response->assertViewHas('semestar');
        $semestar = $response->viewData('semestar');
        $this->assertCount(2, $semestar);
    }

    /**
     * Test index returns empty collection when no semestri exist
     */
    public function test_index_returns_empty_collection_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/semestar');

        $response->assertOk();
        $response->assertViewHas('semestar');
        $semestar = $response->viewData('semestar');
        $this->assertCount(0, $semestar);
    }

    /**
     * Test index requires authentication
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/semestar');

        $response->assertRedirect('/login');
    }

    /**
     * Test add displays create form
     */
    public function test_add_displays_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/semestar/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addSemestar');
    }

    /**
     * Test add requires authentication
     */
    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/semestar/add');

        $response->assertRedirect('/login');
    }

    /**
     * Test unos creates new semestar with indikatorAktivan = 1
     */
    public function test_unos_creates_new_semestar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/semestar/unos', [
            'naziv' => 'Treći semestar',
            'nazivRimski' => 'III',
            'nazivBrojcano' => '3',
        ]);

        $response->assertRedirect('/semestar');
        $this->assertDatabaseHas('semestar', [
            'naziv' => 'Treći semestar',
            'indikatorAktivan' => 1,
        ]);
    }

    /**
     * Test unos always sets indikatorAktivan to 1 (hardcoded)
     */
    public function test_unos_always_sets_indikator_aktivan_to_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/semestar/unos', [
            'naziv' => 'Testni semestar',
            'nazivRimski' => 'X',
            'nazivBrojcano' => '10',
        ]);

        $semestar = Semestar::where('naziv', 'Testni semestar')->first();
        $this->assertEquals(1, $semestar->indikatorAktivan);
    }

    /**
     * Test unos requires authentication
     */
    public function test_unos_requires_authentication(): void
    {
        $response = $this->post('/semestar/unos', [
            'naziv' => 'Novi semestar',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test unos creates multiple entries
     */
    public function test_unos_creates_multiple_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/semestar/unos', ['naziv' => 'Semestar 1', 'nazivRimski' => 'I', 'nazivBrojcano' => '1']);
        $this->actingAs($user)->post('/semestar/unos', ['naziv' => 'Semestar 2', 'nazivRimski' => 'II', 'nazivBrojcano' => '2']);

        $this->assertDatabaseHas('semestar', ['naziv' => 'Semestar 1', 'indikatorAktivan' => 1]);
        $this->assertDatabaseHas('semestar', ['naziv' => 'Semestar 2', 'indikatorAktivan' => 1]);
    }

    /**
     * Test edit displays form with existing semestar data
     */
    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = User::factory()->create();
        $semestar = Semestar::factory()->create(['naziv' => 'Prvi semestar']);

        $response = $this->actingAs($user)->get("/semestar/{$semestar->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editSemestar');
        $response->assertViewHas('semestar');
        $data = $response->viewData('semestar');
        $this->assertEquals('Prvi semestar', $data->naziv);
    }

    /**
     * Test edit requires authentication
     */
    public function test_edit_requires_authentication(): void
    {
        $semestar = Semestar::factory()->create();

        $response = $this->get("/semestar/{$semestar->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test edit returns correct semestar data via route model binding
     */
    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $semestar1 = Semestar::factory()->create(['naziv' => 'Semestar 1']);
        $semestar2 = Semestar::factory()->create(['naziv' => 'Semestar 2']);

        $response = $this->actingAs($user)->get("/semestar/{$semestar2->id}/edit");

        $data = $response->viewData('semestar');
        $this->assertEquals($semestar2->id, $data->id);
        $this->assertEquals('Semestar 2', $data->naziv);
    }

    /**
     * Test update modifies existing semestar
     */
    public function test_update_modifies_semestar(): void
    {
        $user = User::factory()->create();
        $semestar = Semestar::factory()->create(['naziv' => 'Original', 'indikatorAktivan' => 1]);

        $response = $this->actingAs($user)->patch("/semestar/{$semestar->id}", [
            'naziv' => 'Updated',
            'nazivRimski' => 'IV',
            'nazivBrojcano' => '4',
            'indikatorAktivan' => 'on',
        ]);

        $response->assertRedirect('/semestar');
        $this->assertDatabaseHas('semestar', [
            'id' => $semestar->id,
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
        $semestar = Semestar::factory()->create(['indikatorAktivan' => 0]);

        $this->actingAs($user)->patch("/semestar/{$semestar->id}", [
            'naziv' => 'Test',
            'nazivRimski' => 'I',
            'nazivBrojcano' => '1',
            'indikatorAktivan' => 'on',
        ]);

        $semestar->refresh();
        $this->assertEquals(1, $semestar->indikatorAktivan);
    }

    /**
     * Test update sets indikatorAktivan to 0 when checkbox is missing
     */
    public function test_update_checkbox_missing_sets_indikator_to_zero(): void
    {
        $user = User::factory()->create();
        $semestar = Semestar::factory()->create(['indikatorAktivan' => 1]);

        $this->actingAs($user)->patch("/semestar/{$semestar->id}", [
            'naziv' => 'Test',
            'nazivRimski' => 'I',
            'nazivBrojcano' => '1',
        ]);

        $semestar->refresh();
        $this->assertEquals(0, $semestar->indikatorAktivan);
    }

    /**
     * Test update requires authentication
     */
    public function test_update_requires_authentication(): void
    {
        $semestar = Semestar::factory()->create();

        $response = $this->patch("/semestar/{$semestar->id}", [
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
        $semestar = Semestar::factory()->create([
            'naziv' => 'Prvi semestar',
            'indikatorAktivan' => 1,
        ]);

        $this->actingAs($user)->patch("/semestar/{$semestar->id}", [
            'naziv' => 'Prvi semestar',
            'nazivRimski' => 'I',
            'nazivBrojcano' => '1',
            // Checkbox not sent = inactive
        ]);

        $semestar->refresh();
        $this->assertEquals(0, $semestar->indikatorAktivan);
    }

    /**
     * Test update with checkbox logic transitions from inactive to active
     */
    public function test_update_transitions_from_inactive_to_active(): void
    {
        $user = User::factory()->create();
        $semestar = Semestar::factory()->create([
            'naziv' => 'Prvi semestar',
            'indikatorAktivan' => 0,
        ]);

        $this->actingAs($user)->patch("/semestar/{$semestar->id}", [
            'naziv' => 'Prvi semestar',
            'nazivRimski' => 'I',
            'nazivBrojcano' => '1',
            'indikatorAktivan' => 'on',
        ]);

        $semestar->refresh();
        $this->assertEquals(1, $semestar->indikatorAktivan);
    }

    /**
     * Test delete removes semestar
     */
    public function test_delete_removes_semestar(): void
    {
        $user = User::factory()->create();
        $semestar = Semestar::factory()->create(['naziv' => 'To Delete']);

        $response = $this->actingAs($user)->get("/semestar/{$semestar->id}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('semestar', ['id' => $semestar->id]);
    }

    /**
     * Test delete requires authentication
     */
    public function test_delete_requires_authentication(): void
    {
        $semestar = Semestar::factory()->create();

        $response = $this->get("/semestar/{$semestar->id}/delete");

        $response->assertRedirect('/login');
    }

    /**
     * Test delete with nonexistent ID returns 404
     */
    public function test_delete_with_nonexistent_id_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/semestar/9999/delete');

        $response->assertStatus(404);
    }

    /**
     * Test update with nonexistent ID returns 404
     */
    public function test_update_with_nonexistent_id_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/semestar/9999', [
            'naziv' => 'Test',
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test update only modifies specified record
     */
    public function test_update_only_modifies_specified_record(): void
    {
        $user = User::factory()->create();
        $semestar1 = Semestar::factory()->create(['naziv' => 'Original 1']);
        $semestar2 = Semestar::factory()->create(['naziv' => 'Original 2']);

        $this->actingAs($user)->patch("/semestar/{$semestar1->id}", [
            'naziv' => 'Updated',
            'nazivRimski' => 'I',
            'nazivBrojcano' => '1',
        ]);

        $this->assertDatabaseHas('semestar', [
            'id' => $semestar1->id,
            'naziv' => 'Updated',
        ]);
        $this->assertDatabaseHas('semestar', [
            'id' => $semestar2->id,
            'naziv' => 'Original 2',
        ]);
    }
}
