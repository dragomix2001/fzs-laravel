<?php

namespace Tests\Feature;

use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Services\UpisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private UpisService $upisService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->upisService = app(UpisService::class);
    }

    public function test_registruj_kandidata_za_osnovne_studije_kreira_cetiri_upis_godine_zapisa(): void
    {
        $tipStudija = TipStudija::factory()->create(['naziv' => 'Osnovne akademske studije', 'skrNaziv' => 'OAS', 'indikatorAktivan' => 1]);
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id, 'indikatorAktivan' => 1]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2024/2025']);

        $kandidat = \App\Models\Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'godinaStudija_id' => 1,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $this->assertDatabaseCount('upis_godine', 4);

        $this->assertDatabaseHas('upis_godine', [
            'kandidat_id' => $kandidat->id,
            'godina' => 1,
            'statusGodine_id' => 1,
        ]);

        $this->assertDatabaseHas('upis_godine', [
            'kandidat_id' => $kandidat->id,
            'godina' => 2,
            'statusGodine_id' => 3,
        ]);

        $this->assertDatabaseHas('upis_godine', [
            'kandidat_id' => $kandidat->id,
            'godina' => 3,
            'statusGodine_id' => 3,
        ]);

        $this->assertDatabaseHas('upis_godine', [
            'kandidat_id' => $kandidat->id,
            'godina' => 4,
            'statusGodine_id' => 3,
        ]);
    }

    public function test_registruj_kandidata_za_master_studije_kreira_jedan_upis_godine_zapis(): void
    {
        $tipStudija = TipStudija::factory()->create(['naziv' => 'Master akademske studije', 'skrNaziv' => 'MAS', 'indikatorAktivan' => 1]);
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2024/2025']);

        $kandidat = \App\Models\Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'godinaStudija_id' => 1,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $this->assertDatabaseCount('upis_godine', 1);

        $this->assertDatabaseHas('upis_godine', [
            'kandidat_id' => $kandidat->id,
            'godina' => 1,
            'statusGodine_id' => 1,
            'tipStudija_id' => $tipStudija->id,
        ]);
    }

    public function test_sprecava_dupli_upis_istog_kandidata(): void
    {
        $tipStudija = TipStudija::factory()->create(['naziv' => 'Osnovne akademske studije', 'skrNaziv' => 'OAS', 'indikatorAktivan' => 1]);
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2024/2025']);

        $kandidat = \App\Models\Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'godinaStudija_id' => 1,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);
        $this->assertDatabaseCount('upis_godine', 4);

        $this->upisService->registrujKandidata($kandidat->id);
        $this->assertDatabaseCount('upis_godine', 4);
    }

    public function test_generisi_broj_indeksa_formato_tip_studija_red_broj_godina_upisa(): void
    {
        $tipStudija = TipStudija::factory()->create(['naziv' => 'Osnovne akademske studije', 'skrNaziv' => 'OAS', 'indikatorAktivan' => 1]);
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2024/2025']);

        $kandidat = \App\Models\Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'godinaStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $this->upisService->generisiBrojIndeksa($kandidat->id);

        $kandidat->refresh();

        $this->assertNotNull($kandidat->brojIndeksa);
        $this->assertStringContainsString('/2024', $kandidat->brojIndeksa);
    }

    public function test_generisi_jedinstveni_broj_indeksa_za_vise_kandidata(): void
    {
        $tipStudija = TipStudija::factory()->create(['naziv' => 'Osnovne akademske studije', 'skrNaziv' => 'OAS', 'indikatorAktivan' => 1]);
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2024/2025']);

        $kandidat1 = \App\Models\Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'godinaStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $kandidat2 = \App\Models\Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'godinaStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $this->upisService->generisiBrojIndeksa($kandidat1->id);
        $this->upisService->generisiBrojIndeksa($kandidat2->id);

        $kandidat1->refresh();
        $kandidat2->refresh();

        $this->assertNotNull($kandidat1->brojIndeksa);
        $this->assertNotNull($kandidat2->brojIndeksa);
        $this->assertNotEquals($kandidat1->brojIndeksa, $kandidat2->brojIndeksa);
    }
}
