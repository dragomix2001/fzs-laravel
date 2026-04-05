<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AktivniIspitniRokovi;
use App\Models\IspitniRok;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KalendarControllerTest extends TestCase
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

    public function test_index_displays_calendar_view(): void
    {
        $response = $this->get('/kalendar/');

        $response->assertOk();
        $response->assertViewIs('kalendar.kalendar');
    }

    public function test_index_is_publicly_accessible(): void
    {
        $response = $this->get('/kalendar/');

        $this->assertNotEquals(401, $response->status());
        $this->assertNotEquals(403, $response->status());
    }

    public function test_create_rok_displays_create_form(): void
    {
        IspitniRok::factory()->create([
            'naziv' => 'Redovni rok',
        ]);

        $response = $this->get('/kalendar/createRok/');

        $response->assertOk();
        $response->assertViewIs('kalendar.create_rok');
        $response->assertViewHas('ispitniRok');
    }

    public function test_create_rok_loads_all_exam_types(): void
    {
        IspitniRok::factory()->create(['naziv' => 'Tip 1']);
        IspitniRok::factory()->create(['naziv' => 'Tip 2']);

        $response = $this->get('/kalendar/createRok/');

        $response->assertOk();
        $ispitniRok = $response->viewData('ispitniRok');
        $this->assertCount(2, $ispitniRok);
    }

    public function test_create_rok_works_with_no_exam_types(): void
    {
        $response = $this->get('/kalendar/createRok/');

        $response->assertOk();
        $response->assertViewIs('kalendar.create_rok');
        $ispitniRok = $response->viewData('ispitniRok');
        $this->assertCount(0, $ispitniRok);
    }

    public function test_edit_rok_displays_edit_form_with_existing_data(): void
    {
        $rok = AktivniIspitniRokovi::factory()->create([
            'naziv' => 'Januarski ispitni rok',
            'pocetak' => '2026-01-15',
            'kraj' => '2026-01-29',
        ]);

        IspitniRok::factory()->create(['naziv' => 'Redovni rok']);

        $response = $this->get("/kalendar/editRok/{$rok->id}");

        $response->assertOk();
        $response->assertViewIs('kalendar.edit_rok');
        $response->assertViewHas('rok');
        $response->assertViewHas('ispitniRok');
    }

    public function test_edit_rok_returns_correct_exam_period_data(): void
    {
        $rok = AktivniIspitniRokovi::factory()->create([
            'naziv' => 'Februarski rok',
            'pocetak' => '2026-02-15',
        ]);

        IspitniRok::factory()->create();

        $response = $this->get("/kalendar/editRok/{$rok->id}");

        $response->assertOk();
        $rok_data = $response->viewData('rok');
        $this->assertEquals('Februarski rok', $rok_data->naziv);
    }

    public function test_store_rok_creates_new_exam_period(): void
    {
        $response = $this->post('/kalendar/storeRok/', [
            'rok_id' => 1,
            'naziv' => 'Novi rok',
            'pocetak' => '2026-03-15',
            'kraj' => '2026-03-29',
            'tipRoka_id' => 1,
            'komentar' => 'Test komentar',
            'indikatorAktivan' => 1,
        ]);

        $response->assertRedirect('/kalendar/');
        $this->assertDatabaseHas('aktivni_ispitni_rokovi', [
            'naziv' => 'Novi rok',
            'pocetak' => '2026-03-15',
            'kraj' => '2026-03-29',
        ]);
    }

    public function test_store_rok_with_comment_field(): void
    {
        $response = $this->post('/kalendar/storeRok/', [
            'rok_id' => 2,
            'naziv' => 'Februarski rok',
            'pocetak' => '2026-04-15',
            'kraj' => '2026-04-29',
            'tipRoka_id' => 1,
            'komentar' => 'Commentar',
            'indikatorAktivan' => 1,
        ]);

        $response->assertRedirect('/kalendar/');
        $this->assertDatabaseHas('aktivni_ispitni_rokovi', [
            'naziv' => 'Februarski rok',
            'komentar' => 'Commentar',
        ]);
    }

    public function test_store_rok_with_indicator_aktivan(): void
    {
        $response = $this->post('/kalendar/storeRok/', [
            'rok_id' => 4,
            'naziv' => 'Rok sa aktivni indikatorom',
            'pocetak' => '2026-06-15',
            'kraj' => '2026-06-29',
            'tipRoka_id' => 1,
            'komentar' => 'Komentar',
            'indikatorAktivan' => 1,
        ]);

        $response->assertRedirect('/kalendar/');
        $this->assertDatabaseHas('aktivni_ispitni_rokovi', [
            'naziv' => 'Rok sa aktivni indikatorom',
        ]);
    }

    public function test_delete_rok_removes_exam_period(): void
    {
        $rok = AktivniIspitniRokovi::factory()->create([
            'naziv' => 'Rok za brisanje',
        ]);

        $rokId = $rok->id;

        $response = $this->get("/kalendar/deleteRok/{$rok->id}");

        $response->assertRedirect('/kalendar/');
        $this->assertDatabaseMissing('aktivni_ispitni_rokovi', [
            'id' => $rokId,
        ]);
    }

    public function test_delete_rok_with_nonexistent_id_still_redirects(): void
    {
        $response = $this->get('/kalendar/deleteRok/9999');

        $response->assertRedirect('/kalendar/');
    }

    public function test_delete_rok_only_deletes_specified_record(): void
    {
        $rok1 = AktivniIspitniRokovi::factory()->create(['naziv' => 'Rok 1']);
        $rok2 = AktivniIspitniRokovi::factory()->create(['naziv' => 'Rok 2']);

        $this->get("/kalendar/deleteRok/{$rok1->id}");

        $this->assertDatabaseMissing('aktivni_ispitni_rokovi', ['id' => $rok1->id]);
        $this->assertDatabaseHas('aktivni_ispitni_rokovi', ['id' => $rok2->id]);
    }

    public function test_event_source_returns_json_format(): void
    {
        AktivniIspitniRokovi::factory()->create([
            'naziv' => 'Januarski rok',
            'pocetak' => '2026-01-15',
            'kraj' => '2026-01-29',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/kalendar/eventSource/');

        $response->assertOk();
        $response->assertJson([]);
    }

    public function test_event_source_returns_only_active_exam_periods(): void
    {
        AktivniIspitniRokovi::factory()->create([
            'naziv' => 'Aktivni rok',
            'pocetak' => '2026-01-15',
            'kraj' => '2026-01-29',
            'indikatorAktivan' => 1,
        ]);

        AktivniIspitniRokovi::factory()->neaktivan()->create([
            'naziv' => 'Neaktivni rok',
            'pocetak' => '2026-03-15',
            'kraj' => '2026-03-20',
            'indikatorAktivan' => 0,
        ]);

        $response = $this->get('/kalendar/eventSource/');

        $response->assertOk();
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals('Aktivni rok', $data[0]['title']);
    }

    public function test_event_source_maps_fields_correctly(): void
    {
        AktivniIspitniRokovi::factory()->create([
            'naziv' => 'Testni rok',
            'pocetak' => '2026-01-15',
            'kraj' => '2026-01-29',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/kalendar/eventSource/');

        $response->assertOk();
        $data = $response->json();

        $this->assertArrayHasKey('title', $data[0]);
        $this->assertArrayHasKey('start', $data[0]);
        $this->assertArrayHasKey('end', $data[0]);
        $this->assertEquals('Testni rok', $data[0]['title']);
    }

    public function test_event_source_returns_empty_array_when_no_active_periods(): void
    {
        AktivniIspitniRokovi::factory()->neaktivan()->create();

        $response = $this->get('/kalendar/eventSource/');

        $response->assertOk();
        $data = $response->json();
        $this->assertCount(0, $data);
    }

    public function test_event_source_includes_id_field(): void
    {
        AktivniIspitniRokovi::factory()->create([
            'naziv' => 'Rok sa ID-om',
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/kalendar/eventSource/');

        $response->assertOk();
        $data = $response->json();

        $this->assertArrayHasKey('id', $data[0]);
        $this->assertIsInt($data[0]['id']);
    }
}
