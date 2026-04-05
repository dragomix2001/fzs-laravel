<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\ApiDocsController;
use Illuminate\View\View;
use Tests\TestCase;

class ApiDocsControllerTest extends TestCase
{
    public function test_index_returns_view_instance(): void
    {
        $controller = new ApiDocsController;
        $response = $controller->index();

        $this->assertInstanceOf(View::class, $response);
    }

    public function test_index_returns_api_docs_view(): void
    {
        $controller = new ApiDocsController;
        $response = $controller->index();

        $this->assertEquals('api.docs', $response->getName());
    }

    public function test_index_has_correct_view_path(): void
    {
        $controller = new ApiDocsController;
        $response = $controller->index();

        $this->assertStringContainsString('api', $response->getName());
        $this->assertStringContainsString('docs', $response->getName());
    }

    public function test_controller_index_method_exists(): void
    {
        $controller = new ApiDocsController;
        $this->assertTrue(method_exists($controller, 'index'));
    }
}
