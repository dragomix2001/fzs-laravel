<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ArhivIndeksa;
use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\UpisGodine;
use App\Services\UpisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UpisServiceTest extends TestCase
{
    use RefreshDatabase;

    private UpisService $upisService;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (range(1, 6) as $id) {
            DB::table('status_studiranja')->insertOrIgnore([
                'id' => $id,
                'naziv' => "Status {$id}",
                'indikatorAktivan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->upisService = app(UpisService::class);
    }

    private function createTipStudija(string $skrNaziv, ?int $id = null): TipStudija
    {
        if ($id !== null) {
            $existing = TipStudija::find($id);
            if ($existing) {
                return $existing;
            }

            return TipStudija::forceCreate([
                'id' => $id,
                'naziv' => $skrNaziv.' akademske studije',
                'skrNaziv' => $skrNaziv,
                'indikatorAktivan' => 1,
            ]);
        }

        return TipStudija::create([
            'naziv' => $skrNaziv.' akademske studije',
            'skrNaziv' => $skrNaziv,
            'indikatorAktivan' => 1,
        ]);
    }

    private function createKandidat(array $overrides = []): Kandidat
    {
        $tipStudija = $overrides['_tipStudija'] ?? $this->createTipStudija('OAS', 1);
        $skolskaGodina = $overrides['_skolskaGodina'] ?? SkolskaGodUpisa::factory()->create(['naziv' => '2024/2025']);
        $program = $overrides['_program'] ?? StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);

        unset($overrides['_tipStudija'], $overrides['_skolskaGodina'], $overrides['_program']);

        return Kandidat::create(array_merge([
            'imeKandidata' => 'Test',
            'prezimeKandidata' => 'Student',
            'jmbg' => '1234567890123',
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'statusUpisa_id' => 1,
            'godinaStudija_id' => 1,
            'krsnaSlava_id' => 1,
            'uspehSrednjaSkola_id' => 1,
            'opstiUspehSrednjaSkola_id' => 1,
            'mesto_id' => 1,
            'indikatorAktivan' => 1,
            'uplata' => 0,
            'upisan' => 0,
        ], $overrides));
    }

    // =========================================================================
    // registrujKandidata() tests
    // =========================================================================

    public function test_registruj_kandidata_returns_early_when_kandidat_already_registered(): void
    {
        $kandidat = $this->createKandidat(['tipStudija_id' => 1]);

        UpisGodine::create([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'godina' => 1,
            'pokusaj' => 1,
            'statusGodine_id' => 1,
            'skolskaGodina_id' => $kandidat->skolskaGodinaUpisa_id,
            'datumUpisa' => now(),
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $count = UpisGodine::where('kandidat_id', $kandidat->id)->count();
        $this->assertEquals(1, $count);
    }

    public function test_registruj_kandidata_creates_four_godine_for_oas_godina_1(): void
    {
        $tipStudija = $this->createTipStudija('OAS', 1);
        $kandidat = $this->createKandidat([
            '_tipStudija' => $tipStudija,
            'tipStudija_id' => $tipStudija->id,
            'godinaStudija_id' => 1,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $upisi = UpisGodine::where('kandidat_id', $kandidat->id)->orderBy('godina')->get();

        $this->assertCount(4, $upisi);

        // Year 1 should be enrolled (statusGodine_id = 1)
        $this->assertEquals(1, $upisi[0]->godina);
        $this->assertEquals(1, $upisi[0]->statusGodine_id);
        $this->assertEquals($kandidat->skolskaGodinaUpisa_id, $upisi[0]->skolskaGodina_id);
        $this->assertNotNull($upisi[0]->datumUpisa);

        // Years 2-4 should be not enrolled (statusGodine_id = 3)
        foreach ([1, 2, 3] as $idx) {
            $this->assertEquals($idx + 1, $upisi[$idx]->godina);
            $this->assertEquals(3, $upisi[$idx]->statusGodine_id);
            $this->assertNull($upisi[$idx]->skolskaGodina_id);
            $this->assertNull($upisi[$idx]->datumUpisa);
        }
    }

    public function test_registruj_kandidata_creates_four_godine_for_oas_godina_2(): void
    {
        $tipStudija = $this->createTipStudija('OAS', 1);
        $kandidat = $this->createKandidat([
            '_tipStudija' => $tipStudija,
            'tipStudija_id' => $tipStudija->id,
            'godinaStudija_id' => 2,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $upisi = UpisGodine::where('kandidat_id', $kandidat->id)->orderBy('godina')->get();

        $this->assertCount(4, $upisi);

        // Year 1: not enrolled
        $this->assertEquals(3, $upisi[0]->statusGodine_id);

        // Year 2: enrolled
        $this->assertEquals(2, $upisi[1]->godina);
        $this->assertEquals(1, $upisi[1]->statusGodine_id);
        $this->assertEquals($kandidat->skolskaGodinaUpisa_id, $upisi[1]->skolskaGodina_id);
        $this->assertNotNull($upisi[1]->datumUpisa);

        // Years 3-4: not enrolled
        $this->assertEquals(3, $upisi[2]->statusGodine_id);
        $this->assertEquals(3, $upisi[3]->statusGodine_id);
    }

    public function test_registruj_kandidata_creates_four_godine_for_oas_godina_3(): void
    {
        $tipStudija = $this->createTipStudija('OAS', 1);
        $kandidat = $this->createKandidat([
            '_tipStudija' => $tipStudija,
            'tipStudija_id' => $tipStudija->id,
            'godinaStudija_id' => 3,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $upisi = UpisGodine::where('kandidat_id', $kandidat->id)->orderBy('godina')->get();

        $this->assertCount(4, $upisi);

        // Year 3: enrolled
        $this->assertEquals(3, $upisi[2]->godina);
        $this->assertEquals(1, $upisi[2]->statusGodine_id);
        $this->assertNotNull($upisi[2]->datumUpisa);
    }

    public function test_registruj_kandidata_creates_four_godine_for_oas_godina_4(): void
    {
        $tipStudija = $this->createTipStudija('OAS', 1);
        $kandidat = $this->createKandidat([
            '_tipStudija' => $tipStudija,
            'tipStudija_id' => $tipStudija->id,
            'godinaStudija_id' => 4,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $upisi = UpisGodine::where('kandidat_id', $kandidat->id)->orderBy('godina')->get();

        $this->assertCount(4, $upisi);

        // Year 4: enrolled
        $this->assertEquals(4, $upisi[3]->godina);
        $this->assertEquals(1, $upisi[3]->statusGodine_id);
        $this->assertNotNull($upisi[3]->datumUpisa);
    }

    public function test_registruj_kandidata_creates_one_godina_for_mas(): void
    {
        $tipStudija = $this->createTipStudija('MAS', 2);
        $kandidat = $this->createKandidat([
            '_tipStudija' => $tipStudija,
            'tipStudija_id' => $tipStudija->id,
            'godinaStudija_id' => 1,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $upisi = UpisGodine::where('kandidat_id', $kandidat->id)->get();

        $this->assertCount(1, $upisi);
        $this->assertEquals(1, $upisi[0]->godina);
        $this->assertEquals(1, $upisi[0]->statusGodine_id);
        $this->assertEquals($kandidat->skolskaGodinaUpisa_id, $upisi[0]->skolskaGodina_id);
        $this->assertNotNull($upisi[0]->datumUpisa);
        $this->assertNotNull($upisi[0]->datumPromene);
    }

    public function test_registruj_kandidata_creates_one_godina_for_das(): void
    {
        $tipStudija = $this->createTipStudija('DAS', 3);
        $kandidat = $this->createKandidat([
            '_tipStudija' => $tipStudija,
            'tipStudija_id' => $tipStudija->id,
            'godinaStudija_id' => 1,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $upisi = UpisGodine::where('kandidat_id', $kandidat->id)->get();

        $this->assertCount(1, $upisi);
        $this->assertEquals(1, $upisi[0]->godina);
        $this->assertEquals(1, $upisi[0]->statusGodine_id);
        $this->assertEquals($kandidat->skolskaGodinaUpisa_id, $upisi[0]->skolskaGodina_id);
        $this->assertNotNull($upisi[0]->datumUpisa);
        $this->assertNotNull($upisi[0]->datumPromene);
    }

    public function test_registruj_kandidata_does_nothing_for_unknown_tip_studija(): void
    {
        $tipStudija = $this->createTipStudija('UNKNOWN', 99);
        $kandidat = $this->createKandidat([
            '_tipStudija' => $tipStudija,
            'tipStudija_id' => $tipStudija->id,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $count = UpisGodine::where('kandidat_id', $kandidat->id)->count();
        $this->assertEquals(0, $count);
    }

    public function test_registruj_kandidata_handles_null_tip_studija(): void
    {
        $tipStudija = $this->createTipStudija('TEST', 999);
        $kandidat = $this->createKandidat([
            '_tipStudija' => $tipStudija,
            'tipStudija_id' => 999,
        ]);

        $this->upisService->registrujKandidata($kandidat->id);

        $count = UpisGodine::where('kandidat_id', $kandidat->id)->count();
        $this->assertEquals(0, $count);
    }

    // =========================================================================
    // upisMasterPostojeciKandidat() tests
    // =========================================================================

    public function test_upis_master_postojeci_kandidat_creates_new_master_kandidat_from_oas(): void
    {
        Config::set('constants.statusi.upisan', 1);

        $tipOAS = $this->createTipStudija('OAS', 1);
        $tipMAS = $this->createTipStudija('MAS', 2);
        $skolskaGodinaUpisa = SkolskaGodUpisa::factory()->create(['naziv' => '2025/2026']);

        $kandidatOAS = $this->createKandidat([
            '_tipStudija' => $tipOAS,
            'tipStudija_id' => $tipOAS->id,
            'brojIndeksa' => '1001/2024',
        ]);

        $newProgramId = 10;

        $result = $this->upisService->upisMasterPostojeciKandidat(
            $kandidatOAS->id,
            $newProgramId,
            $skolskaGodinaUpisa->id
        );

        $this->assertIsInt($result);
        $this->assertNotEquals($kandidatOAS->id, $result);

        $newKandidat = Kandidat::find($result);
        $this->assertEquals($tipMAS->id, $newKandidat->tipStudija_id);
        $this->assertEquals($newProgramId, $newKandidat->studijskiProgram_id);
        $this->assertEquals($skolskaGodinaUpisa->id, $newKandidat->skolskaGodinaUpisa_id);
        $this->assertEquals(1, $newKandidat->statusUpisa_id);
        $this->assertNotNull($newKandidat->brojIndeksa);
    }

    public function test_upis_master_postojeci_kandidat_returns_false_when_not_oas(): void
    {
        $tipMAS = $this->createTipStudija('MAS', 2);
        $kandidatMAS = $this->createKandidat([
            '_tipStudija' => $tipMAS,
            'tipStudija_id' => $tipMAS->id,
        ]);

        $result = $this->upisService->upisMasterPostojeciKandidat($kandidatMAS->id, 10, 1);

        $this->assertFalse($result);
    }

    public function test_upis_master_postojeci_kandidat_returns_false_when_kandidat_not_found(): void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function replicate() on null');

        $this->upisService->upisMasterPostojeciKandidat(999999, 10, 1);
    }

    // =========================================================================
    // upisiGodinu() tests
    // =========================================================================

    public function test_upisi_godinu_enrolls_godina_successfully(): void
    {
        $kandidat = $this->createKandidat(['godinaStudija_id' => 1, 'brojIndeksa' => '1001/2024']);
        $skolskaGodina = SkolskaGodUpisa::factory()->create();

        UpisGodine::create([
            'kandidat_id' => $kandidat->id,
            'godina' => 2,
            'pokusaj' => 1,
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'statusGodine_id' => 3,
            'skolskaGodina_id' => null,
            'datumUpisa' => null,
        ]);

        $result = $this->upisService->upisiGodinu($kandidat->id, 2, $skolskaGodina->id);

        $this->assertTrue($result);

        $upis = UpisGodine::where([
            'kandidat_id' => $kandidat->id,
            'godina' => 2,
        ])->first();

        $this->assertEquals(1, $upis->statusGodine_id);
        $this->assertEquals($skolskaGodina->id, $upis->skolskaGodina_id);
        $this->assertNotNull($upis->datumUpisa);

        $kandidat->refresh();
        $this->assertEquals(2, $kandidat->godinaStudija_id);
    }

    public function test_upisi_godinu_generates_broj_indeksa_when_empty(): void
    {
        $kandidat = $this->createKandidat(['godinaStudija_id' => 1, 'brojIndeksa' => null]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2025/2026']);

        UpisGodine::create([
            'kandidat_id' => $kandidat->id,
            'godina' => 2,
            'pokusaj' => 1,
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'statusGodine_id' => 3,
            'skolskaGodina_id' => null,
            'datumUpisa' => null,
        ]);

        $result = $this->upisService->upisiGodinu($kandidat->id, 2, $skolskaGodina->id);

        $this->assertTrue($result);

        $kandidat->refresh();
        $this->assertNotNull($kandidat->brojIndeksa);
    }

    public function test_upisi_godinu_returns_false_when_upis_fails(): void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Attempt to assign property "statusGodine_id" on null');

        $kandidat = $this->createKandidat(['godinaStudija_id' => 1]);

        // No UpisGodine record exists, will cause null error
        $this->upisService->upisiGodinu($kandidat->id, 2, 1);
    }

    // =========================================================================
    // generisiBrojIndeksa() tests
    // =========================================================================

    public function test_generisi_broj_indeksa_creates_first_index_when_no_arhiv_exists(): void
    {
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2025/2026']);
        $kandidat = $this->createKandidat([
            '_skolskaGodina' => $skolskaGodina,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'tipStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $this->upisService->generisiBrojIndeksa($kandidat->id);

        $kandidat->refresh();
        $this->assertEquals('1001/2025', $kandidat->brojIndeksa);

        $arhiv = ArhivIndeksa::where([
            'tipStudija_id' => 1,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
        ])->first();

        $this->assertNotNull($arhiv);
        $this->assertEquals(1, $arhiv->indeks);
    }

    public function test_generisi_broj_indeksa_increments_arhiv_index(): void
    {
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2025/2026']);

        ArhivIndeksa::forceCreate([
            'tipStudija_id' => 1,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'indeks' => 0,
        ]);

        $kandidat = $this->createKandidat([
            '_skolskaGodina' => $skolskaGodina,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'tipStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $this->upisService->generisiBrojIndeksa($kandidat->id);

        $kandidat->refresh();
        $this->assertEquals('1001/2025', $kandidat->brojIndeksa);

        $arhiv = ArhivIndeksa::where([
            'tipStudija_id' => 1,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
        ])->first();

        $this->assertEquals(1, $arhiv->indeks);
    }

    public function test_generisi_broj_indeksa_skips_duplicate_indices(): void
    {
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2025/2026']);
        $tipStudija = $this->createTipStudija('OAS', 1);

        ArhivIndeksa::forceCreate([
            'tipStudija_id' => 1,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'indeks' => 5,
        ]);

        // Create existing kandidat with index 1006/2025
        $existingProgram = StudijskiProgram::factory()->create(['tipStudija_id' => 1]);
        Kandidat::create([
            'imeKandidata' => 'Existing',
            'prezimeKandidata' => 'Student',
            'jmbg' => '9999999999999',
            'tipStudija_id' => 1,
            'studijskiProgram_id' => $existingProgram->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'statusUpisa_id' => 1,
            'godinaStudija_id' => 1,
            'krsnaSlava_id' => 1,
            'uspehSrednjaSkola_id' => 1,
            'opstiUspehSrednjaSkola_id' => 1,
            'mesto_id' => 1,
            'indikatorAktivan' => 1,
            'uplata' => 0,
            'upisan' => 0,
            'brojIndeksa' => '1006/2025',
        ]);

        $kandidat = $this->createKandidat([
            '_tipStudija' => $tipStudija,
            '_skolskaGodina' => $skolskaGodina,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'tipStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $this->upisService->generisiBrojIndeksa($kandidat->id);

        $kandidat->refresh();
        $this->assertEquals('1007/2025', $kandidat->brojIndeksa);
    }

    public function test_generisi_broj_indeksa_formats_single_digit_correctly(): void
    {
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2025/2026']);

        ArhivIndeksa::forceCreate([
            'tipStudija_id' => 1,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'indeks' => 5,
        ]);

        $kandidat = $this->createKandidat([
            '_skolskaGodina' => $skolskaGodina,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'tipStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $this->upisService->generisiBrojIndeksa($kandidat->id);

        $kandidat->refresh();
        $this->assertEquals('1006/2025', $kandidat->brojIndeksa);
    }

    public function test_generisi_broj_indeksa_formats_two_digit_correctly(): void
    {
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2025/2026']);

        ArhivIndeksa::forceCreate([
            'tipStudija_id' => 1,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'indeks' => 11,
        ]);

        $kandidat = $this->createKandidat([
            '_skolskaGodina' => $skolskaGodina,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'tipStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $this->upisService->generisiBrojIndeksa($kandidat->id);

        $kandidat->refresh();
        $this->assertEquals('1012/2025', $kandidat->brojIndeksa);
    }

    public function test_generisi_broj_indeksa_formats_three_digit_correctly(): void
    {
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2025/2026']);

        ArhivIndeksa::forceCreate([
            'tipStudija_id' => 1,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'indeks' => 123,
        ]);

        $kandidat = $this->createKandidat([
            '_skolskaGodina' => $skolskaGodina,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'tipStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $this->upisService->generisiBrojIndeksa($kandidat->id);

        $kandidat->refresh();
        $this->assertEquals('1124/2025', $kandidat->brojIndeksa);
    }

    public function test_generisi_broj_indeksa_with_different_tip_studija(): void
    {
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2025/2026']);
        $tipMAS = $this->createTipStudija('MAS', 2);

        $kandidat = $this->createKandidat([
            '_tipStudija' => $tipMAS,
            '_skolskaGodina' => $skolskaGodina,
            'tipStudija_id' => $tipMAS->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'brojIndeksa' => null,
        ]);

        $this->upisService->generisiBrojIndeksa($kandidat->id);

        $kandidat->refresh();
        $this->assertEquals('2001/2025', $kandidat->brojIndeksa);
    }

    public function test_generisi_broj_indeksa_multiple_candidates_get_sequential_indices(): void
    {
        $skolskaGodina = SkolskaGodUpisa::factory()->create(['naziv' => '2025/2026']);

        $kandidat1 = $this->createKandidat([
            '_skolskaGodina' => $skolskaGodina,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'tipStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $kandidat2 = $this->createKandidat([
            '_skolskaGodina' => $skolskaGodina,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'tipStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $kandidat3 = $this->createKandidat([
            '_skolskaGodina' => $skolskaGodina,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'tipStudija_id' => 1,
            'brojIndeksa' => null,
        ]);

        $this->upisService->generisiBrojIndeksa($kandidat1->id);
        $this->upisService->generisiBrojIndeksa($kandidat2->id);
        $this->upisService->generisiBrojIndeksa($kandidat3->id);

        $kandidat1->refresh();
        $kandidat2->refresh();
        $kandidat3->refresh();

        $this->assertEquals('1001/2025', $kandidat1->brojIndeksa);
        $this->assertEquals('1002/2025', $kandidat2->brojIndeksa);
        $this->assertEquals('1003/2025', $kandidat3->brojIndeksa);
    }
}
