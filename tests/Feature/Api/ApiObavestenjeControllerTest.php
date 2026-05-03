<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\ObavestenjeController;
use App\Models\Obavestenje;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApiObavestenjeControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_index_returns_json_response(): void
    {
        $user = User::factory()->create();

        $controller = new ObavestenjeController;
        $request = Request::create('/api/v1/obavestenja', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $controller->index($request);

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('message', $data);
    }

    public function test_index_filters_by_tip(): void
    {
        $user = User::factory()->create();

        $controller = new ObavestenjeController;
        $request = Request::create('/api/v1/obavestenja', 'GET', ['tip' => 'opste']);
        $request->setUserResolver(fn () => $user);

        $response = $controller->index($request);

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
    }

    public function test_show_returns_single_obavestenje(): void
    {
        $obavestenje = Obavestenje::create([
            'naslov' => 'Test naslov',
            'sadrzaj' => 'Test sadrzaj',
            'tip' => 'opste',
            'aktivan' => true,
            'datum_objave' => now(),
        ]);

        $controller = new ObavestenjeController;
        $response = $controller->show($obavestenje);

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($obavestenje->id, $data['data']['id']);
    }

    public function test_javna_returns_public_obavestenja(): void
    {
        $controller = new ObavestenjeController;
        $response = $controller->javna();

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertSame('Javna обавештења успешно учитана', $data['message']);
    }

    public function test_javna_only_returns_active_opste(): void
    {
        // Create inactive obavestenje
        Obavestenje::create([
            'naslov' => 'Inactive',
            'sadrzaj' => 'content',
            'tip' => 'opste',
            'aktivan' => false,
            'datum_objave' => now(),
        ]);

        // Create active opste
        $active = Obavestenje::create([
            'naslov' => 'Active',
            'sadrzaj' => 'content',
            'tip' => 'opste',
            'aktivan' => true,
            'datum_objave' => now(),
        ]);

        $controller = new ObavestenjeController;
        $response = $controller->javna();
        $data = json_decode($response->getContent(), true);

        $ids = array_column($data['data'], 'id');
        $this->assertContains($active->id, $ids);
    }
}
