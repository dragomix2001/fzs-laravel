<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteBootstrapTest extends TestCase
{
    public function test_standard_web_route_loader_preserves_active_routes_and_middleware(): void
    {
        $routes = Route::getRoutes();

        $documentReview = $routes->getByName('kandidat.documents.incomplete');
        $this->assertNotNull($documentReview);
        $this->assertSame('kandidat/documents/incomplete', $documentReview->uri());
        $this->assertContains('GET', $documentReview->methods());
        $this->assertContains('role:admin', $documentReview->gatherMiddleware());

        $saveWidgets = $routes->getByName('dashboard.widgets');
        $this->assertNotNull($saveWidgets);
        $this->assertSame('dashboard/widgets', $saveWidgets->uri());
        $this->assertContains('POST', $saveWidgets->methods());

        $this->assertNull($routes->getByName('dashboard.saveWidgets'));

        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('app', 'FZS Laravel');
    }
}
