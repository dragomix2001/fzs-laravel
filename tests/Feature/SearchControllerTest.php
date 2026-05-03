<?php

namespace Tests\Feature;

use App\Http\Controllers\SearchController;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
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
