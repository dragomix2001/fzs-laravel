<?php

namespace Tests\Feature;

use App\Models\AktivniIspitniRokovi;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Services\IspitMembershipService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IspitMembershipServiceTest extends TestCase
{
    use DatabaseTransactions;

    private IspitMembershipService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(IspitMembershipService::class);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function buildFixtures(): array
    {
        $tipStudija = TipStudija::factory()->create();
        $program = StudijskiProgram::factory()->create(['tipStudija_id' => $tipStudija->id]);
        $skolskaGodina = SkolskaGodUpisa::factory()->create();
        $statusStudiranja = StatusStudiranja::factory()->create();

        $kandidat = Kandidat::factory()->create([
            'studijskiProgram_id' => $program->id,
            'tipStudija_id' => $tipStudija->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'statusUpisa_id' => $statusStudiranja->id,
        ]);

        $predmet = Predmet::factory()->create();
        $predmetProgram = $this->makePredmetProgram($kandidat, $predmet);

        $profesor = Profesor::factory()->create();
        $rok = AktivniIspitniRokovi::factory()->create(['indikatorAktivan' => 1]);

        $zapisnik = ZapisnikOPolaganjuIspita::factory()->create([
            'predmet_id' => $predmet->id,
            'profesor_id' => $profesor->id,
            'rok_id' => $rok->id,
        ]);

        return compact('tipStudija', 'program', 'skolskaGodina', 'statusStudiranja',
            'kandidat', 'predmet', 'predmetProgram', 'profesor', 'rok', 'zapisnik');
    }

    private function makePredmetProgram(Kandidat $kandidat, ?Predmet $predmet = null): PredmetProgram
    {
        $predmet ??= Predmet::factory()->create();
        $tipPredmeta = TipPredmeta::query()->first() ?? TipPredmeta::forceCreate([
            'naziv' => 'Obavezni',
            'skrNaziv' => 'OBV',
            'indikatorAktivan' => 1,
        ]);

        return PredmetProgram::create([
            'predmet_id' => $predmet->id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'tipStudija_id' => $kandidat->tipStudija_id,
            'semestar' => 1,
            'espb' => 6,
            'godinaStudija_id' => $kandidat->godinaStudija_id,
            'tipPredmeta_id' => $tipPredmeta->id,
            'statusPredmeta' => 1,
            'predavanja' => 0,
            'vezbe' => 0,
            'skolskaGodina_id' => $kandidat->skolskaGodinaUpisa_id,
        ]);
    }

    private function enrollStudent(ZapisnikOPolaganjuIspita $zapisnik, Kandidat $kandidat): ZapisnikOPolaganju_Student
    {
        $predmetProgram = $this->makePredmetProgram($kandidat, Predmet::findOrFail($zapisnik->predmet_id));
        $prijava = PrijavaIspita::factory()->create([
            'kandidat_id' => $kandidat->id,
            'predmet_id' => $predmetProgram->id,
            'rok_id' => $zapisnik->rok_id,
        ]);

        return ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $zapisnik->id,
            'prijavaIspita_id' => $prijava->id,
            'kandidat_id' => $kandidat->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // addStudentToZapisnik
    // -----------------------------------------------------------------------

    public function test_add_student_creates_student_record_and_polozeni_ispit(): void
    {
        $f = $this->buildFixtures();

        $this->service->addStudentToZapisnik($f['zapisnik']->id, [$f['kandidat']->id]);

        $this->assertDatabaseHas('zapisnik_o_polaganju__student', [
            'zapisnik_id' => $f['zapisnik']->id,
            'kandidat_id' => $f['kandidat']->id,
        ]);
        $this->assertDatabaseHas('polozeni_ispiti', [
            'zapisnik_id' => $f['zapisnik']->id,
            'kandidat_id' => $f['kandidat']->id,
        ]);
    }

    public function test_add_student_creates_prijava_ispita(): void
    {
        $f = $this->buildFixtures();

        $this->service->addStudentToZapisnik($f['zapisnik']->id, [$f['kandidat']->id]);

        $student = ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $f['zapisnik']->id,
            'kandidat_id' => $f['kandidat']->id,
        ])->firstOrFail();

        $this->assertNotNull($student->prijavaIspita_id);
        $this->assertDatabaseHas('prijava_ispita', ['id' => $student->prijavaIspita_id]);
    }

    public function test_add_student_links_new_study_program_to_zapisnik(): void
    {
        $f = $this->buildFixtures();

        $this->service->addStudentToZapisnik($f['zapisnik']->id, [$f['kandidat']->id]);

        $this->assertDatabaseHas('zapisnik_o_polaganju__studijski_program', [
            'zapisnik_id' => $f['zapisnik']->id,
            'StudijskiProgram_id' => $f['program']->id,
        ]);
    }

    public function test_add_student_skips_already_enrolled_student(): void
    {
        $f = $this->buildFixtures();
        $this->enrollStudent($f['zapisnik'], $f['kandidat']);

        $this->service->addStudentToZapisnik($f['zapisnik']->id, [$f['kandidat']->id]);

        $count = ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $f['zapisnik']->id,
            'kandidat_id' => $f['kandidat']->id,
        ])->count();

        $this->assertEquals(1, $count);
    }

    public function test_add_student_skips_when_no_matching_predmet_program(): void
    {
        $f = $this->buildFixtures();

        // kandidat from a completely different program with no PredmetProgram for this subject
        $otherProgram = StudijskiProgram::factory()->create(['tipStudija_id' => $f['tipStudija']->id]);
        $otherKandidat = Kandidat::factory()->create([
            'studijskiProgram_id' => $otherProgram->id,
            'tipStudija_id' => $f['tipStudija']->id,
            'skolskaGodinaUpisa_id' => $f['skolskaGodina']->id,
            'statusUpisa_id' => $f['statusStudiranja']->id,
        ]);

        $this->service->addStudentToZapisnik($f['zapisnik']->id, [$otherKandidat->id]);

        $this->assertDatabaseMissing('zapisnik_o_polaganju__student', [
            'zapisnik_id' => $f['zapisnik']->id,
            'kandidat_id' => $otherKandidat->id,
        ]);
    }

    public function test_add_student_does_not_duplicate_existing_study_program_link(): void
    {
        $f = $this->buildFixtures();

        // pre-link the study program
        ZapisnikOPolaganju_StudijskiProgram::create([
            'zapisnik_id' => $f['zapisnik']->id,
            'StudijskiProgram_id' => $f['program']->id,
        ]);

        $this->service->addStudentToZapisnik($f['zapisnik']->id, [$f['kandidat']->id]);

        $count = ZapisnikOPolaganju_StudijskiProgram::where([
            'zapisnik_id' => $f['zapisnik']->id,
            'StudijskiProgram_id' => $f['program']->id,
        ])->count();

        $this->assertEquals(1, $count);
    }

    public function test_add_multiple_students_from_same_program_links_program_once(): void
    {
        $f = $this->buildFixtures();

        $kandidat2 = Kandidat::factory()->create([
            'studijskiProgram_id' => $f['program']->id,
            'tipStudija_id' => $f['tipStudija']->id,
            'skolskaGodinaUpisa_id' => $f['skolskaGodina']->id,
            'statusUpisa_id' => $f['statusStudiranja']->id,
        ]);

        $this->service->addStudentToZapisnik($f['zapisnik']->id, [
            $f['kandidat']->id,
            $kandidat2->id,
        ]);

        $count = ZapisnikOPolaganju_StudijskiProgram::where([
            'zapisnik_id' => $f['zapisnik']->id,
            'StudijskiProgram_id' => $f['program']->id,
        ])->count();

        $this->assertEquals(1, $count);
    }

    // -----------------------------------------------------------------------
    // removeStudentFromZapisnik
    // -----------------------------------------------------------------------

    public function test_remove_student_deletes_student_and_polozeni_ispit(): void
    {
        $f = $this->buildFixtures();
        $kandidat2 = Kandidat::factory()->create([
            'studijskiProgram_id' => $f['program']->id,
            'tipStudija_id' => $f['tipStudija']->id,
            'skolskaGodinaUpisa_id' => $f['skolskaGodina']->id,
            'statusUpisa_id' => $f['statusStudiranja']->id,
        ]);

        $this->enrollStudent($f['zapisnik'], $f['kandidat']);
        $this->enrollStudent($f['zapisnik'], $kandidat2);

        PolozeniIspiti::create([
            'kandidat_id' => $f['kandidat']->id,
            'predmet_id' => $f['predmetProgram']->id,
            'zapisnik_id' => $f['zapisnik']->id,
            'prijava_id' => null,
            'indikatorAktivan' => 0,
        ]);

        $this->service->removeStudentFromZapisnik($f['zapisnik']->id, $f['kandidat']->id);

        $this->assertDatabaseMissing('zapisnik_o_polaganju__student', [
            'zapisnik_id' => $f['zapisnik']->id,
            'kandidat_id' => $f['kandidat']->id,
        ]);
        $this->assertDatabaseMissing('polozeni_ispiti', [
            'zapisnik_id' => $f['zapisnik']->id,
            'kandidat_id' => $f['kandidat']->id,
        ]);
    }

    public function test_remove_student_returns_false_when_others_remain(): void
    {
        $f = $this->buildFixtures();
        $kandidat2 = Kandidat::factory()->create([
            'studijskiProgram_id' => $f['program']->id,
            'tipStudija_id' => $f['tipStudija']->id,
            'skolskaGodinaUpisa_id' => $f['skolskaGodina']->id,
            'statusUpisa_id' => $f['statusStudiranja']->id,
        ]);

        $this->enrollStudent($f['zapisnik'], $f['kandidat']);
        $this->enrollStudent($f['zapisnik'], $kandidat2);

        $result = $this->service->removeStudentFromZapisnik($f['zapisnik']->id, $f['kandidat']->id);

        $this->assertFalse($result);
        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', ['id' => $f['zapisnik']->id]);
    }

    public function test_remove_last_student_deletes_zapisnik_and_returns_true(): void
    {
        $f = $this->buildFixtures();
        $this->enrollStudent($f['zapisnik'], $f['kandidat']);

        $result = $this->service->removeStudentFromZapisnik($f['zapisnik']->id, $f['kandidat']->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('zapisnik_o_polaganju_ispita', ['id' => $f['zapisnik']->id]);
    }
}
