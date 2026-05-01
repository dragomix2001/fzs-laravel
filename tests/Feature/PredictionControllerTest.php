<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\User;
use App\Services\PredictionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PredictionControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Kandidat $student;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $this->seedLegacyLookups();

        $tipStudija = TipStudija::query()->firstOrCreate(
            ['id' => 1],
            [
                'naziv' => 'Osnovne akademske studije',
                'skrNaziv' => 'OAS',
                'indikatorAktivan' => 1,
            ]
        );

        $program = StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $skolskaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
            'aktivan' => 1,
        ]);

        $statusStudiranja = StatusStudiranja::query()->firstOrCreate(
            ['id' => 3],
            ['naziv' => 'upisan', 'indikatorAktivan' => 1]
        );

        $this->user = User::create([
            'name' => 'Prediction Admin',
            'email' => 'prediction_admin@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->student = Kandidat::factory()->create([
            'email' => 'prediction_student@test.com',
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $program->id,
            'skolskaGodinaUpisa_id' => $skolskaGodina->id,
            'godinaStudija_id' => 1,
            'statusUpisa_id' => $statusStudiranja->id,
            'indikatorAktivan' => 1,
        ]);

        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_student_prediction_redirects_back_with_error_when_service_fails(): void
    {
        $this->mockPredictionService()
            ->shouldReceive('predictStudentSuccess')
            ->once()
            ->with($this->student->id)
            ->andReturn(['error' => 'Prediction unavailable']);

        $response = $this->from('/prediction')->get('/prediction/student/'.$this->student->id);

        $response->assertRedirect('/prediction');
        $response->assertSessionHas('error', 'Prediction unavailable');
    }

    public function test_index_returns_prediction_view_with_students(): void
    {
        $this->mockPredictionService();

        $response = $this->get('/prediction');

        $response->assertStatus(200);
        $response->assertViewIs('prediction.index');
        $response->assertViewHas('students');
    }

    public function test_student_prediction_returns_view_when_service_succeeds(): void
    {
        $payload = [
            'student' => [
                'ime' => 'Student',
                'prezime' => 'Test',
            ],
            'risk_level' => [
                'color' => 'success',
                'label' => 'Nizak rizik',
                'score' => 15,
                'factors' => [],
            ],
            'prediction' => [
                'graduation_probability' => 85,
                'estimated_remaining_semesters' => 3,
                'success_factors' => ['Kontinuitet'],
            ],
            'statistics' => [
                'pass_rate' => 80,
                'passed_exams' => 8,
                'total_exams' => 10,
                'failed_exams' => 2,
                'average_grade' => 8.5,
                'recent_pass_rate' => 75,
            ],
            'recommendations' => [
                [
                    'priority' => 'low',
                    'action' => 'Nastaviti tempom',
                    'reason' => 'Rezultati su stabilni',
                ],
            ],
        ];

        $this->mockPredictionService()
            ->shouldReceive('predictStudentSuccess')
            ->once()
            ->with($this->student->id)
            ->andReturn($payload);

        $response = $this->get('/prediction/student/'.$this->student->id);

        $response->assertStatus(200);
        $response->assertViewIs('prediction.student');
        $response->assertViewHas('prediction', $payload);
    }

    public function test_class_statistics_redirects_back_with_error_when_service_fails(): void
    {
        $this->mockPredictionService()
            ->shouldReceive('getClassStatistics')
            ->once()
            ->andReturn(['error' => 'Statistics unavailable']);

        $response = $this->from('/prediction')->get('/prediction/statistics');

        $response->assertRedirect('/prediction');
        $response->assertSessionHas('error', 'Statistics unavailable');
    }

    public function test_class_statistics_returns_view_when_service_succeeds(): void
    {
        $payload = [
            'total_students' => 1,
            'overall_pass_rate' => 100.0,
            'exam_statistics' => [
                'total_passed' => 5,
                'average_grade' => 9.4,
                'grade_distribution' => [
                    'excellent' => 3,
                    'very_good' => 1,
                    'good' => 1,
                    'sufficient' => 0,
                ],
            ],
            'risk_distribution' => [
                'high' => 0,
                'medium' => 0,
                'low' => 1,
            ],
        ];

        $this->mockPredictionService()
            ->shouldReceive('getClassStatistics')
            ->once()
            ->andReturn($payload);

        $response = $this->get('/prediction/statistics');

        $response->assertStatus(200);
        $response->assertViewIs('prediction.statistics');
        $response->assertViewHas('statistics', $payload);
    }

    public function test_api_prediction_returns_not_found_when_service_reports_error(): void
    {
        $this->mockPredictionService()
            ->shouldReceive('predictStudentSuccess')
            ->once()
            ->with($this->student->id)
            ->andReturn(['error' => 'Student missing']);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/prediction/student/'.$this->student->id);

        $response->assertStatus(404);
        $response->assertExactJson(['error' => 'Student missing']);
    }

    public function test_api_prediction_statistics_returns_service_payload(): void
    {
        $payload = [
            'total_students' => 2,
            'overall_pass_rate' => 75.5,
            'exam_statistics' => [
                'total_passed' => 5,
                'average_grade' => 8.4,
                'grade_distribution' => [
                    'excellent' => 2,
                    'very_good' => 2,
                    'good' => 1,
                    'sufficient' => 0,
                ],
            ],
            'risk_distribution' => [
                'high' => 0,
                'medium' => 1,
                'low' => 1,
            ],
        ];

        $this->mockPredictionService()
            ->shouldReceive('getClassStatistics')
            ->once()
            ->andReturn($payload);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/prediction/statistics');

        $response->assertStatus(200);
        $response->assertExactJson($payload);
    }

    public function test_web_api_student_prediction_endpoint_returns_payload(): void
    {
        $payload = [
            'student' => [
                'id' => $this->student->id,
                'ime' => 'Prediction',
                'prezime' => 'Student',
            ],
        ];

        $this->mockPredictionService()
            ->shouldReceive('predictStudentSuccess')
            ->once()
            ->with($this->student->id)
            ->andReturn($payload);

        $response = $this->getJson('/api/prediction/student/'.$this->student->id);

        $response->assertStatus(200);
        $response->assertExactJson($payload);
    }

    private function mockPredictionService(): MockInterface
    {
        $mock = Mockery::mock(PredictionService::class);
        $this->instance(PredictionService::class, $mock);

        return $mock;
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
    }
}
