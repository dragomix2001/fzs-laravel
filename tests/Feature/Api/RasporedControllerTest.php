<?php

namespace Tests\Feature\Api;

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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RasporedControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected Predmet $predmet;

    protected Profesor $profesor;

    protected StudijskiProgram $studijskiProgram;

    protected StudijskiProgram $drugiProgram;

    protected GodinaStudija $godinaStudija;

    protected Semestar $semestar;

    protected SkolskaGodUpisa $aktivnaGodina;

    protected OblikNastave $predavanja;

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

        $tipStudija = TipStudija::query()->firstOrCreate(
            ['id' => 1],
            [
                'naziv' => 'Osnovne akademske studije',
                'skrNaziv' => 'OAS',
                'indikatorAktivan' => 1,
            ]
        );

        $this->studijskiProgram = StudijskiProgram::factory()->create([
            'naziv' => 'Fizioterapija',
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->drugiProgram = StudijskiProgram::factory()->create([
            'naziv' => 'Radiologija',
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

        $this->aktivnaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
            'aktivan' => 1,
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
    }

    public function test_index_returns_filtered_schedule_json(): void
    {
        $match = $this->createRaspored([
            'studijski_program_id' => $this->studijskiProgram->id,
            'dan' => 2,
            'vreme_od' => '08:00:00',
            'vreme_do' => '10:00:00',
        ]);

        $this->createRaspored([
            'studijski_program_id' => $this->drugiProgram->id,
            'dan' => 2,
            'vreme_od' => '11:00:00',
            'vreme_do' => '13:00:00',
        ]);

        $response = $this->getJson('/api/v1/raspored?dan=2&profesor_id='.$this->profesor->id.'&studijski_program_id='.$this->studijskiProgram->id);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Распоред успешно учитат');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $match->id);
        $response->assertJsonPath('data.0.profesor.id', $this->profesor->id);
        $response->assertJsonPath('data.0.predmet.naziv', 'Anatomija');
    }

    public function test_index_filters_by_predmet_id(): void
    {
        $otherPredmet = Predmet::factory()->create([
            'naziv' => 'Fiziologija',
        ]);

        $match = $this->createRaspored([
            'predmet_id' => $this->predmet->id,
        ]);

        $this->createRaspored([
            'predmet_id' => $otherPredmet->id,
        ]);

        $response = $this->getJson('/api/v1/raspored?predmet_id='.$this->predmet->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $match->id);
    }

    public function test_today_returns_only_current_day_active_schedule(): void
    {
        $currentDay = (int) now()->dayOfWeekIso;

        $today = $this->createRaspored([
            'dan' => $currentDay,
            'aktivan' => 1,
            'vreme_od' => '09:00:00',
            'vreme_do' => '11:00:00',
        ]);

        $this->createRaspored([
            'dan' => $currentDay,
            'aktivan' => 0,
            'vreme_od' => '12:00:00',
            'vreme_do' => '14:00:00',
        ]);

        $this->createRaspored([
            'dan' => $currentDay === 1 ? 2 : 1,
            'aktivan' => 1,
            'vreme_od' => '15:00:00',
            'vreme_do' => '17:00:00',
        ]);

        $response = $this->getJson('/api/v1/raspored/today');

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Данашњи распоред');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $today->id);
    }

    public function test_show_returns_single_schedule_with_loaded_relations(): void
    {
        $raspored = $this->createRaspored();

        $response = $this->getJson('/api/v1/raspored/'.$raspored->id);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Распоред успешно учитат');
        $response->assertJsonPath('data.id', $raspored->id);
        $response->assertJsonPath('data.profesor.id', $this->profesor->id);
        $response->assertJsonPath('data.studijski_program.id', $this->studijskiProgram->id);
        $response->assertJsonPath('data.oblik_nastave.naziv', 'Predavanja');
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
}
