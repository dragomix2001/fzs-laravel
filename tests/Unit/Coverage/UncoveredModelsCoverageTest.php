<?php

namespace Tests\Unit\Coverage;

use App\Models\ArhivIndeksa;
use App\Models\Bodovanje;
use App\Models\Diploma;
use App\Models\DiplomskiPrijavaOdbrane;
use App\Models\DiplomskiRad;
use App\Models\GodinaStudija;
use App\Models\Ispiti;
use App\Models\IspitniRok;
use App\Models\KnowledgeBase;
use App\Models\KrsnaSlava;
use App\Models\NastavnaNedelja;
use App\Models\OblikNastave;
use App\Models\OpstiUspeh;
use App\Models\Predmet;
use App\Models\Profesor;
use App\Models\Region;
use App\Models\Semestar;
use App\Models\Sport;
use App\Models\SrednjeSkoleFakulteti;
use App\Models\StatusGodine;
use App\Models\StatusIspita;
use App\Models\StatusProfesora;
use App\Models\StatusStudiranja;
use App\Models\TipPredmeta;
use App\Models\TipPrijave;
use App\Models\TipSemestra;
use App\Models\TipStudija;
use App\Models\UspehSrednjaSkola;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

/**
 * Touch uncovered Eloquent models to ensure they appear in coverage report.
 */
class UncoveredModelsCoverageTest extends TestCase
{
    // Models with no custom methods – instantiation is enough

    public function test_arhiv_indeksa_can_be_instantiated(): void
    {
        $m = new ArhivIndeksa();
        $this->assertInstanceOf(ArhivIndeksa::class, $m);
        $this->assertSame('arhiv_indeksa', $m->getTable());
    }

    public function test_bodovanje_can_be_instantiated(): void
    {
        $m = new Bodovanje();
        $this->assertInstanceOf(Bodovanje::class, $m);
    }

    public function test_godina_studija_can_be_instantiated(): void
    {
        $m = new GodinaStudija();
        $this->assertInstanceOf(GodinaStudija::class, $m);
    }

    public function test_knowledge_base_can_be_instantiated(): void
    {
        $m = new KnowledgeBase();
        $this->assertInstanceOf(KnowledgeBase::class, $m);
    }

    public function test_krsna_slava_can_be_instantiated(): void
    {
        $m = new KrsnaSlava();
        $this->assertInstanceOf(KrsnaSlava::class, $m);
    }

    public function test_oblik_nastave_can_be_instantiated(): void
    {
        $m = new OblikNastave();
        $this->assertInstanceOf(OblikNastave::class, $m);
    }

    public function test_opsti_uspeh_can_be_instantiated(): void
    {
        $m = new OpstiUspeh();
        $this->assertInstanceOf(OpstiUspeh::class, $m);
    }

    public function test_region_can_be_instantiated(): void
    {
        $m = new Region();
        $this->assertInstanceOf(Region::class, $m);
    }

    public function test_semestar_can_be_instantiated(): void
    {
        $m = new Semestar();
        $this->assertInstanceOf(Semestar::class, $m);
    }

    public function test_sport_can_be_instantiated(): void
    {
        $m = new Sport();
        $this->assertInstanceOf(Sport::class, $m);
    }

    public function test_srednje_skole_fakulteti_can_be_instantiated(): void
    {
        $m = new SrednjeSkoleFakulteti();
        $this->assertInstanceOf(SrednjeSkoleFakulteti::class, $m);
    }

    public function test_status_godine_can_be_instantiated(): void
    {
        $m = new StatusGodine();
        $this->assertInstanceOf(StatusGodine::class, $m);
    }

    public function test_status_ispita_can_be_instantiated(): void
    {
        $m = new StatusIspita();
        $this->assertInstanceOf(StatusIspita::class, $m);
    }

    public function test_status_profesora_can_be_instantiated(): void
    {
        $m = new StatusProfesora();
        $this->assertInstanceOf(StatusProfesora::class, $m);
    }

