<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kandidat;
use App\Models\Sport;
use App\Models\SportskoAngazovanje;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SportskoAngazovanjeControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    public function test_unos_creates_angazovanje_with_session_kandidat_id(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();

        $this->actingAs($user)
            ->session(['id' => $kandidat->id])
            ->post('/sportskoAngazovanje/unos', [
                'nazivKluba' => 'FK Partizan',
                'odDoGodina' => '2018-2022',
                'ukupnoGodina' => 4,
                'sport_id' => $sport->id,
            ]);

        $this->assertDatabaseHas('sportsko_angazovanje', [
            'nazivKluba' => 'FK Partizan',
            'odDoGodina' => '2018-2022',
            'ukupnoGodina' => 4,
            'sport_id' => $sport->id,
            'kandidat_id' => $kandidat->id,
        ]);
    }

    public function test_unos_returns_back_after_creation(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();

        $response = $this->actingAs($user)
            ->session(['id' => $kandidat->id])
            ->post('/sportskoAngazovanje/unos', [
                'nazivKluba' => 'FK Red Star',
                'odDoGodina' => '2019-2023',
                'ukupnoGodina' => 4,
                'sport_id' => $sport->id,
            ]);

        $response->assertRedirect();
    }

    public function test_unos_creates_multiple_records(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport1 = Sport::factory()->create();
        $sport2 = Sport::factory()->create();

        $this->actingAs($user)
            ->session(['id' => $kandidat->id])
            ->post('/sportskoAngazovanje/unos', [
                'nazivKluba' => 'FK Voždovac',
                'odDoGodina' => '2015-2019',
                'ukupnoGodina' => 4,
                'sport_id' => $sport1->id,
            ]);

        $this->actingAs($user)
            ->session(['id' => $kandidat->id])
            ->post('/sportskoAngazovanje/unos', [
                'nazivKluba' => 'FK Čukarički',
                'odDoGodina' => '2019-2023',
                'ukupnoGodina' => 4,
                'sport_id' => $sport2->id,
            ]);

        $this->assertDatabaseCount('sportsko_angazovanje', 2);
    }

    public function test_edit_displays_edit_view_with_angazovanje_and_sports(): void
    {
        $user = User::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje = SportskoAngazovanje::factory()->create(['sport_id' => $sport->id]);

        $response = $this->actingAs($user)->get("/sportskoAngazovanje/{$angazovanje->id}/edit");

        $response->assertOk();
        $response->assertViewIs('sifarnici.editSportskoAngazovanje');
        $response->assertViewHas('angazovanje');
        $response->assertViewHas('sport');
    }

    public function test_edit_returns_correct_angazovanje(): void
    {
        $user = User::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje1 = SportskoAngazovanje::factory()->create(['sport_id' => $sport->id]);
        $angazovanje2 = SportskoAngazovanje::factory()->create(['sport_id' => $sport->id]);

        $response = $this->actingAs($user)->get("/sportskoAngazovanje/{$angazovanje1->id}/edit");

        $response->assertOk();
        $viewAngazovanje = $response->viewData('angazovanje');
        $this->assertEquals($angazovanje1->id, $viewAngazovanje->id);
        $this->assertNotEquals($angazovanje2->id, $viewAngazovanje->id);
    }

    public function test_edit_returns_all_sports(): void
    {
        $user = User::factory()->create();
        Sport::factory()->count(5)->create();
        $angazovanje = SportskoAngazovanje::factory()->create();

        $response = $this->actingAs($user)->get("/sportskoAngazovanje/{$angazovanje->id}/edit");

        $response->assertOk();
        $sports = $response->viewData('sport');
        $this->assertGreaterThanOrEqual(5, $sports->count());
    }

    public function test_update_modifies_existing_angazovanje(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje = SportskoAngazovanje::factory()->create([
            'kandidat_id' => $kandidat->id,
            'sport_id' => $sport->id,
        ]);

        $this->actingAs($user)
            ->session(['id' => $kandidat->id])
            ->patch("/sportskoAngazovanje/{$angazovanje->id}", [
                'nazivKluba' => 'Updated FK Name',
                'odDoGodina' => '2015-2019',
                'ukupnoGodina' => 4,
                'sport_id' => $sport->id,
            ]);

        $this->assertDatabaseHas('sportsko_angazovanje', [
            'id' => $angazovanje->id,
            'nazivKluba' => 'Updated FK Name',
            'odDoGodina' => '2015-2019',
        ]);
    }

    public function test_update_uses_session_kandidat_id(): void
    {
        $user = User::factory()->create();
        $kandidat1 = Kandidat::factory()->create();
        $kandidat2 = Kandidat::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje = SportskoAngazovanje::factory()->create([
            'kandidat_id' => $kandidat1->id,
            'sport_id' => $sport->id,
        ]);

        $this->actingAs($user)
            ->session(['id' => $kandidat2->id])
            ->patch("/sportskoAngazovanje/{$angazovanje->id}", [
                'nazivKluba' => 'Updated FK',
                'odDoGodina' => '2020-2024',
                'ukupnoGodina' => 4,
                'sport_id' => $sport->id,
            ]);

        $this->assertDatabaseHas('sportsko_angazovanje', [
            'id' => $angazovanje->id,
            'kandidat_id' => $kandidat2->id,
        ]);
    }

    public function test_update_returns_redirect_with_session_id(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje = SportskoAngazovanje::factory()->create([
            'kandidat_id' => $kandidat->id,
            'sport_id' => $sport->id,
        ]);

        $response = $this->actingAs($user)
            ->session(['id' => $kandidat->id])
            ->patch("/sportskoAngazovanje/{$angazovanje->id}", [
                'nazivKluba' => 'Updated FK',
                'odDoGodina' => '2020-2024',
                'ukupnoGodina' => 4,
                'sport_id' => $sport->id,
            ]);

        $response->assertRedirect("/sportskoAngazovanje/{$kandidat->id}");
    }

    public function test_update_preserves_other_records(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje1 = SportskoAngazovanje::factory()->create([
            'nazivKluba' => 'Original FK 1',
            'kandidat_id' => $kandidat->id,
        ]);
        $angazovanje2 = SportskoAngazovanje::factory()->create([
            'nazivKluba' => 'Original FK 2',
            'kandidat_id' => $kandidat->id,
        ]);

        $this->actingAs($user)
            ->session(['id' => $kandidat->id])
            ->patch("/sportskoAngazovanje/{$angazovanje1->id}", [
                'nazivKluba' => 'Updated FK 1',
                'odDoGodina' => '2020-2024',
                'ukupnoGodina' => 4,
                'sport_id' => $sport->id,
            ]);

        $this->assertDatabaseHas('sportsko_angazovanje', [
            'id' => $angazovanje1->id,
            'nazivKluba' => 'Updated FK 1',
        ]);
        $this->assertDatabaseHas('sportsko_angazovanje', [
            'id' => $angazovanje2->id,
            'nazivKluba' => 'Original FK 2',
        ]);
    }

    public function test_delete_removes_angazovanje(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje = SportskoAngazovanje::factory()->create([
            'kandidat_id' => $kandidat->id,
            'sport_id' => $sport->id,
        ]);

        $this->actingAs($user)->get("/sportskoAngazovanje/{$angazovanje->id}/delete");

        $this->assertDatabaseMissing('sportsko_angazovanje', [
            'id' => $angazovanje->id,
        ]);
    }

    public function test_delete_redirects_to_kandidat_sportskoangazovanje(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje = SportskoAngazovanje::factory()->create([
            'kandidat_id' => $kandidat->id,
            'sport_id' => $sport->id,
        ]);

        $response = $this->actingAs($user)->get("/sportskoAngazovanje/{$angazovanje->id}/delete");

        $response->assertRedirect("/kandidat/{$kandidat->id}/sportskoangazovanje");
    }

    public function test_delete_includes_sport_in_response(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje = SportskoAngazovanje::factory()->create([
            'kandidat_id' => $kandidat->id,
            'sport_id' => $sport->id,
        ]);

        $response = $this->actingAs($user)->get("/sportskoAngazovanje/{$angazovanje->id}/delete");

        $response->assertSessionHas('sport');
    }

    public function test_delete_includes_kandidat_in_response(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje = SportskoAngazovanje::factory()->create([
            'kandidat_id' => $kandidat->id,
            'sport_id' => $sport->id,
        ]);

        $response = $this->actingAs($user)->get("/sportskoAngazovanje/{$angazovanje->id}/delete");

        $response->assertSessionHas('kandidat');
    }

    public function test_delete_includes_sportsko_angazovanje_in_response(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje = SportskoAngazovanje::factory()->create([
            'kandidat_id' => $kandidat->id,
            'sport_id' => $sport->id,
        ]);

        $response = $this->actingAs($user)->get("/sportskoAngazovanje/{$angazovanje->id}/delete");

        $response->assertSessionHas('sportskoAngazovanje');
    }

    public function test_vrati_redirects_back(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/sportskoAngazovanje/vrati');

        $response->assertRedirect();
    }

    public function test_vrati_preserves_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/sportskoAngazovanje/vrati');

        $response->assertRedirect();
    }

    public function test_complete_crud_workflow_with_session(): void
    {
        $user = User::factory()->create();
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();

        $this->actingAs($user)
            ->session(['id' => $kandidat->id])
            ->post('/sportskoAngazovanje/unos', [
                'nazivKluba' => 'New FK',
                'odDoGodina' => '2020-2024',
                'ukupnoGodina' => 4,
                'sport_id' => $sport->id,
            ]);

        $angazovanje = SportskoAngazovanje::where('nazivKluba', 'New FK')->first();
        $this->assertNotNull($angazovanje);

        $response = $this->actingAs($user)->get("/sportskoAngazovanje/{$angazovanje->id}/edit");
        $response->assertOk();

        $this->actingAs($user)
            ->session(['id' => $kandidat->id])
            ->patch("/sportskoAngazovanje/{$angazovanje->id}", [
                'nazivKluba' => 'Updated FK',
                'odDoGodina' => '2015-2019',
                'ukupnoGodina' => 4,
                'sport_id' => $sport->id,
            ]);

        $this->assertDatabaseHas('sportsko_angazovanje', [
            'id' => $angazovanje->id,
            'nazivKluba' => 'Updated FK',
        ]);

        $this->actingAs($user)->get("/sportskoAngazovanje/{$angazovanje->id}/delete");

        $this->assertDatabaseMissing('sportsko_angazovanje', [
            'id' => $angazovanje->id,
        ]);
    }

    public function test_foreign_key_relationships(): void
    {
        $kandidat = Kandidat::factory()->create();
        $sport = Sport::factory()->create();
        $angazovanje = SportskoAngazovanje::factory()->create([
            'kandidat_id' => $kandidat->id,
            'sport_id' => $sport->id,
        ]);

        $this->assertEquals($kandidat->id, $angazovanje->kandidat_id);
        $this->assertEquals($sport->id, $angazovanje->sport_id);
        $this->assertEquals($kandidat->id, $angazovanje->kandidat()->first()->id);
        $this->assertEquals($sport->id, $angazovanje->sport()->first()->id);
    }

    public function test_angazovanje_factory_creates_valid_record(): void
    {
        $angazovanje = SportskoAngazovanje::factory()->create();

        $this->assertNotNull($angazovanje->id);
        $this->assertNotEmpty($angazovanje->nazivKluba);
        $this->assertNotEmpty($angazovanje->odDoGodina);
        $this->assertNotNull($angazovanje->ukupnoGodina);
        $this->assertNotNull($angazovanje->sport_id);
        $this->assertNotNull($angazovanje->kandidat_id);
    }

    public function test_sport_factory_creates_valid_record(): void
    {
        $sport = Sport::factory()->create();

        $this->assertNotNull($sport->id);
        $this->assertNotEmpty($sport->naziv);
        $this->assertNotNull($sport->indikatorAktivan);
    }

    public function test_index_returns_view_with_kandidat_and_sports(): void
    {
        $kandidat = Kandidat::factory()->create();

        $controller = app(\App\Http\Controllers\SportskoAngazovanjeController::class);
        $response = $controller->index($kandidat);

        $this->assertSame('sifarnici.sportskoAngazovanje ', $response->name());
        $this->assertSame($kandidat->id, $response->getData()['kandidat']->id);
        $this->assertArrayHasKey('sport', $response->getData());
    }
}
