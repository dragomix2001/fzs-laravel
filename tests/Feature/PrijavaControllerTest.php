<?php

namespace Tests\Feature;

use App\Models\AktivniIspitniRokovi;
use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaOdbrane;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipPrijave;
use App\Models\TipStudija;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PrijavaControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Override the default migrate:fresh strategy because the project's
     * migrations cannot run from scratch (cross-table references inside
     * migration files).  Instead we rely on the pre-existing table
     * structure in fzs_testing and let RefreshDatabase's transaction
     * wrapping keep each test isolated.
     */
    protected function migrateDatabases(): void
    {
        if (DB::getSchemaBuilder()->hasTable('migrations')) {
            return;
        }
        // Fallback: plain migrate if DB is empty.
        $this->artisan('migrate', ['--force' => true]);
    }

    protected User $user;

    protected Kandidat $kandidat;

    protected TipStudija $tipStudija;

    protected StudijskiProgram $program;

    protected SkolskaGodUpisa $skolskaGodina;

    protected StatusStudiranja $status;

    protected Predmet $predmet;

    protected PredmetProgram $predmetProgram;

    protected AktivniIspitniRokovi $rok;

    protected Profesor $profesor;

    protected TipPredmeta $tipPredmeta;

    protected TipPrijave $tipPrijave;

    protected GodinaStudija $godinaStudija;

    protected function setUp(): void
    {
        parent::setUp();

        // The base TestCase::setUp() opens an output buffer (ob_start) to
        // suppress binary output during tests.  Close it immediately so
        // PHPUnit 13's strict buffer-level tracking is satisfied.  We
        // also override tearDown() so the base tearDown's ob_end_clean()
        // always has a matching buffer to close.
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Allow all gates for admin user
        Gate::before(function ($user, string $ability) {
            if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return true;
            }
            if ($user && isset($user->role) && $user->role === 'admin') {
                return true;
            }
        });

        // Create lookup data needed by the controller
        $this->tipPredmeta = TipPredmeta::forceCreate([
            'naziv' => 'Obavezni',
            'skrNaziv' => 'OBV',
            'indikatorAktivan' => 1,
        ]);

        $this->tipPrijave = TipPrijave::forceCreate([
            'naziv' => 'Redovna',
            'indikatorAktivan' => 1,
        ]);

        $this->godinaStudija = GodinaStudija::forceCreate([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->tipStudija = TipStudija::factory()->create([
            'naziv' => 'Osnovne akademske studije',
            'skrNaziv' => 'OAS',
            'indikatorAktivan' => 1,
        ]);

        $this->program = StudijskiProgram::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
        ]);

        $this->skolskaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
        ]);

        $this->status = StatusStudiranja::factory()->create([
            'naziv' => 'upis u toku',
            'indikatorAktivan' => 1,
        ]);

        $this->kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'statusUpisa_id' => $this->status->id,
            'indikatorAktivan' => 1,
        ]);

        $this->predmet = Predmet::factory()->create();

        $this->predmetProgram = PredmetProgram::forceCreate([
            'predmet_id' => $this->predmet->id,
            'studijskiProgram_id' => $this->program->id,
            'tipStudija_id' => $this->tipStudija->id,
            'semestar' => 1,
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipPredmeta_id' => $this->tipPredmeta->id,
            'statusPredmeta' => 1,
            'espb' => 6,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => $this->skolskaGodina->id,
        ]);

        $this->rok = AktivniIspitniRokovi::factory()->create([
            'indikatorAktivan' => 1,
        ]);

        $this->profesor = Profesor::factory()->create();

        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        // Re-open an output buffer so base TestCase::tearDown()'s
        // ob_end_clean() has a matching buffer to close.
        ob_start();
        parent::tearDown();
    }

    // region PRIJAVA ISPITA - PREDMET SIDE

    /** @test */
    public function test_spisak_predmeta_returns_view(): void
    {
        $response = $this->get('/predmeti/');

        $response->assertStatus(200);
        $response->assertViewIs('prijava.spisakPredmeta');
    }

    /** @test */
    public function test_index_prijava_ispita_predmet_returns_view(): void
    {
        $response = $this->get("/prijava/zaPredmet/{$this->predmet->id}");

        $response->assertStatus(200);
        $response->assertViewIs('prijava.indexPredmet');
    }

    /** @test */
    public function test_index_prijava_ispita_predmet_with_no_predmet_program(): void
    {
        $novPredmet = Predmet::factory()->create();

        $response = $this->get("/prijava/zaPredmet/{$novPredmet->id}");

        $response->assertStatus(200);
        $response->assertViewIs('prijava.indexPredmet');
        $response->assertViewHas('predmet');
    }

    /** @test */
    public function test_create_prijava_ispita_predmet_returns_view(): void
    {
        $response = $this->get("/prijava/predmet/{$this->predmetProgram->id}");

        $response->assertStatus(200);
        $response->assertViewIs('prijava.create');
    }

    /** @test */
    public function test_create_prijava_ispita_predmet_many_returns_view(): void
    {
        $response = $this->get("/prijava/predmetVise/{$this->predmet->id}");

        $response->assertStatus(200);
        $response->assertViewIs('prijava.createManyPredmet');
    }

    // endregion

    // region PRIJAVA ISPITA - STUDENT SIDE

    /** @test */
    public function test_sve_prijave_ispita_za_studenta_returns_view(): void
    {
        $response = $this->get("/prijava/zaStudenta/{$this->kandidat->id}");

        $response->assertStatus(200);
        $response->assertViewIs('prijava.index');
        $response->assertViewHas('kandidat');
    }

    /** @test */
    public function test_sve_prijave_ispita_za_studenta_with_prijave(): void
    {
        PrijavaIspita::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => $this->tipPrijave->id,
        ]);

        $response = $this->get("/prijava/zaStudenta/{$this->kandidat->id}");

        $response->assertStatus(200);
        $response->assertViewHas('prijave');
    }

    /** @test */
    public function test_create_prijava_ispita_student_returns_view(): void
    {
        $response = $this->get("/prijava/student/{$this->kandidat->id}");

        $response->assertStatus(200);
        $response->assertViewIs('prijava.create');
        $response->assertViewHas('kandidat');
    }

    // endregion

    // region STORE / DELETE

    /** @test */
    public function test_store_prijava_ispita_creates_record(): void
    {
        $response = $this->post('/prijava/', [
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => $this->tipPrijave->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $this->assertDatabaseHas('prijava_ispita', [
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'rok_id' => $this->rok->id,
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function test_store_prijava_ispita_redirects_to_predmet_when_flag_set(): void
    {
        $response = $this->post('/prijava/', [
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => $this->tipPrijave->id,
            'prijava_za_predmet' => 1,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $response->assertRedirect();
        $response->assertRedirectContains('/prijava/zaPredmet/');
    }

    /** @test */
    public function test_delete_prijava_ispita_removes_record(): void
    {
        $prijava = PrijavaIspita::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => $this->tipPrijave->id,
        ]);

        $response = $this->get("/prijava/delete/{$prijava->id}");

        $this->assertDatabaseMissing('prijava_ispita', ['id' => $prijava->id]);
        $response->assertRedirect();
    }

    /** @test */
    public function test_delete_prijava_ispita_redirects_to_predmet(): void
    {
        $prijava = PrijavaIspita::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => $this->tipPrijave->id,
        ]);

        $response = $this->get("/prijava/delete/{$prijava->id}?prijava=predmet");

        $response->assertRedirect();
        $response->assertRedirectContains('/prijava/zaPredmet/');
    }

    // endregion

    // region AJAX HELPERS

    /** @test */
    public function test_vrati_kandidata_prijava_returns_json(): void
    {
        $response = $this->post('/prijava/vratiKandidataPrijava', [
            'id' => $this->kandidat->id,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('student', $data);
        $this->assertArrayHasKey('predmeti', $data);
    }

    /** @test */
    public function test_vrati_predmet_prijava_returns_data(): void
    {
        $response = $this->post('/prijava/vratiPredmetPrijava', [
            'id' => $this->predmetProgram->id,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('tipPredmeta', $data);
        $this->assertArrayHasKey('godinaStudija', $data);
        $this->assertArrayHasKey('tipStudija', $data);
        $this->assertArrayHasKey('profesori', $data);
    }

    /** @test */
    public function test_vrati_kandidata_po_broju_returns_html_row(): void
    {
        $response = $this->post('/prijava/vratiKandidataPoBroju', [
            'id' => $this->kandidat->id,
        ]);

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('<tr>', $content);
        $this->assertStringContainsString('<td>', $content);
    }

    // endregion

    // region DIPLOMSKI TEMA

    /** @test */
    public function test_diplomski_tema_returns_view(): void
    {
        $response = $this->get("/prijava/diplomskiTema/{$this->kandidat->id}");

        $response->assertStatus(200);
        $response->assertViewIs('prijava.diplomskiTema');
        $response->assertViewHas('kandidat');
    }

    /** @test */
    public function test_store_diplomski_tema_creates_record(): void
    {
        $response = $this->post('/prijava/storeDiplomskiTema', [
            'kandidat_id' => $this->kandidat->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'predmet_id' => $this->predmetProgram->id,
            'profesor_id' => $this->profesor->id,
            'nazivTeme' => 'Test Tema Diplomskog Rada',
            'datum' => now()->toDateString(),
            'indikatorOdobreno' => 0,
        ]);

        $this->assertDatabaseHas('diplomski_prijava_teme', [
            'kandidat_id' => $this->kandidat->id,
            'nazivTeme' => 'Test Tema Diplomskog Rada',
        ]);

        $response->assertRedirect("/prijava/zaStudenta/{$this->kandidat->id}");
    }

    /** @test */
    public function test_edit_diplomski_tema_returns_view(): void
    {
        DiplomskiPrijavaTeme::create([
            'kandidat_id' => $this->kandidat->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'predmet_id' => $this->predmetProgram->id,
            'profesor_id' => $this->profesor->id,
            'nazivTeme' => 'Test Tema',
            'datum' => now()->toDateString(),
            'indikatorOdobreno' => 0,
        ]);

        $response = $this->get("/prijava/diplomskiTema/{$this->kandidat->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('prijava.editDiplomskiTema');
    }

    /** @test */
    public function test_delete_diplomski_tema_removes_record(): void
    {
        $tema = DiplomskiPrijavaTeme::create([
            'kandidat_id' => $this->kandidat->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'predmet_id' => $this->predmetProgram->id,
            'profesor_id' => $this->profesor->id,
            'nazivTeme' => 'Test Tema Za Brisanje',
            'datum' => now()->toDateString(),
            'indikatorOdobreno' => 0,
        ]);

        $response = $this->get("/deleteDiplomskiTema/{$this->kandidat->id}/delete");

        $this->assertDatabaseMissing('diplomski_prijava_teme', ['id' => $tema->id]);
        $response->assertRedirect("/prijava/zaStudenta/{$this->kandidat->id}");
    }

    // endregion

    // region DIPLOMSKI ODBRANA

    /** @test */
    public function test_diplomski_odbrana_returns_string_when_no_tema(): void
    {
        $response = $this->get("/prijava/diplomskiOdbrana/{$this->kandidat->id}");

        $response->assertStatus(200);
        $content = $response->getContent();
        // Controller returns a string message when no tema exists
        $this->assertNotEmpty($content);
    }

    /** @test */
    public function test_diplomski_odbrana_returns_view_when_tema_exists(): void
    {
        DiplomskiPrijavaTeme::create([
            'kandidat_id' => $this->kandidat->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'predmet_id' => $this->predmetProgram->id,
            'profesor_id' => $this->profesor->id,
            'nazivTeme' => 'Test Tema Za Odbranu',
            'datum' => now()->toDateString(),
            'indikatorOdobreno' => 0,
        ]);

        $response = $this->get("/prijava/diplomskiOdbrana/{$this->kandidat->id}");

        $response->assertStatus(200);
        $response->assertViewIs('prijava.odbrana.diplomskiOdbrana');
    }

    /** @test */
    public function test_store_diplomski_odbrana_creates_record(): void
    {
        DiplomskiPrijavaTeme::create([
            'kandidat_id' => $this->kandidat->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'predmet_id' => $this->predmetProgram->id,
            'profesor_id' => $this->profesor->id,
            'nazivTeme' => 'Test Tema',
            'datum' => now()->toDateString(),
            'indikatorOdobreno' => 0,
        ]);

        $response = $this->post('/prijava/storeDiplomskiOdbrana', [
            'kandidat_id' => $this->kandidat->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'predmet_id' => $this->predmetProgram->id,
            'temu_odobrio_profesor_id' => $this->profesor->id,
            'odbranu_odobrio_profesor_id' => $this->profesor->id,
            'nazivTeme' => 'Test Odbrana',
            'datumPrijave' => now()->toDateString(),
            'datumOdbrane' => now()->addDays(30)->toDateString(),
            'indikatorOdobreno' => 0,
        ]);

        $this->assertDatabaseHas('diplomski_prijava_odbrane', [
            'kandidat_id' => $this->kandidat->id,
            'nazivTeme' => 'Test Odbrana',
        ]);

        $response->assertRedirect("/prijava/zaStudenta/{$this->kandidat->id}");
    }

    /** @test */
    public function test_delete_diplomski_odbrana_removes_record(): void
    {
        $odbrana = DiplomskiPrijavaOdbrane::create([
            'kandidat_id' => $this->kandidat->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'predmet_id' => $this->predmetProgram->id,
            'temu_odobrio_profesor_id' => $this->profesor->id,
            'odbranu_odobrio_profesor_id' => $this->profesor->id,
            'nazivTeme' => 'Test Odbrana Za Brisanje',
            'datumPrijave' => now()->toDateString(),
            'datumOdbrane' => now()->addDays(30)->toDateString(),
            'indikatorOdobreno' => 0,
        ]);

        $response = $this->get("/deleteDiplomskiOdbrana/{$this->kandidat->id}/delete");

        $this->assertDatabaseMissing('diplomski_prijava_odbrane', ['id' => $odbrana->id]);
        $response->assertRedirect("/prijava/zaStudenta/{$this->kandidat->id}");
    }

    // endregion

    // region DIPLOMSKI POLAGANJE

    /** @test */
    public function test_diplomski_polaganje_returns_view(): void
    {
        $response = $this->get("/prijava/diplomskiPolaganje/{$this->kandidat->id}");

        $response->assertStatus(200);
        $response->assertViewIs('prijava.polaganje.diplomskiPolaganje');
        $response->assertViewHas('kandidat');
    }

    /** @test */
    public function test_store_diplomski_polaganje_creates_record(): void
    {
        $response = $this->post('/prijava/storeDiplomskiPolaganje', [
            'kandidat_id' => $this->kandidat->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'predmet_id' => $this->predmetProgram->id,
            'profesor_id' => $this->profesor->id,
            'profesor_id_predsednik' => $this->profesor->id,
            'profesor_id_clan' => $this->profesor->id,
            'rok_id' => $this->rok->id,
            'nazivTeme' => 'Test Polaganje',
            'datum' => now()->toDateString(),
            'vreme' => '10:00:00',
        ]);

        $this->assertDatabaseHas('diplomski_polaganje', [
            'kandidat_id' => $this->kandidat->id,
            'nazivTeme' => 'Test Polaganje',
        ]);

        $response->assertRedirect("/prijava/zaStudenta/{$this->kandidat->id}");
    }

    /** @test */
    public function test_delete_diplomski_polaganje_removes_record(): void
    {
        $polaganje = DiplomskiPolaganje::create([
            'kandidat_id' => $this->kandidat->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'predmet_id' => $this->predmetProgram->id,
            'profesor_id' => $this->profesor->id,
            'profesor_id_predsednik' => $this->profesor->id,
            'profesor_id_clan' => $this->profesor->id,
            'rok_id' => $this->rok->id,
            'nazivTeme' => 'Test Polaganje Za Brisanje',
            'datum' => now()->toDateString(),
            'vreme' => '10:00:00',
        ]);

        $response = $this->get("/deleteDiplomskiPolaganje/{$this->kandidat->id}/delete");

        $this->assertDatabaseMissing('diplomski_polaganje', ['id' => $polaganje->id]);
        $response->assertRedirect("/prijava/zaStudenta/{$this->kandidat->id}");
    }

    // endregion

    // region PRIVREMENI UNOS

    /** @test */
    public function test_unos_privremeni_returns_view(): void
    {
        $response = $this->get("/prijava/unosPrivremeni/{$this->kandidat->id}");

        $response->assertStatus(200);
        $response->assertViewIs('upis.unosPrivremeni');
        $response->assertViewHas('kandidat');
    }

    /** @test */
    public function test_dodaj_polozene_ispite_creates_records(): void
    {
        $response = $this->post('/prijava/dodajPolozeneIspite', [
            'kandidat_id' => $this->kandidat->id,
            'odabir' => [$this->predmetProgram->id => $this->predmetProgram->id],
            'konacnaOcena' => [$this->predmetProgram->id => 8],
        ]);

        $this->assertDatabaseHas('polozeni_ispiti', [
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
        ]);

        $response->assertRedirect();
    }

    // endregion

    // region ADDITIONAL COVERAGE TESTS

    /** @test */
    public function test_spisak_predmeta_contains_view_data(): void
    {
        $response = $this->get('/predmeti/');

        $response->assertViewHas('predmeti');
        $response->assertViewHas('tipStudija');
        $response->assertViewHas('studijskiProgrami');
    }

    /** @test */
    public function test_index_prijava_ispita_predmet_contains_view_data(): void
    {
        $response = $this->get("/prijava/zaPredmet/{$this->predmet->id}");

        $response->assertViewHas('predmet');
        $response->assertViewHas('prijave');
    }

    /** @test */
    public function test_create_prijava_ispita_predmet_contains_view_data(): void
    {
        $response = $this->get("/prijava/predmet/{$this->predmetProgram->id}");

        $response->assertViewHas('predmet');
        $response->assertViewHas('ispitniRok');
        $response->assertViewHas('profesor');
    }

    /** @test */
    public function test_sve_prijave_ispita_za_studenta_with_diplomski_data(): void
    {
        DiplomskiPrijavaTeme::create([
            'kandidat_id' => $this->kandidat->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'predmet_id' => $this->predmetProgram->id,
            'profesor_id' => $this->profesor->id,
            'nazivTeme' => 'Test Tema',
            'datum' => now()->toDateString(),
            'indikatorOdobreno' => 0,
        ]);

        $response = $this->get("/prijava/zaStudenta/{$this->kandidat->id}");

        $response->assertStatus(200);
        $response->assertViewHas('diplomskiRadTema');
    }

    /** @test */
    public function test_create_prijava_ispita_predmet_many_contains_view_data(): void
    {
        $response = $this->get("/prijava/predmetVise/{$this->predmet->id}");

        $response->assertViewHas('predmet');
        $response->assertViewHas('ispitniRok');
        $response->assertViewHas('profesor');
        $response->assertViewHas('kandidati');
    }

    // endregion
}