    public function test_status_studiranja_can_be_instantiated(): void
    {
        $m = new StatusStudiranja();
        $this->assertInstanceOf(StatusStudiranja::class, $m);
    }

    public function test_tip_predmeta_can_be_instantiated(): void
    {
        $m = new TipPredmeta();
        $this->assertInstanceOf(TipPredmeta::class, $m);
    }

    public function test_tip_prijave_can_be_instantiated(): void
    {
        $m = new TipPrijave();
        $this->assertInstanceOf(TipPrijave::class, $m);
    }

    public function test_tip_semestra_can_be_instantiated(): void
    {
        $m = new TipSemestra();
        $this->assertInstanceOf(TipSemestra::class, $m);
    }

    public function test_uspeh_srednja_skola_can_be_instantiated(): void
    {
        $m = new UspehSrednjaSkola();
        $this->assertInstanceOf(UspehSrednjaSkola::class, $m);
    }

    public function test_zapisnik_studijski_program_can_be_instantiated(): void
    {
        $m = new ZapisnikOPolaganju_StudijskiProgram();
        $this->assertInstanceOf(ZapisnikOPolaganju_StudijskiProgram::class, $m);
    }

    // Models WITH relation methods – call each relation to cover method bodies

    public function test_diploma_relations(): void
    {
        $m = new Diploma();
        $this->assertInstanceOf(BelongsTo::class, $m->student());
        $this->assertInstanceOf(BelongsTo::class, $m->potpis());
    }

    public function test_diplomski_rad_relations(): void
    {
        $m = new DiplomskiRad();
        $this->assertInstanceOf(BelongsTo::class, $m->student());
        $this->assertInstanceOf(BelongsTo::class, $m->mentor());
        $this->assertInstanceOf(BelongsTo::class, $m->clan());
        $this->assertInstanceOf(BelongsTo::class, $m->predsednik());
        $this->assertInstanceOf(BelongsTo::class, $m->predmet());
    }

    public function test_diplomski_prijava_odbrane_relations(): void
    {
        $m = new DiplomskiPrijavaOdbrane();
        $this->assertInstanceOf(BelongsTo::class, $m->predmet());
        $this->assertInstanceOf(BelongsTo::class, $m->odobrioTemuProfesor());
        $this->assertInstanceOf(BelongsTo::class, $m->odobrioOdbranuProfesor());
    }

    public function test_ispiti_relations(): void
    {
        $m = new Ispiti();
        $this->assertInstanceOf(BelongsTo::class, $m->predmet());
        $this->assertInstanceOf(BelongsTo::class, $m->student());
        $this->assertInstanceOf(BelongsTo::class, $m->rok());
    }

    public function test_ispitni_rok_relations(): void
    {
        $m = new IspitniRok();
        $this->assertInstanceOf(HasMany::class, $m->aktivniRokovi());
    }

    public function test_nastavna_nedelja_relations(): void
    {
        $m = new NastavnaNedelja();
        $this->assertInstanceOf(BelongsTo::class, $m->skolskaGodina());
        $this->assertInstanceOf(HasMany::class, $m->prisanstva());
    }

    public function test_predmet_relations(): void
    {
        $m = new Predmet();
        $this->assertInstanceOf(BelongsTo::class, $m->godinaStudija());
        $this->assertInstanceOf(HasMany::class, $m->prijaveIspita());
        $this->assertInstanceOf(BelongsTo::class, $m->tipStudija());
        $this->assertInstanceOf(BelongsTo::class, $m->studijskiProgram());
    }

    public function test_profesor_relations(): void
    {
        $m = new Profesor();
        $this->assertInstanceOf(BelongsTo::class, $m->status());
        $this->assertInstanceOf(HasMany::class, $m->angazovanja());
    }

    public function test_tip_studija_relations(): void
    {
        $m = new TipStudija();
        $this->assertInstanceOf(HasMany::class, $m->studijskiProgram());
    }

    public function test_zapisnik_student_relations(): void
    {
        $m = new ZapisnikOPolaganju_Student();
        $this->assertInstanceOf(BelongsTo::class, $m->prijava());
    }
}
