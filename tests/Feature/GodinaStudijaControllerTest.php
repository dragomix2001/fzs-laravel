<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GodinaStudijaControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // ===== INDEX TESTS =====

    public function test_index_returns_view_with_all_records(): void
    {
        $godina1 = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $godina2 = GodinaStudija::create([
            'naziv' => 'Druga',
            'nazivRimski' => 'II',
            'nazivSlovimaUPadezu' => 'Druge',
            'redosledPrikazivanja' => 2,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/godinaStudija');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.godinaStudija');
        $response->assertViewHas('godinaStudija', function ($collection) use ($godina1, $godina2) {
            return $collection->contains('id', $godina1->id) && $collection->contains('id', $godina2->id);
        });
    }

    public function test_index_returns_empty_collection_when_no_records_exist(): void
    {
        GodinaStudija::query()->delete();

        $response = $this->get('/godinaStudija');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.godinaStudija');
        $response->assertViewHas('godinaStudija', function ($collection) {
            return $collection->count() === 0;
        });
    }

    public function test_index_returns_empty_view_when_no_error(): void
    {
        $response = $this->get('/godinaStudija');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.godinaStudija');
    }

    // ===== ADD TESTS =====

    public function test_add_returns_create_form_view(): void
    {
        $response = $this->get('/godinaStudija/add');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.addGodinaStudija');
    }

    // ===== UNOS TESTS =====

    public function test_unos_creates_new_record_and_redirects(): void
    {
        $response = $this->post('/godinaStudija/unos', [
            'naziv' => 'Druga',
            'nazivRimski' => 'II',
            'nazivSlovimaUPadezu' => 'Druge',
            'redosledPrikazivanja' => 2,
        ]);

        $response->assertRedirect('/godinaStudija');
        $this->assertDatabaseHas('godina_studija', [
            'naziv' => 'Druga',
            'nazivRimski' => 'II',
            'nazivSlovimaUPadezu' => 'Druge',
            'redosledPrikazivanja' => 2,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_unos_always_sets_indikator_aktivan_to_1(): void
    {
        $response = $this->post('/godinaStudija/unos', [
            'naziv' => 'Cetvrta',
            'nazivRimski' => 'IV',
            'nazivSlovimaUPadezu' => 'Cetvte',
            'redosledPrikazivanja' => 4,
        ]);

        $response->assertRedirect('/godinaStudija');
        $this->assertDatabaseHas('godina_studija', [
            'naziv' => 'Cetvrta',
            'indikatorAktivan' => 1,
        ]);
    }

    // ===== EDIT TESTS =====

    public function test_edit_returns_form_with_existing_record(): void
    {
        $godina = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/godinaStudija/'.$godina->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.editGodinaStudija');
        $response->assertViewHas('godinaStudija', function (GodinaStudija $record) use ($godina) {
            return $record->is($godina);
        });
    }

    // ===== UPDATE TESTS =====

    public function test_update_modifies_record_successfully(): void
    {
        $godina = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch('/godinaStudija/'.$godina->id, [
            'naziv' => 'Prva - Izmenjeno',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 5,
            'indikatorAktivan' => 1,
        ]);

        $response->assertRedirect('/godinaStudija');
        $this->assertDatabaseHas('godina_studija', [
            'id' => $godina->id,
            'naziv' => 'Prva - Izmenjeno',
            'redosledPrikazivanja' => 5,
        ]);
    }

    public function test_update_with_indikator_aktivan_on_saves_as_1(): void
    {
        $godina = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 0,
        ]);

        $response = $this->patch('/godinaStudija/'.$godina->id, [
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 'on',
        ]);

        $response->assertRedirect('/godinaStudija');
        $this->assertDatabaseHas('godina_studija', [
            'id' => $godina->id,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_update_with_indikator_aktivan_1_saves_as_1(): void
    {
        $godina = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 0,
        ]);

        $response = $this->patch('/godinaStudija/'.$godina->id, [
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $response->assertRedirect('/godinaStudija');
        $this->assertDatabaseHas('godina_studija', [
            'id' => $godina->id,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_update_with_indikator_aktivan_0_saves_as_0(): void
    {
        $godina = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch('/godinaStudija/'.$godina->id, [
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 0,
        ]);

        $response->assertRedirect('/godinaStudija');
        $this->assertDatabaseHas('godina_studija', [
            'id' => $godina->id,
            'indikatorAktivan' => 0,
        ]);
    }

    public function test_update_with_indikator_aktivan_null_saves_as_0(): void
    {
        $godina = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch('/godinaStudija/'.$godina->id, [
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => null,
        ]);

        $response->assertRedirect('/godinaStudija');
        $this->assertDatabaseHas('godina_studija', [
            'id' => $godina->id,
            'indikatorAktivan' => 0,
        ]);
    }

    public function test_update_preserves_all_fields_when_modified(): void
    {
        $godina = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->patch('/godinaStudija/'.$godina->id, [
            'naziv' => 'Trzeca',
            'nazivRimski' => 'III',
            'nazivSlovimaUPadezu' => 'Trece',
            'redosledPrikazivanja' => 3,
            'indikatorAktivan' => 0,
        ]);

        $response->assertRedirect('/godinaStudija');
        $this->assertDatabaseHas('godina_studija', [
            'id' => $godina->id,
            'naziv' => 'Trzeca',
            'nazivRimski' => 'III',
            'nazivSlovimaUPadezu' => 'Trece',
            'redosledPrikazivanja' => 3,
            'indikatorAktivan' => 0,
        ]);
    }

    // ===== DELETE TESTS =====

    public function test_delete_removes_record_and_redirects_back(): void
    {
        $godina = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/godinaStudija/'.$godina->id.'/delete');

        $response->assertRedirect();
        $this->assertDatabaseMissing('godina_studija', [
            'id' => $godina->id,
        ]);
    }

    public function test_delete_removes_only_specified_record(): void
    {
        $godina1 = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $godina2 = GodinaStudija::create([
            'naziv' => 'Druga',
            'nazivRimski' => 'II',
            'nazivSlovimaUPadezu' => 'Druge',
            'redosledPrikazivanja' => 2,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/godinaStudija/'.$godina1->id.'/delete');

        $response->assertRedirect();
        $this->assertDatabaseMissing('godina_studija', [
            'id' => $godina1->id,
        ]);
        $this->assertDatabaseHas('godina_studija', [
            'id' => $godina2->id,
        ]);
    }
}
