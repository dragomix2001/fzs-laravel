<?php

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PredmetControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected TipStudija $tipStudija;

    protected StudijskiProgram $program;

    protected SkolskaGodUpisa $skolskaGodina;

    protected GodinaStudija $godinaStudija;

    protected TipPredmeta $tipPredmeta;

    protected Predmet $predmet;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'predmet_admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->tipStudija = TipStudija::factory()->create([
            'id' => 1,
            'naziv' => 'Osnovne akademske studije',
            'skrNaziv' => 'OAS',
            'indikatorAktivan' => 1,
        ]);

        $this->program = StudijskiProgram::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->skolskaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
        ]);

        $this->godinaStudija = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $this->tipPredmeta = TipPredmeta::query()->first() ?? TipPredmeta::create([
            'naziv' => 'Obavezni',
            'skrNaziv' => 'OBV',
            'indikatorAktivan' => 1,
        ]);

        $this->predmet = Predmet::factory()->create([
            'naziv' => 'Anatomija 1',
        ]);

        $this->actingAs($this->user);
    }

    public function test_index_returns_subject_list_view(): void
    {
        $response = $this->get('/predmet');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.predmet');
        $response->assertViewHas('predmet', function ($predmeti) {
            return $predmeti->contains('id', $this->predmet->id);
        });
        $response->assertViewHasAll([
            'tipStudija',
            'studijskiProgram',
            'godinaStudija',
            'tipPredmeta',
        ]);
    }

    public function test_add_returns_create_view(): void
    {
        $response = $this->get('/predmet/add');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.addPredmet');
    }

    public function test_unos_creates_subject_and_redirects(): void
    {
        $response = $this->post('/predmet/unos', [
            'naziv' => 'Fiziologija',
        ]);

        $response->assertRedirect('/predmet');
        $this->assertDatabaseHas('predmet', [
            'naziv' => 'Fiziologija',
        ]);
    }

    public function test_edit_returns_edit_view(): void
    {
        $response = $this->get('/predmet/'.$this->predmet->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.editPredmet');
        $response->assertViewHas('predmet', function (Predmet $predmet) {
            return $predmet->is($this->predmet);
        });
    }

    public function test_update_changes_subject_name_and_redirects(): void
    {
        $response = $this->patch('/predmet/'.$this->predmet->id, [
            'naziv' => 'Anatomija 2',
        ]);

        $response->assertRedirect('/predmet');
        $this->assertDatabaseHas('predmet', [
            'id' => $this->predmet->id,
            'naziv' => 'Anatomija 2',
        ]);
    }

    public function test_add_program_returns_view_with_lookup_data(): void
    {
        $response = $this->get('/predmet/'.$this->predmet->id.'/addProgram');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.addPredmetProgram');
        $response->assertViewHasAll([
            'programi',
            'predmet',
            'godinaStudija',
            'tipPredmeta',
            'tipStudija',
            'skolskaGodina',
        ]);
    }

    public function test_add_program_unos_creates_predmet_program_and_redirects(): void
    {
        $response = $this->post('/predmet/addProgramUnos', [
            'program_id' => $this->program->id,
            'predmet_id' => $this->predmet->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'semestar' => 1,
            'tipPredmeta_id' => $this->tipPredmeta->id,
            'espb' => 6,
            'predavanja' => 3,
            'vezbe' => 2,
            'skolskaGodina_id' => $this->skolskaGodina->id,
        ]);

        $response->assertRedirect('/predmet/'.$this->predmet->id.'/editProgram');
        $this->assertDatabaseHas('predmet_program', [
            'predmet_id' => $this->predmet->id,
            'studijskiProgram_id' => $this->program->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'semestar' => 1,
            'tipPredmeta_id' => $this->tipPredmeta->id,
            'tipStudija_id' => $this->tipStudija->id,
            'espb' => 6,
            'predavanja' => 3,
            'vezbe' => 2,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'statusPredmeta' => 1,
            'indikatorAktivan' => 1,
        ]);
    }

    public function test_edit_program_returns_existing_program_assignments(): void
    {
        $predmetProgram = PredmetProgram::create([
            'predmet_id' => $this->predmet->id,
            'studijskiProgram_id' => $this->program->id,
            'tipStudija_id' => $this->tipStudija->id,
            'semestar' => 2,
            'espb' => 5,
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipPredmeta_id' => $this->tipPredmeta->id,
            'statusPredmeta' => 1,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/predmet/'.$this->predmet->id.'/editProgram');

        $response->assertStatus(200);
        $response->assertViewIs('sifarnici.editPredmetProgram');
        $response->assertViewHas('predmet', function (Predmet $predmet) {
            return $predmet->is($this->predmet);
        });
        $response->assertViewHas('programi', function ($programi) use ($predmetProgram) {
            return $programi->contains('id', $predmetProgram->id);
        });
    }

    public function test_delete_program_removes_assignment_and_redirects_back(): void
    {
        $predmetProgram = PredmetProgram::create([
            'predmet_id' => $this->predmet->id,
            'studijskiProgram_id' => $this->program->id,
            'tipStudija_id' => $this->tipStudija->id,
            'semestar' => 2,
            'espb' => 5,
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipPredmeta_id' => $this->tipPredmeta->id,
            'statusPredmeta' => 1,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->from('/predmet/'.$this->predmet->id.'/editProgram')
            ->get('/predmet/'.$predmetProgram->id.'/deleteProgram');

        $response->assertRedirect('/predmet/'.$this->predmet->id.'/editProgram');
        $this->assertDatabaseMissing('predmet_program', [
            'id' => $predmetProgram->id,
        ]);
    }

    public function test_delete_removes_subject_and_redirects_back(): void
    {
        $response = $this->from('/predmet')
            ->get('/predmet/'.$this->predmet->id.'/delete');

        $response->assertRedirect('/predmet');
        $this->assertDatabaseMissing('predmet', [
            'id' => $this->predmet->id,
        ]);
    }
}
