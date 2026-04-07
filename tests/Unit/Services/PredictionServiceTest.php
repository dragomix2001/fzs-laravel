<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Kandidat;
use App\Models\PrijavaIspita;
use App\Services\PredictionService;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PredictionServiceTest extends TestCase
{
    use DatabaseTransactions;

    private PredictionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PredictionService::class);

        TipStudija::factory()->create();
        StatusStudiranja::factory()->create();
        StudijskiProgram::factory()->create();
    }

    #[Test]
    public function test_predict_student_success_returns_all_required_fields(): void
    {
        $kandidat = Kandidat::factory()->create();

        $result = $this->service->predictStudentSuccess($kandidat->id);

        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertArrayHasKey('risk_level', $result);
        $this->assertArrayHasKey('recommendations', $result);
        $this->assertArrayHasKey('prediction', $result);
    }

    #[Test]
    public function test_predict_student_success_returns_error_for_nonexistent_student(): void
    {
        $result = $this->service->predictStudentSuccess(99999);

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('пронађен', $result['error']);
    }

    #[Test]
    public function test_predict_student_success_contains_student_info(): void
    {
        $kandidat = Kandidat::factory()->create([
            'imeKandidata' => 'Marko',
            'prezimeKandidata' => 'Markovic',
            'email' => 'marko@example.com',
        ]);

        $result = $this->service->predictStudentSuccess($kandidat->id);

        $this->assertEquals($kandidat->id, $result['student']['id']);
        $this->assertEquals('Marko', $result['student']['ime']);
        $this->assertEquals('Markovic', $result['student']['prezime']);
        $this->assertEquals('marko@example.com', $result['student']['email']);
    }

    #[Test]
    public function test_get_student_stats_with_no_exams(): void
    {
        $kandidat = Kandidat::factory()->create();

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $stats = $result['statistics'];

        $this->assertEquals(0, $stats['total_exams']);
        $this->assertEquals(0, $stats['passed_exams']);
        $this->assertEquals(0, $stats['failed_exams']);
        $this->assertEquals(0, $stats['pass_rate']);
        $this->assertEquals(0, $stats['average_grade']);
    }

    #[Test]
    public function test_get_student_stats_with_exams(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 10);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $stats = $result['statistics'];

        $this->assertEquals(10, $stats['total_exams']);
        $this->assertGreaterThanOrEqual(0, $stats['passed_exams']);
        $this->assertGreaterThanOrEqual(0, $stats['failed_exams']);
    }

    #[Test]
    public function test_calculate_risk_level_low_risk(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 10);
        $this->createPolozeniIspiti($kandidat, 10, 9);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $riskLevel = $result['risk_level'];

        $this->assertEquals('low', $riskLevel['level']);
        $this->assertEquals('success', $riskLevel['color']);
        $this->assertLessThan(25, $riskLevel['score']);
    }

    #[Test]
    public function test_calculate_risk_level_medium_risk(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 10);
        $this->createPolozeniIspiti($kandidat, 6, 7);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $riskLevel = $result['risk_level'];

        $this->assertEquals('medium', $riskLevel['level']);
        $this->assertGreaterThanOrEqual(25, $riskLevel['score']);
        $this->assertLessThan(50, $riskLevel['score']);
    }

    #[Test]
    public function test_calculate_risk_level_high_risk(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 10);
        $this->createPolozeniIspiti($kandidat, 2, 6);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $riskLevel = $result['risk_level'];

        $this->assertEquals('high', $riskLevel['level']);
        $this->assertGreaterThanOrEqual(50, $riskLevel['score']);
    }

    #[Test]
    public function test_risk_level_includes_factors(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 5);
        $this->createPolozeniIspiti($kandidat, 2, 6);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $riskLevel = $result['risk_level'];

        $this->assertIsArray($riskLevel['factors']);
        $this->assertNotEmpty($riskLevel['factors']);
    }

    #[Test]
    public function test_generate_recommendations_high_risk(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 5);
        $this->createPolozeniIspiti($kandidat, 1, 6);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $recommendations = $result['recommendations'];

        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);

        $highPriority = collect($recommendations)->filter(fn ($r) => $r['priority'] === 'high');
        $this->assertNotEmpty($highPriority);
    }

    #[Test]
    public function test_generate_recommendations_low_risk(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 10);
        $this->createPolozeniIspiti($kandidat, 10, 9);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $recommendations = $result['recommendations'];

        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);

        $lowPriority = collect($recommendations)->filter(fn ($r) => $r['priority'] === 'low');
        $this->assertNotEmpty($lowPriority);
    }

    #[Test]
    public function test_generate_prediction_contains_required_fields(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 5);
        $this->createPolozeniIspiti($kandidat, 3, 8);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $prediction = $result['prediction'];

        $this->assertArrayHasKey('graduation_probability', $prediction);
        $this->assertArrayHasKey('estimated_remaining_semesters', $prediction);
        $this->assertArrayHasKey('success_factors', $prediction);
    }

    #[Test]
    public function test_graduation_probability_is_percentage(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 5);
        $this->createPolozeniIspiti($kandidat, 3, 8);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $probability = $result['prediction']['graduation_probability'];

        $this->assertGreaterThanOrEqual(0, $probability);
        $this->assertLessThanOrEqual(100, $probability);
    }

    #[Test]
    public function test_estimate_remaining_semesters_calculation(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 10);
        $this->createPolozeniIspiti($kandidat, 10, 8);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $remainingSemesters = $result['prediction']['estimated_remaining_semesters'];

        $this->assertIsInt($remainingSemesters);
        $this->assertGreaterThanOrEqual(0, $remainingSemesters);
    }

    #[Test]
    public function test_identify_success_factors_with_high_performance(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 10);
        $this->createPolozeniIspiti($kandidat, 10, 9);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $factors = $result['prediction']['success_factors'];

        $this->assertIsArray($factors);
        $this->assertNotEmpty($factors);
    }

    #[Test]
    public function test_identify_success_factors_with_low_performance(): void
    {
        $kandidat = Kandidat::factory()->create();

        $this->createPrijavaIspita($kandidat, 5);
        $this->createPolozeniIspiti($kandidat, 2, 6);

        $result = $this->service->predictStudentSuccess($kandidat->id);
        $factors = $result['prediction']['success_factors'];

        $this->assertIsArray($factors);
    }

    #[Test]
    public function test_get_class_statistics_returns_all_fields(): void
    {
        Kandidat::factory()->count(5)->create();

        $result = $this->service->getClassStatistics();

        $this->assertArrayHasKey('total_students', $result);
        $this->assertArrayHasKey('exam_statistics', $result);
        $this->assertArrayHasKey('risk_distribution', $result);
        $this->assertArrayHasKey('overall_pass_rate', $result);
    }

    #[Test]
    public function test_get_class_statistics_counts_students(): void
    {
        Kandidat::factory()->count(5)->create();

        $result = $this->service->getClassStatistics();

        $this->assertEquals(5, $result['total_students']);
    }

    #[Test]
    public function test_get_class_statistics_risk_distribution(): void
    {
        Kandidat::factory()->count(3)->create();

        $result = $this->service->getClassStatistics();
        $riskDistribution = $result['risk_distribution'];

        $this->assertArrayHasKey('high', $riskDistribution);
        $this->assertArrayHasKey('medium', $riskDistribution);
        $this->assertArrayHasKey('low', $riskDistribution);
    }

    private function createPrijavaIspita(Kandidat $kandidat, int $count): void
    {
        PrijavaIspita::factory()->count($count)->create(['kandidat_id' => $kandidat->id]);
    }

    private function createPolozeniIspiti(Kandidat $kandidat, int $count, int $ocena): void
    {
        for ($i = 0; $i < $count; $i++) {
            \DB::table('polozeni_ispiti')->insert([
                'kandidat_id' => $kandidat->id,
                'konacnaOcena' => $ocena,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
