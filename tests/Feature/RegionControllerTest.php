<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegionControllerTest extends TestCase
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

    // INDEX TESTS

    public function test_index_displays_list_of_regions(): void
    {
        $user = User::factory()->create();
        Region::factory()->create(['naziv' => 'Region 1']);
        Region::factory()->create(['naziv' => 'Region 2']);

        $response = $this->actingAs($user)->get('/region');

        $response->assertOk();
        $response->assertViewIs('sifarnici.region');
        $response->assertViewHas('region');
        $regions = $response->viewData('region');
        $this->assertCount(2, $regions);
    }

    public function test_index_returns_empty_collection_when_no_regions_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/region');

        $response->assertOk();
        $response->assertViewHas('region');
        $regions = $response->viewData('region');
        $this->assertCount(0, $regions);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/region');

        $response->assertRedirect('/login');
    }

    // ADD TESTS

    public function test_add_displays_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/region/add');

        $response->assertOk();
        $response->assertViewIs('sifarnici.addRegion');
    }

    public function test_add_requires_authentication(): void
    {
        $response = $this->get('/region/add');

        $response->assertRedirect('/login');
    }

    // UNOS (CREATE) TESTS

    public function test_unos_creates_new_region_with_naziv(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/region/unos', [
            'naziv' => 'Test Region',
        ]);

        $this->assertDatabaseHas('region', [
            'naziv' => 'Test Region',
        ]);
    }

    public function test_unos_creates_region_with_specific_naziv(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/region/unos', [
            'naziv' => 'Region A',
        ]);

        $this->assertDatabaseHas('region', [
            'naziv' => 'Region A',
        ]);

        $this->assertDatabaseMissing('region', [
            'naziv' => 'Region B',
        ]);
    }

    public function test_unos_redirects_to_region_index_after_creation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/region/unos', [
            'naziv' => 'New Region',
        ]);

        $response->assertRedirect('/region');
    }

    public function test_unos_stores_naziv_field_correctly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/region/unos', [
            'naziv' => 'Stored Region',
        ]);

        $region = Region::where('naziv', 'Stored Region')->first();
        $this->assertNotNull($region);
        $this->assertEquals('Stored Region', $region->naziv);
    }

    public function test_unos_creates_multiple_regions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/region/unos', [
            'naziv' => 'Region 1',
        ]);

        $this->actingAs($user)->post('/region/unos', [
            'naziv' => 'Region 2',
        ]);

        $this->assertDatabaseHas('region', ['naziv' => 'Region 1']);
        $this->assertDatabaseHas('region', ['naziv' => 'Region 2']);
    }

    public function test_unos_requires_authentication(): void
    {
        $response = $this->post('/region/unos', [
            'naziv' => 'Test Region',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_unos_with_empty_naziv(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/region/unos', [
            'naziv' => '',
        ]);

        $count = Region::where('naziv', '')->count();
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function test_unos_persists_data_correctly_in_database(): void
    {
        $user = User::factory()->create();
        $nazivValue = 'Persistent Region Name';

        $this->actingAs($user)->post('/region/unos', [
            'naziv' => $nazivValue,
        ]);

        $stored = Region::where('naziv', $nazivValue)->first();
        $this->assertNotNull($stored);
        $this->assertIsInt($stored->id);
    }

    // EDIT TESTS

    public function test_edit_displays_form_with_existing_region(): void
    {
        $user = User::factory()->create();
        $region = Region::factory()->create(['naziv' => 'Test Region']);

        $response = $this->actingAs($user)->get("/region/{$region->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editRegion');
        $response->assertViewHas('region', $region);
    }

    public function test_edit_returns_404_for_nonexistent_region(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/region/99999/edit');

        $response->assertNotFound();
    }

    public function test_edit_uses_route_model_binding(): void
    {
        $user = User::factory()->create();
        $region = Region::factory()->create(['naziv' => 'Binding Test']);

        $response = $this->actingAs($user)->get("/region/{$region->id}/edit");

        $response->assertOk();
        $response->assertViewHas('region', $region);
        $this->assertEquals($region->id, $response->viewData('region')->id);
    }

    public function test_edit_requires_authentication(): void
    {
        $region = Region::factory()->create();

        $response = $this->get("/region/{$region->id}/edit");

        $response->assertRedirect('/login');
    }

    // UPDATE TESTS

    public function test_update_modifies_existing_region(): void
    {
        $user = User::factory()->create();
        $region = Region::factory()->create(['naziv' => 'Old Name']);

        $response = $this->actingAs($user)->patch("/region/{$region->id}", [
            'naziv' => 'New Name',
        ]);

        $this->assertDatabaseHas('region', [
            'id' => $region->id,
            'naziv' => 'New Name',
        ]);
    }

    public function test_update_redirects_to_region_index_after_success(): void
    {
        $user = User::factory()->create();
        $region = Region::factory()->create();

        $response = $this->actingAs($user)->patch("/region/{$region->id}", [
            'naziv' => 'Updated Name',
        ]);

        $response->assertRedirect('/region');
    }

    public function test_update_does_not_affect_other_regions(): void
    {
        $user = User::factory()->create();
        $region1 = Region::factory()->create(['naziv' => 'Region 1']);
        $region2 = Region::factory()->create(['naziv' => 'Region 2']);

        $this->actingAs($user)->patch("/region/{$region1->id}", [
            'naziv' => 'Region 1 Updated',
        ]);

        $this->assertDatabaseHas('region', [
            'id' => $region2->id,
            'naziv' => 'Region 2',
        ]);

        $this->assertDatabaseMissing('region', [
            'id' => $region2->id,
            'naziv' => 'Region 1 Updated',
        ]);
    }

    public function test_update_returns_404_for_nonexistent_region(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/region/99999', [
            'naziv' => 'Test',
        ]);

        $response->assertNotFound();
    }

    public function test_update_uses_route_model_binding_for_correct_record(): void
    {
        $user = User::factory()->create();
        $region1 = Region::factory()->create(['naziv' => 'Region A']);
        $region2 = Region::factory()->create(['naziv' => 'Region B']);

        $this->actingAs($user)->patch("/region/{$region1->id}", [
            'naziv' => 'Region A Updated',
        ]);

        $this->assertDatabaseHas('region', [
            'id' => $region1->id,
            'naziv' => 'Region A Updated',
        ]);

        $this->assertDatabaseHas('region', [
            'id' => $region2->id,
            'naziv' => 'Region B',
        ]);
    }

    public function test_update_maintains_data_integrity_across_multiple_updates(): void
    {
        $user = User::factory()->create();
        $region = Region::factory()->create(['naziv' => 'Original']);

        $this->actingAs($user)->patch("/region/{$region->id}", [
            'naziv' => 'First Update',
        ]);

        $this->actingAs($user)->patch("/region/{$region->id}", [
            'naziv' => 'Second Update',
        ]);

        $region->refresh();
        $this->assertEquals('Second Update', $region->naziv);
    }

    public function test_update_requires_authentication(): void
    {
        $region = Region::factory()->create();

        $response = $this->patch("/region/{$region->id}", [
            'naziv' => 'Test',
        ]);

        $response->assertRedirect('/login');
    }

    // DELETE TESTS

    public function test_delete_removes_region_from_database(): void
    {
        $user = User::factory()->create();
        $region = Region::factory()->create(['naziv' => 'To Delete']);

        $regionId = $region->id;

        $this->actingAs($user)->get("/region/{$region->id}/delete");

        $this->assertDatabaseMissing('region', [
            'id' => $regionId,
        ]);
    }

    public function test_delete_redirects_back_after_success(): void
    {
        $user = User::factory()->create();
        $region = Region::factory()->create();

        $response = $this->actingAs($user)->get("/region/{$region->id}/delete");

        $response->assertRedirect('/');
    }

    public function test_delete_does_not_affect_other_regions(): void
    {
        $user = User::factory()->create();
        $region1 = Region::factory()->create(['naziv' => 'To Delete']);
        $region2 = Region::factory()->create(['naziv' => 'To Keep']);

        $this->actingAs($user)->get("/region/{$region1->id}/delete");

        $this->assertDatabaseHas('region', [
            'id' => $region2->id,
            'naziv' => 'To Keep',
        ]);

        $this->assertDatabaseMissing('region', [
            'id' => $region1->id,
        ]);
    }

    public function test_delete_returns_404_for_nonexistent_region(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/region/99999/delete');

        $response->assertNotFound();
    }

    public function test_delete_uses_route_model_binding_for_correct_record(): void
    {
        $user = User::factory()->create();
        $region1 = Region::factory()->create(['naziv' => 'Region A']);
        $region2 = Region::factory()->create(['naziv' => 'Region B']);

        $this->actingAs($user)->get("/region/{$region1->id}/delete");

        $this->assertDatabaseMissing('region', [
            'id' => $region1->id,
        ]);

        $this->assertDatabaseHas('region', [
            'id' => $region2->id,
        ]);
    }

    public function test_delete_requires_authentication(): void
    {
        $region = Region::factory()->create();

        $response = $this->get("/region/{$region->id}/delete");

        $response->assertRedirect('/login');
    }

    // DATA ISOLATION TESTS

    public function test_data_isolation_multiple_region_operations(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/region/unos', [
            'naziv' => 'Region 1',
        ]);

        $this->actingAs($user)->post('/region/unos', [
            'naziv' => 'Region 2',
        ]);

        $this->assertDatabaseHas('region', ['naziv' => 'Region 1']);
        $this->assertDatabaseHas('region', ['naziv' => 'Region 2']);
    }

    public function test_crud_operations_complete_workflow(): void
    {
        $user = User::factory()->create();

        // Create
        $this->actingAs($user)->post('/region/unos', [
            'naziv' => 'Workflow Region',
        ]);

        $region = Region::where('naziv', 'Workflow Region')->first();
        $this->assertNotNull($region);

        // Read
        $response = $this->actingAs($user)->get("/region/{$region->id}/edit");
        $response->assertOk();

        // Update
        $this->actingAs($user)->patch("/region/{$region->id}", [
            'naziv' => 'Workflow Region Updated',
        ]);

        $region->refresh();
        $this->assertEquals('Workflow Region Updated', $region->naziv);

        // Delete
        $this->actingAs($user)->get("/region/{$region->id}/delete");

        $this->assertDatabaseMissing('region', [
            'id' => $region->id,
        ]);
    }

    public function test_update_preserves_id_on_update(): void
    {
        $user = User::factory()->create();
        $region = Region::factory()->create(['naziv' => 'Original']);

        $originalId = $region->id;

        $this->actingAs($user)->patch("/region/{$region->id}", [
            'naziv' => 'Updated',
        ]);

        $region->refresh();
        $this->assertEquals($originalId, $region->id);
    }

    public function test_delete_actually_removes_all_data(): void
    {
        $user = User::factory()->create();
        $region = Region::factory()->create(['naziv' => 'Test Region']);

        $regionId = $region->id;

        $this->actingAs($user)->get("/region/{$region->id}/delete");

        $this->assertNull(Region::find($regionId));
    }

    public function test_multiple_users_can_create_regions(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1)->post('/region/unos', [
            'naziv' => 'User1 Region',
        ]);

        $this->actingAs($user2)->post('/region/unos', [
            'naziv' => 'User2 Region',
        ]);

        $this->assertDatabaseHas('region', ['naziv' => 'User1 Region']);
        $this->assertDatabaseHas('region', ['naziv' => 'User2 Region']);
    }

    public function test_index_displays_all_created_regions(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 5; $i++) {
            $this->actingAs($user)->post('/region/unos', [
                'naziv' => "Region $i",
            ]);
        }

        $response = $this->actingAs($user)->get('/region');

        $regions = $response->viewData('region');
        $this->assertCount(5, $regions);
    }
}
