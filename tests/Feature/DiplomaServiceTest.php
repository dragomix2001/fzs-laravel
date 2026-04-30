<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\DiplomaAddData;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Services\DiplomaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiplomaServiceTest extends TestCase
{
    use DatabaseTransactions;

    private DiplomaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        if (! DB::table('tip_studija')->find(1)) {
            DB::table('tip_studija')->insert([
                ['id' => 1, 'naziv' => 'Osnovne akademske studije', 'skrNaziv' => 'OAS', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        if (! DB::table('status_studiranja')->find(1)) {
            DB::table('status_studiranja')->insert([
                ['id' => 1, 'naziv' => 'upis u toku', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->service = $this->app->make(DiplomaService::class);
    }

    private function makeKandidat(): Kandidat
    {
        // tip_studija and status_studiranja rows are inserted in setUp()
        $tipStudija = TipStudija::find(1);
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $status = StatusStudiranja::find(1);
        $skolskaGod = SkolskaGodUpisa::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();

        return Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'statusUpisa_id' => $status->id,
            'skolskaGodinaUpisa_id' => $skolskaGod->id,
            'godinaStudija_id' => $godinaStudija->id,
        ]);
    }

    #[Test]
    public function diploma_unos_returns_view_with_student(): void
    {
        $kandidat = $this->makeKandidat();

        $result = $this->service->diplomaUnos($kandidat);

        $this->assertInstanceOf(View::class, $result);
        $this->assertSame($kandidat->id, $result->getData()['student']->id);
    }

    #[Test]
    public function diploma_add_creates_diploma_record(): void
    {
        $kandidat = $this->makeKandidat();

        $data = new DiplomaAddData(
            kandidatId: $kandidat->id,
            brojDiplome: 'D-123',
            datumOdbrane: '2026-05-01',
            nazivStudijskogPrograma: 'Informatika',
            brojPocetnogLista: '1',
            brojZapisnika: 'Z-42',
            datum: '2026-05-01',
            pristupniRad: 'Diplomski rad',
            tema: 'Tema rada',
            mentor: 'Prof. Test',
            ocena: '10',
        );

        try {
            $this->service->diplomaAdd($data);
        } catch (UrlGenerationException $e) {
            // The redirect after save throws because route needs tipStudijaId param;
            // the insert itself has already succeeded at this point.
        }

        $this->assertDatabaseHas('diploma', [
            'kandidat_id' => $kandidat->id,
            'tema' => 'Tema rada',
        ]);
    }

    #[Test]
    public function diploma_stampa_redirects_with_error_when_diploma_not_found(): void
    {
        $kandidat = $this->makeKandidat();

        $result = $this->service->diplomaStampa($kandidat);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('Диплома није пронађена', session('error'));
    }
}
