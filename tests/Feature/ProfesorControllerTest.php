<?php

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\OblikNastave;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\Profesor;
use App\Models\ProfesorPredmet;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusProfesora;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProfesorControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected StatusProfesora $statusProfesora;

    protected Profesor $profesor;

    protected TipStudija $tipStudija;

    protected StudijskiProgram $program;

    protected GodinaStudija $godinaStudija;

    protected SkolskaGodUpisa $skolskaGodina;

    protected TipPredmeta $tipPredmeta;

    protected Predmet $predmet;

    protected PredmetProgram $predmetProgram;

    protected OblikNastave $oblikNastave;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'profesor_admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->statusProfesora = StatusProfesora::query()->first() ?? StatusProfesora::create([
            'id' => 1,
            'naziv' => 'Aktivan',
            'indikatorAktivan' => 1,
        ]);

        $this->tipStudija = TipStudija::query()->firstOrCreate(
            ['id' => 1],
            [
                'naziv' => 'Osnovne akademske studije',
                'skrNaziv' => 'OAS',
                'indikatorAktivan' => 1,
            ]
        );

        $this->program = StudijskiProgram::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
            'skrNazivStudijskogPrograma' => 'FZI',
            'indikatorAktivan' => 1,
        ]);

        $this->godinaStudija = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $this->skolskaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
        ]);

        $this->tipPredmeta = TipPredmeta::query()->first() ?? TipPredmeta::create([
            'naziv' => 'Obavezni',
            'skrNaziv' => 'OBV',
            'indikatorAktivan' => 1,
        ]);

        $this->predmet = Predmet::factory()->create([
            'naziv' => 'Anatomija 1',
        ]);

        $this->predmetProgram = PredmetProgram::create([
            'predmet_id' => $this->predmet->id,
            'studijskiProgram_id' => $this->program->id,
            'tipStudija_id' => $this->tipStudija->id,
            'semestar' => 1,
            'espb' => 6,
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipPredmeta_id' => $this->tipPredmeta->id,
            'statusPredmeta' => 1,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'indikatorAktivan' => 1,
        ]);

        $this->oblikNastave = OblikNastave::query()->first() ?? OblikNastave::create([
            'naziv' => 'Predavanja',
            'skrNaziv' => 'P',
            'indikatorAktivan' => 1,
        ]);

        $this->profesor = Profesor::factory()->create([
            'status_id' => $this->statusProfesora->id,
            'indikatorAktivan' => 1,
        ]);

        $this->actingAs($this->user);
    }

    public function test_index_returns_professor_list_view(): void
    {
        $response = $this->get('/profesor');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.profesor');
        $response->assertViewHas('profesor', function ($profesori) {
            return $profesori->contains('id', $this->profesor->id);
        });
        $response->assertViewHas('status', function ($statusi) {
            return $statusi->contains('id', $this->statusProfesora->id);
        });
    }

    public function test_add_returns_create_view_with_statuses(): void
    {
        $response = $this->get('/profesor/add');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.addProfesor');
        $response->assertViewHas('status', function ($statusi) {
            return $statusi->contains('id', $this->statusProfesora->id);
        });
    }

    public function test_unos_creates_professor_and_redirects(): void
    {
        $response = $this->post('/profesor/unos', [
            'jmbg' => '1234567890123',
            'ime' => 'Petar',
            'prezime' => 'Petrovic',
            'telefon' => '060111222',
            'zvanje' => 'Docent',
            'kabinet' => 'A1',
            'mail' => 'petar.petrovic@test.com',
            'status_id' => $this->statusProfesora->id,
        ]);

        $response->assertRedirect('/profesor');
        $this->assertDatabaseHas('profesor', [
            'jmbg' => '1234567890123',
            'ime' => 'Petar',
            'prezime' => 'Petrovic',
            'status_id' => $this->statusProfesora->id,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_edit_returns_edit_view_with_assignments(): void
    {
        $angazovanje = ProfesorPredmet::create([
            'profesor_id' => $this->profesor->id,
            'predmet_id' => $this->predmetProgram->id,
            'oblik_nastave_id' => $this->oblikNastave->id,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/profesor/'.$this->profesor->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.editProfesor');
        $response->assertViewHas('profesor', function (Profesor $profesor) {
            return $profesor->is($this->profesor);
        });
        $response->assertViewHas('status', function ($statusi) {
            return $statusi->contains('id', $this->statusProfesora->id);
        });
        $response->assertViewHas('predmeti', function ($predmeti) use ($angazovanje) {
            return $predmeti->contains('id', $angazovanje->id);
        });
    }

    public function test_update_changes_professor_and_sets_inactive_flag(): void
    {
        $response = $this->patch('/profesor/'.$this->profesor->id, [
            'jmbg' => '9999999999999',
            'ime' => 'Marko',
            'prezime' => 'Markovic',
            'telefon' => '060333444',
            'zvanje' => 'Vanredni profesor',
            'kabinet' => 'B2',
            'mail' => 'marko.markovic@test.com',
            'status_id' => $this->statusProfesora->id,
            'indikatorAktivan' => 0,
        ]);

        $response->assertRedirect('/profesor');
        $this->assertDatabaseHas('profesor', [
            'id' => $this->profesor->id,
            'jmbg' => '9999999999999',
            'ime' => 'Marko',
            'prezime' => 'Markovic',
            'telefon' => '060333444',
            'zvanje' => 'Vanredni profesor',
            'kabinet' => 'B2',
            'mail' => 'marko.markovic@test.com',
            'indikatorAktivan' => 0,
        ]);
    }

    public function test_update_sets_active_flag_when_checkbox_value_is_on(): void
    {
        $response = $this->patch('/profesor/'.$this->profesor->id, [
            'jmbg' => '8888888888888',
            'ime' => 'Aktivni',
            'prezime' => 'Profesor',
            'telefon' => '060000000',
            'zvanje' => 'Profesor',
            'kabinet' => 'C3',
            'mail' => 'aktivni.profesor@test.com',
            'status_id' => $this->statusProfesora->id,
            'indikatorAktivan' => 'on',
        ]);

        $response->assertRedirect('/profesor');
        $this->assertDatabaseHas('profesor', [
            'id' => $this->profesor->id,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_add_predmet_returns_assignment_view_with_lookup_data(): void
    {
        $response = $this->get('/profesor/'.$this->profesor->id.'/addPredmet');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.addProfesorPredmet');
        $response->assertViewHasAll([
            'predmet',
            'oblik',
            'profesor',
        ]);
    }

    public function test_add_predmet_unos_creates_assignment_and_redirects(): void
    {
        $response = $this->post('/profesor/addPredmetUnos', [
            'profesor_id' => $this->profesor->id,
            'predmet_id' => $this->predmetProgram->id,
            'oblikNastave_id' => $this->oblikNastave->id,
        ]);

        $response->assertRedirect('/profesor/'.$this->profesor->id.'/editPredmet');
        $this->assertDatabaseHas('profesor_predmet', [
            'profesor_id' => $this->profesor->id,
            'predmet_id' => $this->predmetProgram->id,
            'oblik_nastave_id' => $this->oblikNastave->id,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_edit_predmet_returns_existing_assignments_view(): void
    {
        $angazovanje = ProfesorPredmet::create([
            'profesor_id' => $this->profesor->id,
            'predmet_id' => $this->predmetProgram->id,
            'oblik_nastave_id' => $this->oblikNastave->id,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/profesor/'.$this->profesor->id.'/editPredmet');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.editProfesorPredmet');
        $response->assertViewHas('profesor', function (Profesor $profesor) {
            return $profesor->is($this->profesor);
        });
        $response->assertViewHas('predmeti', function ($predmeti) use ($angazovanje) {
            return $predmeti->contains('id', $angazovanje->id);
        });
    }

    public function test_delete_predmet_removes_assignment_and_redirects_back(): void
    {
        $angazovanje = ProfesorPredmet::create([
            'profesor_id' => $this->profesor->id,
            'predmet_id' => $this->predmetProgram->id,
            'oblik_nastave_id' => $this->oblikNastave->id,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->from('/profesor/'.$this->profesor->id.'/editPredmet')
            ->get('/profesor/'.$angazovanje->id.'/deletePredmet');

        $response->assertRedirect('/profesor/'.$this->profesor->id.'/editPredmet');
        $this->assertDatabaseMissing('profesor_predmet', [
            'id' => $angazovanje->id,
        ]);
    }

    public function test_delete_removes_professor_and_redirects_back(): void
    {
        $response = $this->from('/profesor')
            ->get('/profesor/'.$this->profesor->id.'/delete');

        $response->assertRedirect('/profesor');
        $this->assertDatabaseMissing('profesor', [
            'id' => $this->profesor->id,
        ]);
    }
}
