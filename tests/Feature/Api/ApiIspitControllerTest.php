<?php

namespace Tests\Feature\Api;

use App\Models\Predmet;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiIspitControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Predmet $predmet;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        $this->user = User::create([
            'name' => 'Api Ispit Admin',
            'email' => 'api.ispit@test.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->predmet = Predmet::factory()->create([
            'naziv' => 'Anatomija',
        ]);
    }

    public function test_index_returns_subject_collection(): void
    {
        $response = $this->getJson('/api/v1/ispiti');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->predmet->id,
            'naziv' => 'Anatomija',
        ]);
    }

    public function test_show_returns_single_subject(): void
    {
        $response = $this->getJson('/api/v1/ispiti/'.$this->predmet->id);

        $response->assertStatus(200);
        $response->assertJsonPath('id', $this->predmet->id);
        $response->assertJsonPath('naziv', 'Anatomija');
    }

    public function test_store_creates_subject_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ispiti', [
            'naziv' => 'Fiziologija',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('naziv', 'Fiziologija');
        $this->assertDatabaseHas('predmet', [
            'naziv' => 'Fiziologija',
        ]);
    }

    public function test_store_requires_authenticated_user(): void
    {
        $response = $this->postJson('/api/v1/ispiti', [
            'naziv' => 'Biohemija',
        ]);

        $response->assertStatus(401);
    }

    public function test_update_changes_subject_name_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/ispiti/'.$this->predmet->id, [
            'naziv' => 'Anatomija 2',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('naziv', 'Anatomija 2');
        $this->assertDatabaseHas('predmet', [
            'id' => $this->predmet->id,
            'naziv' => 'Anatomija 2',
        ]);
    }

    public function test_destroy_deletes_subject_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/ispiti/'.$this->predmet->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('predmet', [
            'id' => $this->predmet->id,
        ]);
    }
}
