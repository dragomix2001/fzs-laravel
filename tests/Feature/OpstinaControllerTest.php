<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Opstina;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OpstinaControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Region $region;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'user_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->region = Region::create([
            'naziv' => 'Test Region',
        ]);

        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // ===== INDEX TESTS =====

    public function test_index_returns_correct_view(): void
    {
        $response = $this->get('/opstina');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.opstina');
    }

    public function test_index_returns_view_with_regions(): void
    {
        $region1 = Region::create(['naziv' => 'Region 1']);
        $region2 = Region::create(['naziv' => 'Region 2']);

        $response = $this->get('/opstina');

        $response->assertStatus(200);
        $response->assertViewHas('region', function ($collection) use ($region1, $region2) {
            return $collection->contains('id', $region1->id) && $collection->contains('id', $region2->id);
        });
    }

    public function test_index_returns_empty_collections_when_no_records_exist(): void
    {
        Opstina::query()->delete();
        Region::query()->delete();

        $response = $this->get('/opstina');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.opstina');
        $response->assertViewHas('opstina', function ($collection) {
            return $collection->count() === 0;
        });
        $response->assertViewHas('region', function ($collection) {
            return $collection->count() === 0;
        });
    }

    public function test_index_is_protected_by_auth_middleware(): void
    {
        Auth::logout();

        $response = $this->get('/opstina');

        $response->assertRedirect('/login');
    }

    // ===== ADD TESTS =====

    public function test_add_returns_create_form_view(): void
    {
        $response = $this->get('/opstina/add');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.addOpstina');
    }

    public function test_add_returns_view_with_regions(): void
    {
        $region1 = Region::create(['naziv' => 'Region 1']);
        $region2 = Region::create(['naziv' => 'Region 2']);

        $response = $this->get('/opstina/add');

        $response->assertStatus(200);
        $response->assertViewHas('region', function ($collection) use ($region1, $region2) {
            return $collection->contains('id', $region1->id) && $collection->contains('id', $region2->id);
        });
    }

    public function test_add_is_protected_by_auth_middleware(): void
    {
        Auth::logout();

        $response = $this->get('/opstina/add');

        $response->assertRedirect('/login');
    }

    // ===== UNOS TESTS =====

    public function test_unos_creates_new_record_with_valid_region(): void
    {
        $response = $this->post('/opstina/unos', [
            'naziv' => 'Kragujevac',
            'region_id' => $this->region->id,
        ]);

        $response->assertRedirect('/opstina');
        $this->assertDatabaseHas('opstina', [
            'naziv' => 'Kragujevac',
            'region_id' => $this->region->id,
        ]);
    }

    public function test_unos_creates_record_and_redirects_to_index(): void
    {
        $response = $this->post('/opstina/unos', [
            'naziv' => 'Nis',
            'region_id' => $this->region->id,
        ]);

        $response->assertRedirect('/opstina');
    }

    public function test_unos_persists_naziv_field(): void
    {
        $this->post('/opstina/unos', [
            'naziv' => 'Subotica',
            'region_id' => $this->region->id,
        ]);

        $this->assertDatabaseHas('opstina', [
            'naziv' => 'Subotica',
        ]);
    }

    public function test_unos_persists_region_id_field(): void
    {
        $region = Region::create(['naziv' => 'Another Region']);

        $this->post('/opstina/unos', [
            'naziv' => 'Vranje',
            'region_id' => $region->id,
        ]);

        $this->assertDatabaseHas('opstina', [
            'naziv' => 'Vranje',
            'region_id' => $region->id,
        ]);
    }

    public function test_unos_is_protected_by_auth_middleware(): void
    {
        Auth::logout();

        $response = $this->post('/opstina/unos', [
            'naziv' => 'Vranje',
            'region_id' => $this->region->id,
        ]);

        $response->assertRedirect('/login');
    }

    // ===== EDIT TESTS =====

    public function test_edit_returns_form_with_existing_record(): void
    {
        $opstina = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $this->region->id,
        ]);

        $response = $this->get('/opstina/'.$opstina->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.editOpstina');
        $response->assertViewHas('opstina', function (Opstina $record) use ($opstina) {
            return $record->is($opstina);
        });
    }

    public function test_edit_returns_view_with_regions(): void
    {
        $opstina = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $this->region->id,
        ]);

        $region1 = Region::create(['naziv' => 'Region 1']);
        $region2 = Region::create(['naziv' => 'Region 2']);

        $response = $this->get('/opstina/'.$opstina->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewHas('region', function ($collection) use ($region1, $region2) {
            return $collection->contains('id', $region1->id) && $collection->contains('id', $region2->id);
        });
    }

    public function test_edit_is_protected_by_auth_middleware(): void
    {
        $opstina = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $this->region->id,
        ]);

        Auth::logout();

        $response = $this->get('/opstina/'.$opstina->id.'/edit');

        $response->assertRedirect('/login');
    }

    // ===== UPDATE TESTS =====

    public function test_update_modifies_record_successfully(): void
    {
        $opstina = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $this->region->id,
        ]);

        $response = $this->patch('/opstina/'.$opstina->id, [
            'naziv' => 'Beograd - Izmenjeno',
            'region_id' => $this->region->id,
        ]);

        $response->assertRedirect('/opstina');
        $this->assertDatabaseHas('opstina', [
            'id' => $opstina->id,
            'naziv' => 'Beograd - Izmenjeno',
            'region_id' => $this->region->id,
        ]);
    }

    public function test_update_changes_region_id(): void
    {
        $region1 = Region::create(['naziv' => 'Region 1']);
        $region2 = Region::create(['naziv' => 'Region 2']);

        $opstina = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $region1->id,
        ]);

        $response = $this->patch('/opstina/'.$opstina->id, [
            'naziv' => 'Beograd',
            'region_id' => $region2->id,
        ]);

        $response->assertRedirect('/opstina');
        $this->assertDatabaseHas('opstina', [
            'id' => $opstina->id,
            'region_id' => $region2->id,
        ]);
    }

    public function test_update_changes_naziv(): void
    {
        $opstina = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $this->region->id,
        ]);

        $response = $this->patch('/opstina/'.$opstina->id, [
            'naziv' => 'Novi Beograd',
            'region_id' => $this->region->id,
        ]);

        $response->assertRedirect('/opstina');
        $this->assertDatabaseHas('opstina', [
            'id' => $opstina->id,
            'naziv' => 'Novi Beograd',
        ]);
    }

    public function test_update_preserves_all_fields_when_modified(): void
    {
        $region1 = Region::create(['naziv' => 'Region 1']);
        $region2 = Region::create(['naziv' => 'Region 2']);

        $opstina = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $region1->id,
        ]);

        $response = $this->patch('/opstina/'.$opstina->id, [
            'naziv' => 'Novi Sad',
            'region_id' => $region2->id,
        ]);

        $response->assertRedirect('/opstina');
        $this->assertDatabaseHas('opstina', [
            'id' => $opstina->id,
            'naziv' => 'Novi Sad',
            'region_id' => $region2->id,
        ]);
    }

    public function test_update_is_protected_by_auth_middleware(): void
    {
        $opstina = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $this->region->id,
        ]);

        Auth::logout();

        $response = $this->patch('/opstina/'.$opstina->id, [
            'naziv' => 'Novi Sad',
            'region_id' => $this->region->id,
        ]);

        $response->assertRedirect('/login');
    }

    // ===== DELETE TESTS =====

    public function test_delete_removes_record_and_redirects_back(): void
    {
        $opstina = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $this->region->id,
        ]);

        $response = $this->get('/opstina/'.$opstina->id.'/delete');

        $response->assertRedirect();
        $this->assertDatabaseMissing('opstina', [
            'id' => $opstina->id,
        ]);
    }

    public function test_delete_removes_only_specified_record(): void
    {
        $opstina1 = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $this->region->id,
        ]);

        $opstina2 = Opstina::create([
            'naziv' => 'Novi Sad',
            'region_id' => $this->region->id,
        ]);

        $response = $this->get('/opstina/'.$opstina1->id.'/delete');

        $response->assertRedirect();
        $this->assertDatabaseMissing('opstina', [
            'id' => $opstina1->id,
        ]);
        $this->assertDatabaseHas('opstina', [
            'id' => $opstina2->id,
        ]);
    }

    public function test_delete_is_protected_by_auth_middleware(): void
    {
        $opstina = Opstina::create([
            'naziv' => 'Beograd',
            'region_id' => $this->region->id,
        ]);

        Auth::logout();

        $response = $this->get('/opstina/'.$opstina->id.'/delete');

        $response->assertRedirect('/login');
    }
}
