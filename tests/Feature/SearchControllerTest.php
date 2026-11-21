<?php

namespace Tests\Feature;

use App\Http\Controllers\SearchController;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TipStudija::factory()->create();
        StudijskiProgram::factory()->create();
        SkolskaGodUpisa::factory()->create();
        StatusStudiranja::factory()->create();
        GodinaStudija::factory()->create();
    }

    public function test_search_page_loads_with_reference_data(): void
    {
        $controller = app(SearchController::class);
        $response = $controller->search();

        $this->assertSame('search.index', $response->name());
        $data = $response->getData();
        $this->assertArrayHasKey('studijskiProgrami', $data);
        $this->assertArrayHasKey('godineStudija', $data);
        $this->assertArrayHasKey('skolskeGodine', $data);
        $this->assertArrayHasKey('statusi', $data);
    }

    public function test_search_result_without_filters_returns_empty_students_collection(): void
    {
        Kandidat::factory()->count(2)->create();

        $request = Request::create('/pretraga', 'POST', []);
        $controller = app(SearchController::class);
        $response = $controller->searchResult($request);

        $data = $response->getData();
        $this->assertSame(0, $data['studenti']->count());
    }

    public function test_search_result_filters_students_by_text_term(): void
    {
        Kandidat::factory()->create([
            'imeKandidata' => 'Petar',
            'prezimeKandidata' => 'Petrovic',
            'brojIndeksa' => 'SV-001',
            'jmbg' => '1234567890123',
        ]);

        Kandidat::factory()->create([
            'imeKandidata' => 'Marko',
            'prezimeKandidata' => 'Markovic',
        ]);

        $request = Request::create('/pretraga', 'POST', [
            'pretraga' => 'Petar',
        ]);
        $controller = app(SearchController::class);
        $response = $controller->searchResult($request);

        $data = $response->getData();
        $this->assertSame(1, $data['studenti']->count());
        $this->assertSame('Petar', $data['studenti']->first()->imeKandidata);
    }

    public function test_jmbg_search_matches_only_an_exact_identifier(): void
    {
        $target = Kandidat::factory()->create([
            'jmbg' => '1234567890123',
            'imeKandidata' => 'Target',
            'prezimeKandidata' => 'Student',
        ]);
        Kandidat::factory()->create([
            'jmbg' => '1234567890124',
            'imeKandidata' => 'Other',
            'prezimeKandidata' => 'Student',
        ]);

        $response = app(SearchController::class)->searchResult(
            Request::create('/pretraga', 'GET', ['pretraga' => '1234567890123'])
        );

        $students = $response->getData()['studenti'];
        $this->assertInstanceOf(LengthAwarePaginator::class, $students);
        $this->assertSame(1, $students->total());
        $this->assertTrue($students->first()->is($target));
    }

    public function test_search_matches_both_parts_of_a_full_student_name(): void
    {
        $target = Kandidat::factory()->create([
            'imeKandidata' => 'Petar',
            'prezimeKandidata' => 'Petrovic',
        ]);
        Kandidat::factory()->create([
            'imeKandidata' => 'Petar',
            'prezimeKandidata' => 'Markovic',
        ]);

        $response = app(SearchController::class)->searchResult(
            Request::create('/pretraga', 'GET', ['pretraga' => 'Petar Petrovic'])
        );

        $students = $response->getData()['studenti'];
        $this->assertSame(1, $students->total());
        $this->assertTrue($students->first()->is($target));
    }

    public function test_student_search_is_paginated_and_preserves_filters(): void
    {
        Kandidat::factory()->count(26)->create([
            'imeKandidata' => 'Searchable',
            'prezimeKandidata' => 'Candidate',
        ]);

        $response = $this->get('/pretraga?pretraga=Searchable');

        $response->assertOk();
        $students = $response->viewData('studenti');
        $this->assertInstanceOf(LengthAwarePaginator::class, $students);
        $this->assertSame(26, $students->total());
        $this->assertCount(25, $students);
        $this->assertSame('student_page', $students->getPageName());
        $response->assertSee('pretraga=Searchable', false);
        $response->assertSee('student_page=2', false);
    }

    public function test_subject_search_uses_subject_program_records(): void
    {
        $program = StudijskiProgram::query()->firstOrFail();
        $tipStudija = TipStudija::query()->firstOrFail();
        $godinaStudija = GodinaStudija::query()->firstOrFail();
        $skolskaGodina = SkolskaGodUpisa::query()->firstOrFail();
        $tipPredmetaId = TipPredmeta::factory()->create()->id;
        $predmet = Predmet::factory()->create(['naziv' => 'Pretrazivi predmet']);
        PredmetProgram::create([
            'studijskiProgram_id' => $program->id,
            'godinaStudija_id' => $godinaStudija->id,
            'semestar' => 1,
            'predmet_id' => $predmet->id,
            'tipPredmeta_id' => $tipPredmetaId,
            'tipStudija_id' => $tipStudija->id,
            'espb' => 6,
            'statusPredmeta' => 1,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => $skolskaGodina->id,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/pretraga?pretraga_predmet=Pretrazivi');

        $response->assertOk();
        $subjects = $response->viewData('predmeti');
        $this->assertInstanceOf(LengthAwarePaginator::class, $subjects);
        $this->assertSame(1, $subjects->total());
        $this->assertSame($predmet->id, $subjects->first()->predmet_id);
        $this->assertTrue($subjects->first()->relationLoaded('predmet'));
    }

    public function test_search_result_applies_program_year_status_and_school_filters(): void
    {
        $tip = TipStudija::factory()->create();
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tip->id]);
        $godinaStudija = GodinaStudija::factory()->create();
        $status = StatusStudiranja::factory()->create();
        $skolska = SkolskaGodUpisa::factory()->create();

        Kandidat::factory()->create([
            'studijskiProgram_id' => $program->id,
            'godinaStudija_id' => $godinaStudija->id,
            'statusUpisa_id' => $status->id,
            'skolskaGodinaUpisa_id' => $skolska->id,
            'imeKandidata' => 'Ukljucen',
        ]);

        Kandidat::factory()->create([
            'imeKandidata' => 'Iskljucen',
        ]);

        $request = Request::create('/pretraga', 'POST', [
            'studijski_program_id' => $program->id,
            'godina_studija_id' => $godinaStudija->id,
            'status_upisa_id' => $status->id,
            'skolska_godina_id' => $skolska->id,
        ]);
        $controller = app(SearchController::class);
        $response = $controller->searchResult($request);

        $data = $response->getData();
        $this->assertSame(1, $data['studenti']->count());
        $this->assertSame('Ukljucen', $data['studenti']->first()->imeKandidata);
    }
}
