<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\WelcomeController;
use Tests\TestCase;

class WelcomeControllerTest extends TestCase
{
    public function test_welcome_controller_can_be_instantiated(): void
    {
        $controller = new WelcomeController;
        $this->assertInstanceOf(WelcomeController::class, $controller);
    }

    public function test_welcome_controller_index_returns_view(): void
    {
        $controller = new WelcomeController;
        $response = $controller->index();

        $this->assertNotNull($response);
    }

    public function test_welcome_controller_has_guest_middleware(): void
    {
        $controller = new WelcomeController;
        $middleware = $controller->getMiddleware();

        $this->assertNotEmpty($middleware);
        $this->assertContains('guest', array_column($middleware, 'middleware'));
    }

    public function test_welcome_view_renders(): void
    {
        $content = \view('welcome')->render();
        $this->assertNotEmpty($content);
    }
}
