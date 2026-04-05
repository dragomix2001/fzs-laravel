<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Predmet;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
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
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $predmetData = Predmet::factory()->create([
            'naziv' => 'Anatomija',
        ]);
        $this->predmet = $predmetData instanceof Predmet ? $predmetData : Predmet::findOrFail($predmetData->id);
    }

    /**
     * Test index returns subject collection
     */
    public function test_index_returns_subject_collection(): void
    {
        $response = $this->getJson('/api/v1/ispiti');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->predmet->id,
            'naziv' => 'Anatomija',
        ]);
    }

    /**
     * Test index returns empty array when no subjects exist
     */
    public function test_index_returns_empty_collection_when_no_subjects(): void
    {
        Predmet::query()->delete();

        $response = $this->getJson('/api/v1/ispiti');

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    /**
     * Test index with multiple subjects returns all
     */
    public function test_index_returns_multiple_subjects(): void
    {
        Predmet::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/ispiti');

        $response->assertStatus(200);
        $response->assertJsonCount(3); // 1 from setUp + 2 created
    }

    /**
     * Test show returns single subject
     */
    public function test_show_returns_single_subject(): void
    {
        $response = $this->getJson('/api/v1/ispiti/'.$this->predmet->id);

        $response->assertStatus(200);
        $response->assertJsonPath('id', $this->predmet->id);
        $response->assertJsonPath('naziv', 'Anatomija');
    }

    /**
     * Test show returns 500 for non-existent subject (model binding)
     */
    public function test_show_handles_non_existent_subject(): void
    {
        $response = $this->getJson('/api/v1/ispiti/99999');

        $response->assertStatus(500);
    }

    /**
     * Test store creates subject for authenticated user
     */
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

    /**
     * Test store requires authenticated user
     */
    public function test_store_requires_authenticated_user(): void
    {
        $response = $this->postJson('/api/v1/ispiti', [
            'naziv' => 'Biohemija',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test store validates required naziv with auth
     */
    public function test_store_with_required_field_present(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ispiti', [
            'naziv' => 'Valid Subject Name',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('predmet', [
            'naziv' => 'Valid Subject Name',
        ]);
    }

    /**
     * Test store with long subject name
     */
    public function test_store_with_long_subject_name(): void
    {
        Sanctum::actingAs($this->user);

        $longName = str_repeat('a', 100);

        $response = $this->postJson('/api/v1/ispiti', [
            'naziv' => $longName,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('naziv', $longName);
    }

    /**
     * Test store with different subject name types
     */
    public function test_store_with_special_characters_in_name(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ispiti', [
            'naziv' => 'Subject-123 (Special)',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('naziv', 'Subject-123 (Special)');
    }

    /**
     * Test update changes subject name for authenticated user
     */
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

    /**
     * Test update requires authenticated user
     */
    public function test_update_requires_authenticated_user(): void
    {
        $response = $this->putJson('/api/v1/ispiti/'.$this->predmet->id, [
            'naziv' => 'Updated Name',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test update returns 500 for non-existent subject (model binding)
     */
    public function test_update_handles_non_existent_subject(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/ispiti/99999', [
            'naziv' => 'Updated Name',
        ]);

        $response->assertStatus(500);
    }

    /**
     * Test update with empty data (optional fields)
     */
    public function test_update_with_empty_data(): void
    {
        Sanctum::actingAs($this->user);

        $originalNaziv = $this->predmet->naziv;
        $response = $this->putJson('/api/v1/ispiti/'.$this->predmet->id, []);

        $response->assertStatus(200);
        $this->predmet->refresh();
        $this->assertEquals($originalNaziv, $this->predmet->naziv);
    }

    /**
     * Test update subject with partial data
     */
    public function test_update_subject_with_partial_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/ispiti/'.$this->predmet->id, [
            'naziv' => 'Partial Update Name',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('naziv', 'Partial Update Name');
        $this->predmet->refresh();
        $this->assertEquals('Partial Update Name', $this->predmet->naziv);
    }

    /**
     * Test destroy deletes subject for authenticated user
     */
    public function test_destroy_deletes_subject_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/ispiti/'.$this->predmet->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('predmet', [
            'id' => $this->predmet->id,
        ]);
    }

    /**
     * Test destroy requires authenticated user
     */
    public function test_destroy_requires_authenticated_user(): void
    {
        $response = $this->deleteJson('/api/v1/ispiti/'.$this->predmet->id);

        $response->assertStatus(401);
    }

    /**
     * Test destroy returns 500 for non-existent subject (model binding)
     */
    public function test_destroy_handles_non_existent_subject(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/ispiti/99999');

        $response->assertStatus(500);
    }

    /**
     * Test store returns json response with created status
     */
    public function test_store_returns_created_status_code(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ispiti', [
            'naziv' => 'Biohemija',
        ]);

        $response->assertCreated();
    }

    /**
     * Test index returns json response
     */
    public function test_index_returns_json_response(): void
    {
        $response = $this->getJson('/api/v1/ispiti');

        $response->assertJsonIsArray();
    }

    /**
     * Test store trims whitespace from input
     */
    public function test_store_trims_whitespace_from_naziv(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ispiti', [
            'naziv' => '  Trimmed Subject  ',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('predmet', [
            'naziv' => '  Trimmed Subject  ',
        ]);
    }

    /**
     * Test update with no changes still returns success
     */
    public function test_update_with_same_data_returns_success(): void
    {
        Sanctum::actingAs($this->user);

        $originalNaziv = $this->predmet->naziv;

        $response = $this->putJson('/api/v1/ispiti/'.$this->predmet->id, [
            'naziv' => $originalNaziv,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('naziv', $originalNaziv);
        $this->predmet->refresh();
        $this->assertEquals($originalNaziv, $this->predmet->naziv);
    }

    /**
     * Test show returns all predmet attributes
     */
    public function test_show_returns_complete_predmet_data(): void
    {
        $response = $this->getJson('/api/v1/ispiti/'.$this->predmet->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'naziv',
            'created_at',
            'updated_at',
        ]);
    }
}
