<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Mesto;
use App\Models\Opstina;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MestoControllerTest extends TestCase
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

    public function test_index_displays_list_of_mesta(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        Mesto::factory()->create(['naziv' => 'Crveni Krst', 'opstina_id' => $opstina->id]);
        Mesto::factory()->create(['naziv' => 'Banovo Brdo', 'opstina_id' => $opstina->id]);

        $response = $this->actingAs($user)->get('/mesto');

        $response->assertOk();
        $response->assertViewIs('sifarnici.mesto');
        $response->assertViewHas('mesto');
        $response->assertViewHas('opstina');
        $mesta = $response->viewData('mesto');
        $this->assertCount(2, $mesta);
    }

    public function test_index_returns_empty_collection_when_no_mesta_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/mesto');

        $response->assertOk();
        $response->assertViewHas('mesto');
        $mesta = $response->viewData('mesto');
        $this->assertCount(0, $mesta);
    }

    public function test_index_includes_all_opstine(): void
    {
        $user = User::factory()->create();
        $opstina1 = Opstina::factory()->create();
        $opstina2 = Opstina::factory()->create();
        Mesto::factory()->create(['opstina_id' => $opstina1->id]);
        Mesto::factory()->create(['opstina_id' => $opstina2->id]);

        $response = $this->actingAs($user)->get('/mesto');

        $response->assertOk();
        $opstine = $response->viewData('opstina');
        $this->assertCount(2, $opstine);
    }

    public function test_unos_creates_new_mesto_with_naziv_and_opstina_id(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();

        $response = $this->actingAs($user)->post('/mesto/unos', [
            'naziv' => 'Voždovac',
            'opstina_id' => $opstina->id,
        ]);

        $this->assertDatabaseHas('mesto', [
            'naziv' => 'Voždovac',
            'opstina_id' => $opstina->id,
        ]);
    }

    public function test_unos_creates_mesto_with_specific_opstina(): void
    {
        $user = User::factory()->create();
        $opstina1 = Opstina::factory()->create();
        $opstina2 = Opstina::factory()->create();

        $this->actingAs($user)->post('/mesto/unos', [
            'naziv' => 'Zemun',
            'opstina_id' => $opstina1->id,
        ]);

        $this->assertDatabaseHas('mesto', [
            'naziv' => 'Zemun',
            'opstina_id' => $opstina1->id,
        ]);

        $this->assertDatabaseMissing('mesto', [
            'naziv' => 'Zemun',
            'opstina_id' => $opstina2->id,
        ]);
    }

    public function test_unos_redirects_back_after_creation(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();

        $response = $this->actingAs($user)->post('/mesto/unos', [
            'naziv' => 'Kumodraž',
            'opstina_id' => $opstina->id,
        ]);

        $response->assertRedirect('/');
    }

    public function test_unos_stores_naziv_field_correctly(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();

        $this->actingAs($user)->post('/mesto/unos', [
            'naziv' => 'Terazije',
            'opstina_id' => $opstina->id,
        ]);

        $mesto = Mesto::where('naziv', 'Terazije')->first();
        $this->assertNotNull($mesto);
        $this->assertEquals('Terazije', $mesto->naziv);
    }

    public function test_unos_creates_multiple_mesta_in_same_opstina(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();

        $this->actingAs($user)->post('/mesto/unos', [
            'naziv' => 'Grad 1',
            'opstina_id' => $opstina->id,
        ]);

        $this->actingAs($user)->post('/mesto/unos', [
            'naziv' => 'Grad 2',
            'opstina_id' => $opstina->id,
        ]);

        $this->assertDatabaseHas('mesto', ['naziv' => 'Grad 1', 'opstina_id' => $opstina->id]);
        $this->assertDatabaseHas('mesto', ['naziv' => 'Grad 2', 'opstina_id' => $opstina->id]);
    }

    public function test_edit_displays_form_with_existing_data(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        $mesto = Mesto::factory()->create(['naziv' => 'Savski Venac', 'opstina_id' => $opstina->id]);

        $response = $this->actingAs($user)->get("/mesto/{$mesto->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editMesto');
        $response->assertViewHas('mesto', $mesto);
        $response->assertViewHas('opstina');
    }

    public function test_edit_passes_all_opstine_in_view(): void
    {
        $user = User::factory()->create();
        $opstina1 = Opstina::factory()->create();
        $opstina2 = Opstina::factory()->create();
        $mesto = Mesto::factory()->create(['opstina_id' => $opstina1->id]);

        $response = $this->actingAs($user)->get("/mesto/{$mesto->id}/edit");

        $response->assertOk();
        $opstine = $response->viewData('opstina');
        $this->assertCount(2, $opstine);
    }

    public function test_edit_returns_404_for_nonexistent_mesto(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/mesto/99999/edit');

        $response->assertNotFound();
    }

    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        $mesto = Mesto::factory()->create(['naziv' => 'Obilićev Venac', 'opstina_id' => $opstina->id]);

        $response = $this->actingAs($user)->get("/mesto/{$mesto->id}/edit");

        $response->assertOk();
        $response->assertViewHas('mesto', $mesto);
        $this->assertEquals($mesto->id, $response->viewData('mesto')->id);
    }

    public function test_edit_shows_correct_place_for_selected_opstina(): void
    {
        $user = User::factory()->create();
        $opstina1 = Opstina::factory()->create(['naziv' => 'Čukarica']);
        $opstina2 = Opstina::factory()->create(['naziv' => 'Voždovac']);
        $mesto = Mesto::factory()->create(['naziv' => 'Test Mesto', 'opstina_id' => $opstina1->id]);

        $response = $this->actingAs($user)->get("/mesto/{$mesto->id}/edit");

        $response->assertOk();
        $mesto = $response->viewData('mesto');
        $this->assertEquals($opstina1->id, $mesto->opstina_id);
    }

    public function test_update_updates_existing_mesto(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        $mesto = Mesto::factory()->create(['naziv' => 'Old Name', 'opstina_id' => $opstina->id]);

        $response = $this->actingAs($user)->patch("/mesto/{$mesto->id}", [
            'naziv' => 'New Name',
            'opstina_id' => $opstina->id,
        ]);

        $this->assertDatabaseHas('mesto', [
            'id' => $mesto->id,
            'naziv' => 'New Name',
            'opstina_id' => $opstina->id,
        ]);
    }

    public function test_update_changes_opstina_id(): void
    {
        $user = User::factory()->create();
        $opstina1 = Opstina::factory()->create();
        $opstina2 = Opstina::factory()->create();
        $mesto = Mesto::factory()->create(['naziv' => 'Centar', 'opstina_id' => $opstina1->id]);

        $this->actingAs($user)->patch("/mesto/{$mesto->id}", [
            'naziv' => 'Centar',
            'opstina_id' => $opstina2->id,
        ]);

        $mesto->refresh();
        $this->assertEquals($opstina2->id, $mesto->opstina_id);
    }

    public function test_update_redirects_to_mesto_index_after_success(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        $mesto = Mesto::factory()->create(['opstina_id' => $opstina->id]);

        $response = $this->actingAs($user)->patch("/mesto/{$mesto->id}", [
            'naziv' => 'Updated Name',
            'opstina_id' => $opstina->id,
        ]);

        $response->assertRedirect('/mesto');
    }

    public function test_update_does_not_affect_other_mesta(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        $mesto1 = Mesto::factory()->create(['naziv' => 'Mesto 1', 'opstina_id' => $opstina->id]);
        $mesto2 = Mesto::factory()->create(['naziv' => 'Mesto 2', 'opstina_id' => $opstina->id]);

        $this->actingAs($user)->patch("/mesto/{$mesto1->id}", [
            'naziv' => 'Mesto 1 Updated',
            'opstina_id' => $opstina->id,
        ]);

        $this->assertDatabaseHas('mesto', [
            'id' => $mesto2->id,
            'naziv' => 'Mesto 2',
        ]);

        $this->assertDatabaseMissing('mesto', [
            'id' => $mesto2->id,
            'naziv' => 'Mesto 1 Updated',
        ]);
    }

    public function test_update_returns_404_for_nonexistent_mesto(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/mesto/99999', [
            'naziv' => 'Test',
            'opstina_id' => 1,
        ]);

        $response->assertNotFound();
    }

    public function test_update_uses_route_model_binding_for_correct_record(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        $mesto1 = Mesto::factory()->create(['naziv' => 'Mesto A', 'opstina_id' => $opstina->id]);
        $mesto2 = Mesto::factory()->create(['naziv' => 'Mesto B', 'opstina_id' => $opstina->id]);

        $this->actingAs($user)->patch("/mesto/{$mesto1->id}", [
            'naziv' => 'Mesto A Updated',
            'opstina_id' => $opstina->id,
        ]);

        $this->assertDatabaseHas('mesto', [
            'id' => $mesto1->id,
            'naziv' => 'Mesto A Updated',
        ]);

        $this->assertDatabaseHas('mesto', [
            'id' => $mesto2->id,
            'naziv' => 'Mesto B',
        ]);
    }

    public function test_update_maintains_data_integrity_across_multiple_updates(): void
    {
        $user = User::factory()->create();
        $opstina1 = Opstina::factory()->create();
        $opstina2 = Opstina::factory()->create();
        $mesto = Mesto::factory()->create(['naziv' => 'Original', 'opstina_id' => $opstina1->id]);

        $this->actingAs($user)->patch("/mesto/{$mesto->id}", [
            'naziv' => 'First Update',
            'opstina_id' => $opstina1->id,
        ]);

        $this->actingAs($user)->patch("/mesto/{$mesto->id}", [
            'naziv' => 'Second Update',
            'opstina_id' => $opstina2->id,
        ]);

        $mesto->refresh();
        $this->assertEquals('Second Update', $mesto->naziv);
        $this->assertEquals($opstina2->id, $mesto->opstina_id);
    }

    public function test_delete_removes_mesto_from_database(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        $mesto = Mesto::factory()->create(['naziv' => 'To Delete', 'opstina_id' => $opstina->id]);

        $mestoId = $mesto->id;

        $this->actingAs($user)->get("/mesto/{$mesto->id}/delete");

        $this->assertDatabaseMissing('mesto', [
            'id' => $mestoId,
        ]);
    }

    public function test_delete_redirects_back_after_success(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        $mesto = Mesto::factory()->create(['opstina_id' => $opstina->id]);

        $response = $this->actingAs($user)->get("/mesto/{$mesto->id}/delete");

        $response->assertRedirect('/');
    }

    public function test_delete_does_not_affect_other_mesta(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        $mesto1 = Mesto::factory()->create(['naziv' => 'To Delete', 'opstina_id' => $opstina->id]);
        $mesto2 = Mesto::factory()->create(['naziv' => 'To Keep', 'opstina_id' => $opstina->id]);

        $this->actingAs($user)->get("/mesto/{$mesto1->id}/delete");

        $this->assertDatabaseHas('mesto', [
            'id' => $mesto2->id,
            'naziv' => 'To Keep',
        ]);

        $this->assertDatabaseMissing('mesto', [
            'id' => $mesto1->id,
        ]);
    }

    public function test_delete_returns_404_for_nonexistent_mesto(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/mesto/99999/delete');

        $response->assertNotFound();
    }

    public function test_delete_uses_route_model_binding_for_correct_record(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create();
        $mesto1 = Mesto::factory()->create(['naziv' => 'Mesto A', 'opstina_id' => $opstina->id]);
        $mesto2 = Mesto::factory()->create(['naziv' => 'Mesto B', 'opstina_id' => $opstina->id]);

        $this->actingAs($user)->get("/mesto/{$mesto1->id}/delete");

        $this->assertDatabaseMissing('mesto', [
            'id' => $mesto1->id,
        ]);

        $this->assertDatabaseHas('mesto', [
            'id' => $mesto2->id,
        ]);
    }

    public function test_data_isolation_multiple_mesta_operations(): void
    {
        $user = User::factory()->create();
        $opstina1 = Opstina::factory()->create();
        $opstina2 = Opstina::factory()->create();

        $this->actingAs($user)->post('/mesto/unos', [
            'naziv' => 'Mesto 1',
            'opstina_id' => $opstina1->id,
        ]);

        $this->actingAs($user)->post('/mesto/unos', [
            'naziv' => 'Mesto 2',
            'opstina_id' => $opstina2->id,
        ]);

        $this->assertDatabaseHas('mesto', ['naziv' => 'Mesto 1', 'opstina_id' => $opstina1->id]);
        $this->assertDatabaseHas('mesto', ['naziv' => 'Mesto 2', 'opstina_id' => $opstina2->id]);
    }

    public function test_mesto_relationship_with_opstina(): void
    {
        $user = User::factory()->create();
        $opstina = Opstina::factory()->create(['naziv' => 'Test Opstina']);
        $mesto = Mesto::factory()->create(['naziv' => 'Test Mesto', 'opstina_id' => $opstina->id]);

        $response = $this->actingAs($user)->get('/mesto');

        $response->assertOk();
        $dbMesto = Mesto::findOrFail($mesto->id);
        $this->assertEquals($opstina->id, $dbMesto->opstina_id);
        $this->assertNotNull($dbMesto->opstina());
    }
}
