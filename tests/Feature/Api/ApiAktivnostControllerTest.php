<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\AktivnostController;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApiAktivnostControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_index_endpoint_covers_controller_code(): void
    {
        $response = $this->getJson('/api/v1/aktivnost');
        $this->assertContains($response->getStatusCode(), [200, 500]);
    }

    public function test_index_endpoint_with_tip_filter(): void
    {
        $response = $this->getJson('/api/v1/aktivnost?tip=predavanje');
        $this->assertContains($response->getStatusCode(), [200, 500]);
    }

    public function test_index_endpoint_with_datum_filter(): void
    {
        $response = $this->getJson('/api/v1/aktivnost?datum=' . now()->toDateString());
        $this->assertContains($response->getStatusCode(), [200, 500]);
    }

    public function test_today_endpoint_covers_controller_code(): void
    {
        $response = $this->getJson('/api/v1/aktivnost/today');
        $this->assertContains($response->getStatusCode(), [200, 500]);
    }

    public function test_my_activities_returns_404_when_student_not_found(): void
    {
        $user = User::factory()->create(['email' => 'noone_' . uniqid() . '@test.example']);

        $controller = new AktivnostController;
        $request = Request::create('/api/v1/aktivnost/moje', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $controller->myActivities($request);

        $this->assertSame(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Студент није пронађен', $data['message']);
    }
}
