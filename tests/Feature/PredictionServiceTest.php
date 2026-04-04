<?php

declare(strict_types=1);

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
use App\Services\PredictionService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PredictionServiceTest extends TestCase
{
    use DatabaseTransactions;

    private PredictionService $predictionService;

    private TipStudija $tipStudija;

    private StudijskiProgram $program;

    private SkolskaGodUpisa $skolskaGodina;

    private StatusStudiranja $statusStudiranja;

    private Profesor $profesor;

    private AktivniIspitniRokovi $rok;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $this->seedLegacyLookups();

        $this->predictionService = app(PredictionService::class);

        $this->tipStudija = TipStudija::factory()->create([
            'id' => 1,
            'naziv' => 'Osnovne akademske studije',
            'skrNaziv' => 'OAS',
            'indikatorAktivan' => 1,
        ]);

        $this->program = StudijskiProgram::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->skolskaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
            'aktivan' => 1,
        ]);

        $this->statusStudiranja = StatusStudiranja::query()->firstOrCreate(
            ['id' => 3],
            ['naziv' => 'upisan', 'indikatorAktivan' => 1]
        );

        $this->profesor = Profesor::factory()->create([
            'status_id' => 1,
            'indikatorAktivan' => 1,
        ]);

        DB::table('ispitni_rok')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Januarski',
            'indikatorAktivan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->rok = AktivniIspitniRokovi::create([
            'rok_id' => 1,
            'naziv' => 'Januarski rok',
            'pocetak' => now()->startOfMonth(),
            'kraj' => now()->endOfMonth(),
            'tipRoka_id' => 1,
            'komentar' => 'Aktivni rok za testove',
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_predict_student_success_returns_high_risk_prediction_for_struggling_student(): void
    {
        $student = $this->createStudent('risk@test.com');
        $predmetProgram = $this->createPredmetProgram($student, 'Anatomija');

        $this->createExamRegistration($student, $predmetProgram, now()->subMonths(5));
        $this->createExamRegistration($student, $predmetProgram, now()->subMonths(4));
        $this->createExamRegistration($student, $predmetProgram, now()->subMonths(2));
        $passedRegistration = $this->createExamRegistration($student, $predmetProgram, now()->subMonths(8));
        $this->createPassedExam($student, $predmetProgram, 6, now()->subMonths(8), $passedRegistration);

        $prediction = $this->predictionService->predictStudentSuccess($student->id);

        $this->assertSame($student->id, $prediction['student']['id']);
        $this->assertSame('high', $prediction['risk_level']['level']);
        $this->assertSame('Висок ризик', $prediction['risk_level']['label']);
        $this->assertSame(65, $prediction['risk_level']['score']);
        $this->assertSame(25.0, $prediction['statistics']['pass_rate']);
        $this->assertSame(0.0, $prediction['statistics']['recent_pass_rate']);
        $this->assertSame(1, $prediction['statistics']['passed_exams']);
        $this->assertSame(3, $prediction['statistics']['failed_exams']);
        $this->assertSame(15, $prediction['prediction']['graduation_probability']);
        $this->assertSame(39, $prediction['prediction']['estimated_remaining_semesters']);
        $this->assertContains('Ниска пролазност (25%)', $prediction['risk_level']['factors']);
        $this->assertContains('Ниска просечна оцена (6)', $prediction['risk_level']['factors']);
        $this->assertContains('Опадајући тренд у последњих 6 месеци', $prediction['risk_level']['factors']);
        $this->assertSame('Контактирати студента за индивидуални састанак', $prediction['recommendations'][0]['action']);
        $this->assertSame([], $prediction['prediction']['success_factors']);
    }

    public function test_predict_student_success_returns_low_risk_prediction_for_consistent_student(): void
    {
        $student = $this->createStudent('steady@test.com');
        $predmetProgram = $this->createPredmetProgram($student, 'Biomehanika');

        $firstRegistration = $this->createExamRegistration($student, $predmetProgram, now()->subMonths(7));
        $secondRegistration = $this->createExamRegistration($student, $predmetProgram, now()->subMonths(5));
        $thirdRegistration = $this->createExamRegistration($student, $predmetProgram, now()->subMonths(2));

        $this->createPassedExam($student, $predmetProgram, 9, now()->subMonths(7), $firstRegistration);
        $this->createPassedExam($student, $predmetProgram, 10, now()->subMonths(5), $secondRegistration);
        $this->createPassedExam($student, $predmetProgram, 8, now()->subMonths(2), $thirdRegistration);

        $prediction = $this->predictionService->predictStudentSuccess($student->id);

        $this->assertSame('low', $prediction['risk_level']['level']);
        $this->assertSame('Низак ризик', $prediction['risk_level']['label']);
        $this->assertSame(0, $prediction['risk_level']['score']);
        $this->assertSame(100.0, $prediction['statistics']['pass_rate']);
        $this->assertSame(100.0, $prediction['statistics']['recent_pass_rate']);
        $this->assertSame(9.0, $prediction['statistics']['average_grade']);
        $this->assertSame(95, $prediction['prediction']['graduation_probability']);
        $this->assertSame(37, $prediction['prediction']['estimated_remaining_semesters']);
        $this->assertSame('Наставити са редовним праћењем', $prediction['recommendations'][0]['action']);
        $this->assertContains('Висока пролазност', $prediction['prediction']['success_factors']);
        $this->assertContains('Висока просечна оцена', $prediction['prediction']['success_factors']);
        $this->assertContains('Стабилан или растући тренд', $prediction['prediction']['success_factors']);
    }

    public function test_predict_student_success_returns_not_found_error_for_missing_student(): void
    {
        $prediction = $this->predictionService->predictStudentSuccess(999999);

        $this->assertSame(['error' => 'Студент није пронађен'], $prediction);
    }

    public function test_get_class_statistics_returns_aggregated_exam_and_risk_distribution_data(): void
    {
        $highRiskStudent = $this->createStudent('aggregate-high@test.com');
        $lowRiskStudent = $this->createStudent('aggregate-low@test.com');

        $highRiskPredmet = $this->createPredmetProgram($highRiskStudent, 'Fiziologija');
        $lowRiskPredmet = $this->createPredmetProgram($lowRiskStudent, 'Psihologija sporta');

        $this->createExamRegistration($highRiskStudent, $highRiskPredmet, now()->subMonths(4));
        $this->createExamRegistration($highRiskStudent, $highRiskPredmet, now()->subMonths(2));
        $highRiskPassedRegistration = $this->createExamRegistration($highRiskStudent, $highRiskPredmet, now()->subMonths(8));
        $this->createPassedExam($highRiskStudent, $highRiskPredmet, 6, now()->subMonths(8), $highRiskPassedRegistration);

        $lowRiskFirstRegistration = $this->createExamRegistration($lowRiskStudent, $lowRiskPredmet, now()->subMonths(5));
        $lowRiskSecondRegistration = $this->createExamRegistration($lowRiskStudent, $lowRiskPredmet, now()->subMonths(3));
        $this->createPassedExam($lowRiskStudent, $lowRiskPredmet, 9, now()->subMonths(5), $lowRiskFirstRegistration);
        $this->createPassedExam($lowRiskStudent, $lowRiskPredmet, 8, now()->subMonths(3), $lowRiskSecondRegistration);

        $statistics = $this->predictionService->getClassStatistics();

        $this->assertSame(2, $statistics['total_students']);
        $this->assertSame(60.0, $statistics['overall_pass_rate']);
        $this->assertSame(3, $statistics['exam_statistics']['total_passed']);
        $this->assertSame(7.67, $statistics['exam_statistics']['average_grade']);
        $this->assertSame(1, $statistics['exam_statistics']['grade_distribution']['excellent']);
        $this->assertSame(1, $statistics['exam_statistics']['grade_distribution']['very_good']);
        $this->assertSame(0, $statistics['exam_statistics']['grade_distribution']['good']);
        $this->assertSame(1, $statistics['exam_statistics']['grade_distribution']['sufficient']);
        $this->assertSame(['high' => 1, 'medium' => 0, 'low' => 1], $statistics['risk_distribution']);
    }

    private function seedLegacyLookups(): void
    {
        DB::table('krsna_slava')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Sveti Nikola',
            'datumSlave' => '19.12.',
            'indikatorAktivan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('opsti_uspeh')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Odlican',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('opstina')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Beograd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mesto')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Beograd',
            'opstina_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('godina_studija')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tip_predmeta')->insertOrIgnore([
            'id' => 1,
            'naziv' => 'Obavezni',
            'skrNaziv' => 'OBV',
            'indikatorAktivan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createStudent(string $email): Kandidat
    {
        return Kandidat::factory()->create([
            'email' => $email,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => 1,
            'statusUpisa_id' => $this->statusStudiranja->id,
            'indikatorAktivan' => 1,
        ]);
    }

    private function createPredmetProgram(Kandidat $student, string $nazivPredmeta): PredmetProgram
    {
        $predmet = Predmet::factory()->create([
            'naziv' => $nazivPredmeta,
        ]);

        $tipPredmeta = TipPredmeta::query()->firstOrCreate(
            ['id' => 1],
            ['naziv' => 'Obavezni', 'skrNaziv' => 'OBV', 'indikatorAktivan' => 1]
        );

        return PredmetProgram::create([
            'predmet_id' => $predmet->id,
            'studijskiProgram_id' => $student->studijskiProgram_id,
            'tipStudija_id' => $student->tipStudija_id,
            'godinaStudija_id' => $student->godinaStudija_id,
            'tipPredmeta_id' => $tipPredmeta->id,
            'semestar' => 1,
            'espb' => 6,
            'predavanja' => 3,
            'vezbe' => 2,
            'skolskaGodina_id' => $student->skolskaGodinaUpisa_id,
            'statusPredmeta' => 1,
            'indikatorAktivan' => 1,
        ]);
    }

    private function createExamRegistration(Kandidat $student, PredmetProgram $predmetProgram, DateTimeInterface $createdAt): PrijavaIspita
    {
        return PrijavaIspita::create([
            'kandidat_id' => $student->id,
            'predmet_id' => $predmetProgram->id,
            'profesor_id' => $this->profesor->id,
            'rok_id' => $this->rok->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'datum2' => now()->addWeek()->toDateString(),
            'vreme' => '10:00:00',
            'tipPrijave_id' => 1,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function createPassedExam(Kandidat $student, PredmetProgram $predmetProgram, int $grade, DateTimeInterface $createdAt, ?PrijavaIspita $prijava = null): PolozeniIspiti
    {
        $prijava ??= $this->createExamRegistration($student, $predmetProgram, $createdAt);

        return PolozeniIspiti::create([
            'prijava_id' => $prijava->id,
            'zapisnik_id' => null,
            'kandidat_id' => $student->id,
            'predmet_id' => $predmetProgram->id,
            'ocenaPismeni' => $grade,
            'ocenaUsmeni' => $grade,
            'konacnaOcena' => $grade,
            'brojBodova' => $grade * 10,
            'statusIspita' => 1,
            'odluka_id' => 1,
            'indikatorAktivan' => 1,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
