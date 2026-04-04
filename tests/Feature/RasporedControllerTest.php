<?php

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\OblikNastave;
use App\Models\Predmet;
use App\Models\Profesor;
use App\Models\Raspored;
use App\Models\Semestar;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusProfesora;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RasporedControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Predmet $predmet;

    protected Profesor $profesor;

    protected StudijskiProgram $studijskiProgram;

    protected GodinaStudija $godinaStudija;

    protected Semestar $semestar;

    protected SkolskaGodUpisa $aktivnaGodina;

    protected SkolskaGodUpisa $neaktivnaGodina;

    protected OblikNastave $predavanja;

    protected OblikNastave $vezbe;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $statusProfesora = StatusProfesora::query()->first() ?? StatusProfesora::create([
            'id' => 1,
            'naziv' => 'Aktivan',
            'indikatorAktivan' => 1,
        ]);

        $tipStudija = TipStudija::factory()->create([
            'id' => 1,
            'naziv' => 'Osnovne akademske studije',
            'skrNaziv' => 'OAS',
            'indikatorAktivan' => 1,
        ]);

        $this->studijskiProgram = StudijskiProgram::factory()->create([
            'naziv' => 'Fizioterapija',
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->godinaStudija = GodinaStudija::query()->firstOrCreate(
            ['id' => 1],
            [
                'naziv' => 'Prva',
                'nazivRimski' => 'I',
                'nazivSlovimaUPadezu' => 'Prve',
                'redosledPrikazivanja' => 1,
                'indikatorAktivan' => 1,
            ]
        );

        $this->semestar = Semestar::query()->firstOrCreate(
            ['id' => 1],
            [
                'naziv' => 'Zimski',
                'nazivRimski' => 'I',
                'nazivBrojcano' => 1,
                'indikatorAktivan' => 1,
            ]
        );

        $this->predavanja = OblikNastave::query()->firstOrCreate(
            ['id' => 1],
            [
                'naziv' => 'Predavanja',
                'skrNaziv' => 'P',
                'indikatorAktivan' => 1,
            ]
        );

        $this->vezbe = OblikNastave::query()->firstOrCreate(
            ['id' => 2],
            [
                'naziv' => 'Vezbe',
                'skrNaziv' => 'V',
                'indikatorAktivan' => 1,
            ]
        );

        $this->aktivnaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
            'aktivan' => 1,
        ]);

        $this->neaktivnaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2023/2024',
            'aktivan' => 0,
        ]);

        $this->predmet = Predmet::factory()->create([
            'naziv' => 'Anatomija',
        ]);

        $this->profesor = Profesor::factory()->create([
            'ime' => 'Pera',
            'prezime' => 'Peric',
            'status_id' => $statusProfesora->id,
            'indikatorAktivan' => 1,
        ]);

        $this->user = User::create([
            'name' => 'Raspored Admin',
            'email' => 'raspored_admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($this->user);
    }

    public function test_index_returns_only_active_schedule_by_default_and_applies_filters(): void
    {
        $aktivniTermin = $this->createRaspored([
            'studijski_program_id' => $this->studijskiProgram->id,
            'semestar_id' => $this->semestar->id,
            'skolska_godina_id' => $this->aktivnaGodina->id,
            'dan' => 1,
            'vreme_od' => '08:00:00',
            'vreme_do' => '10:00:00',
        ]);

        $neaktivniTermin = $this->createRaspored([
            'skolska_godina_id' => $this->neaktivnaGodina->id,
            'dan' => 2,
            'vreme_od' => '10:00:00',
            'vreme_do' => '12:00:00',
            'grupa' => 'B',
        ]);

        $response = $this->get('/raspored?studijski_program_id='.$this->studijskiProgram->id.'&semestar_id='.$this->semestar->id);

        $response->assertStatus(200);
        $response->assertViewIs('raspored.index');
        $response->assertViewHas('raspored', function ($raspored) use ($aktivniTermin, $neaktivniTermin) {
            return $raspored->contains('id', $aktivniTermin->id)
                && ! $raspored->contains('id', $neaktivniTermin->id);
        });
    }

    public function test_create_returns_form_with_lookup_data(): void
    {
        $response = $this->get('/raspored/create');

        $response->assertStatus(200);
        $response->assertViewIs('raspored.create');
        $response->assertViewHas('predmeti', fn ($predmeti) => $predmeti->contains('id', $this->predmet->id));
        $response->assertViewHas('profesori', fn ($profesori) => $profesori->contains('id', $this->profesor->id));
        $response->assertViewHas('studijskiProgrami', fn ($programi) => $programi->contains('id', $this->studijskiProgram->id));
        $response->assertViewHas('obliciNastave', fn ($oblici) => $oblici->contains('id', $this->predavanja->id));
    }

    public function test_store_creates_schedule_entry(): void
    {
        $response = $this->post('/raspored', $this->validPayload([
            'dan' => 3,
            'vreme_od' => '09:00',
            'vreme_do' => '11:00',
            'prostorija' => 'A-201',
            'grupa' => 'A',
        ]));

        $response->assertRedirect(route('raspored.index'));
        $response->assertSessionHas('success', 'Распоред креиран');
        $this->assertDatabaseHas('raspored', [
            'predmet_id' => $this->predmet->id,
            'profesor_id' => $this->profesor->id,
            'studijski_program_id' => $this->studijskiProgram->id,
            'skolska_godina_id' => $this->aktivnaGodina->id,
            'dan' => 3,
            'prostorija' => 'A-201',
            'grupa' => 'A',
        ]);
    }

    public function test_edit_returns_existing_schedule_with_lookup_data(): void
    {
        $raspored = $this->createRaspored();

        $response = $this->get('/raspored/'.$raspored->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewIs('raspored.edit');
        $response->assertViewHas('raspored', fn (Raspored $termin) => $termin->is($raspored));
        $response->assertViewHas('skolskeGodine', fn ($godine) => $godine->contains('id', $this->aktivnaGodina->id));
    }

    public function test_update_persists_schedule_changes(): void
    {
        $raspored = $this->createRaspored([
            'dan' => 1,
            'vreme_od' => '08:00:00',
            'vreme_do' => '10:00:00',
            'prostorija' => 'A-101',
        ]);

        $response = $this->put('/raspored/'.$raspored->id, $this->validPayload([
            'oblik_nastave_id' => $this->vezbe->id,
            'dan' => 4,
            'vreme_od' => '12:00',
            'vreme_do' => '14:00',
            'prostorija' => 'Lab-2',
            'grupa' => 'C',
        ]));

        $response->assertRedirect(route('raspored.index'));
        $response->assertSessionHas('success', 'Распоред ажуриран');
        $this->assertDatabaseHas('raspored', [
            'id' => $raspored->id,
            'oblik_nastave_id' => $this->vezbe->id,
            'dan' => 4,
            'prostorija' => 'Lab-2',
            'grupa' => 'C',
        ]);
    }

    public function test_destroy_removes_schedule_entry(): void
    {
        $raspored = $this->createRaspored();

        $response = $this->delete('/raspored/'.$raspored->id);

        $response->assertRedirect(route('raspored.index'));
        $response->assertSessionHas('success', 'Распоред обрисан');
        $this->assertDatabaseMissing('raspored', [
            'id' => $raspored->id,
        ]);
    }

    public function test_pregled_groups_active_schedule_by_day(): void
    {
        $ponedeljak = $this->createRaspored([
            'dan' => 1,
            'vreme_od' => '08:00:00',
            'vreme_do' => '10:00:00',
            'skolska_godina_id' => $this->aktivnaGodina->id,
        ]);

        $this->createRaspored([
            'dan' => 3,
            'vreme_od' => '12:00:00',
            'vreme_do' => '14:00:00',
            'skolska_godina_id' => $this->neaktivnaGodina->id,
            'grupa' => 'B',
        ]);

        $response = $this->get('/raspored/pregled');

        $response->assertStatus(200);
        $response->assertViewIs('raspored.pregled');
        $response->assertViewHas('rasporedPoDanima', function (array $rasporedPoDanima) use ($ponedeljak) {
            return $rasporedPoDanima[1]['casovi']->contains('id', $ponedeljak->id)
                && $rasporedPoDanima[3]['casovi']->isEmpty();
        });
    }

    public function test_kalendar_returns_filtered_view_data(): void
    {
        $terminA = $this->createRaspored([
            'studijski_program_id' => $this->studijskiProgram->id,
            'skolska_godina_id' => $this->aktivnaGodina->id,
            'dan' => 2,
            'vreme_od' => '09:00:00',
            'vreme_do' => '11:00:00',
        ]);

        $drugiProgram = StudijskiProgram::factory()->create([
            'naziv' => 'Radiologija',
            'tipStudija_id' => $this->studijskiProgram->tipStudija_id,
            'indikatorAktivan' => 1,
        ]);

        $this->createRaspored([
            'studijski_program_id' => $drugiProgram->id,
            'skolska_godina_id' => $this->aktivnaGodina->id,
            'dan' => 5,
            'vreme_od' => '13:00:00',
            'vreme_do' => '15:00:00',
            'grupa' => 'D',
        ]);

        $response = $this->get('/raspored/kalendar?studijski_program_id='.$this->studijskiProgram->id);

        $response->assertStatus(200);
        $response->assertViewIs('raspored.kalendar');
        $response->assertViewHas('raspored', function ($raspored) use ($terminA) {
            return $raspored->count() === 1 && $raspored->first()->is($terminA);
        });
    }

    public function test_kalendar_events_returns_calendar_payload_with_professor_name(): void
    {
        $predavanja = $this->createRaspored([
            'oblik_nastave_id' => $this->predavanja->id,
            'dan' => 1,
            'vreme_od' => '08:15:00',
            'vreme_do' => '10:00:00',
            'prostorija' => 'Sala 1',
            'grupa' => 'Svi',
            'skolska_godina_id' => $this->aktivnaGodina->id,
        ]);

        $response = $this->getJson('/raspored/kalendar/events?studijski_program_id='.$this->studijskiProgram->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $predavanja->id,
            'title' => 'Anatomija - Predavanja',
            'daysOfWeek' => [1],
            'startTime' => '08:15',
            'endTime' => '10:00',
            'backgroundColor' => '#3498db',
            'borderColor' => '#3498db',
        ]);
        $response->assertJsonPath('0.extendedProps.profesor', 'Pera Peric');
        $response->assertJsonPath('0.extendedProps.prostorija', 'Sala 1');
        $response->assertJsonPath('0.extendedProps.grupa', 'Svi');
    }

    protected function createRaspored(array $overrides = []): Raspored
    {
        return Raspored::create(array_merge([
            'predmet_id' => $this->predmet->id,
            'profesor_id' => $this->profesor->id,
            'studijski_program_id' => $this->studijskiProgram->id,
            'godina_studija_id' => $this->godinaStudija->id,
            'semestar_id' => $this->semestar->id,
            'skolska_godina_id' => $this->aktivnaGodina->id,
            'oblik_nastave_id' => $this->predavanja->id,
            'dan' => 1,
            'vreme_od' => '08:00:00',
            'vreme_do' => '10:00:00',
            'prostorija' => 'A-101',
            'grupa' => 'A',
            'aktivan' => 1,
        ], $overrides));
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'predmet_id' => $this->predmet->id,
            'profesor_id' => $this->profesor->id,
            'studijski_program_id' => $this->studijskiProgram->id,
            'godina_studija_id' => $this->godinaStudija->id,
            'semestar_id' => $this->semestar->id,
            'skolska_godina_id' => $this->aktivnaGodina->id,
            'oblik_nastave_id' => $this->predavanja->id,
            'dan' => 1,
            'vreme_od' => '08:00',
            'vreme_do' => '10:00',
            'prostorija' => 'A-101',
            'grupa' => 'A',
        ], $overrides);
    }
}
