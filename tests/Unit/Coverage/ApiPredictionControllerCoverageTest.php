<?php

declare(strict_types=1);

namespace Tests\Unit\Coverage;

use App\Http\Controllers\Api\PredictionController;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiPredictionControllerCoverageTest extends TestCase
{
    #[Test]
    public function constructor_and_student_prediction_success_path_are_covered(): void
    {
        $service = Mockery::mock(PredictionService::class);
        $service->shouldReceive('predictStudentSuccess')
            ->once()
            ->with(7)
            ->andReturn(['predicted_pass_rate' => 88.2]);

        $controller = new PredictionController($service);

        $response = $controller->studentPrediction(Request::create('/'), 7);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"predicted_pass_rate":88.2}', $response->getContent());
    }

    #[Test]
    public function statistics_and_student_prediction_error_path_are_covered(): void
    {
        $service = Mockery::mock(PredictionService::class);
        $service->shouldReceive('predictStudentSuccess')
            ->once()
            ->with(9)
            ->andReturn(['error' => 'not found']);
        $service->shouldReceive('getClassStatistics')
            ->once()
            ->andReturn(['total_students' => 2]);

        $controller = new PredictionController($service);

        $errorResponse = $controller->studentPrediction(Request::create('/'), 9);
        $this->assertSame(404, $errorResponse->getStatusCode());
        $this->assertSame('{"error":"not found"}', $errorResponse->getContent());

        $statsResponse = $controller->statistics();
        $this->assertSame(200, $statsResponse->getStatusCode());
        $this->assertSame('{"total_students":2}', $statsResponse->getContent());
    }
}
