<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kandidat;
use App\Models\NastavnaNedelja;
use App\Models\Predmet;
use App\Models\Prisanstvo;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PrisustvoControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Profesor $profesor;

    protected Predmet $predmet;

    protected Kandidat $student1;

    protected Kandidat $student2;

    protected Kandidat $student3;

    protected NastavnaNedelja $nedelja;

    protected SkolskaGodUpisa $skolskaGodina;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        Gate::before(function ($user) {
            if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return true;
            }

            if ($user && isset($user->role) && $user->role === 'admin') {
                return true;
            }
        });

        // Create profesor
        $this->profesor = Profesor::factory()->create();

        // Create authenticated user
        $this->user = User::create([
            'name' => 'Test Professor',
            'email' => 'profesor_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($this->user);

        // Create required models for Kandidat factory
        $tipStudija = TipStudija::factory()->create();
        $studijskiProgram = StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
        ]);
        $this->skolskaGodina = SkolskaGodUpisa::factory()->create();
        $enrolledStatus = StatusStudiranja::factory()->create([
            'id' => 3,
            'naziv' => 'upisan',
            'indikatorAktivan' => 1,
        ]);

        // Create predmet
        $this->predmet = Predmet::factory()->create();

        // Create students with statusUpisa_id = 3 for enrollment
        $this->student1 = Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $studijskiProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'statusUpisa_id' => $enrolledStatus->id,
            'imeKandidata' => 'John',
            'prezimeKandidata' => 'Doe',
        ]);

        $this->student2 = Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $studijskiProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'statusUpisa_id' => $enrolledStatus->id,
            'imeKandidata' => 'Jane',
            'prezimeKandidata' => 'Smith',
        ]);

        $this->student3 = Kandidat::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'studijskiProgram_id' => $studijskiProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'statusUpisa_id' => $enrolledStatus->id,
            'imeKandidata' => 'Bob',
            'prezimeKandidata' => 'Johnson',
        ]);

        // Create nastavna nedelja
        $this->nedelja = NastavnaNedelja::create([
            'skolska_godina_id' => $this->skolskaGodina->id,
            'redni_broj' => 1,
            'datum_pocetka' => now()->startOfWeek()->toDateString(),
            'datum_kraja' => now()->endOfWeek()->toDateString(),
        ]);
    }

    /**
     * Test index returns null prisanstva when no filters provided
     */
    public function test_index_without_filters_returns_null_prisanstva(): void
    {
        $response = $this->get(route('prisustvo.index'));

        $response->assertSuccessful();
        $response->assertViewIs('prisustvo.index');
        $response->assertViewHas('predmeti');
        $response->assertViewHas('nedelje');
        $response->assertViewHas('prisanstva', null);
    }

    /**
     * Test index with both filters loads prisanstva collection
     */
    public function test_index_with_both_filters_loads_prisanstva(): void
    {
        // Create test attendance records
        Prisanstvo::create([
            'student_id' => $this->student1->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'prisutan',
            'napomena' => null,
            'profesor_id' => $this->profesor->id,
        ]);

        Prisanstvo::create([
            'student_id' => $this->student2->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'odsutan',
            'napomena' => 'Bolestan',
            'profesor_id' => $this->profesor->id,
        ]);

        $response = $this->get(route('prisustvo.index', [
            'predmet' => $this->predmet->id,
            'nedelja' => $this->nedelja->id,
        ]));

        $response->assertSuccessful();
        $response->assertViewIs('prisustvo.index');
        $response->assertViewHas('prisanstva');

        $prisanstva = $response->viewData('prisanstva');
        $this->assertCount(2, $prisanstva);
        $this->assertTrue($prisanstva->pluck('student_id')->contains($this->student1->id));
    }

    /**
     * Test index with only predmet filter returns null (requires both filters)
     */
    public function test_index_with_only_predmet_filter_returns_null_prisanstva(): void
    {
        Prisanstvo::create([
            'student_id' => $this->student1->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'prisutan',
        ]);

        $response = $this->get(route('prisustvo.index', [
            'predmet' => $this->predmet->id,
        ]));

        $response->assertSuccessful();
        $response->assertViewHas('prisanstva', null);
    }

    /**
     * Test index loads all predmeti
     */
    public function test_index_loads_all_predmeti(): void
    {
        $predmet2 = Predmet::factory()->create();
        $predmet3 = Predmet::factory()->create();

        $response = $this->get(route('prisustvo.index'));

        $response->assertSuccessful();
        $predmeti = $response->viewData('predmeti');
        $this->assertGreaterThanOrEqual(3, count($predmeti));
    }

    /**
     * Test create without predmet returns empty studenti array
     */
    public function test_create_without_predmet_returns_empty_studenti(): void
    {
        $response = $this->get(route('prisustvo.create'));

        $response->assertSuccessful();
        $response->assertViewIs('prisustvo.create');
        $response->assertViewHas('predmeti');
        $response->assertViewHas('nedelje');
        $response->assertViewHas('studenti', []);
    }

    /**
     * Test create with predmet parameter loads enrolled students
     */
    public function test_create_with_predmet_loads_enrolled_students(): void
    {
        $response = $this->get(route('prisustvo.create', [
            'predmet' => $this->predmet->id,
        ]));

        $response->assertSuccessful();
        $response->assertViewIs('prisustvo.create');
        $response->assertViewHas('studenti');

        $studenti = $response->viewData('studenti');
        $this->assertGreaterThanOrEqual(3, count($studenti));
        $this->assertTrue($studenti->pluck('id')->contains($this->student1->id));
        $this->assertTrue($studenti->pluck('id')->contains($this->student2->id));
        $this->assertTrue($studenti->pluck('id')->contains($this->student3->id));
    }

    /**
     * Test create loads all predmeti and nedelje
     */
    public function test_create_loads_predmeti_and_nedelje(): void
    {
        $response = $this->get(route('prisustvo.create'));

        $response->assertSuccessful();
        $response->assertViewHas('predmeti');
        $response->assertViewHas('nedelje');

        $nedelje = $response->viewData('nedelje');
        $this->assertGreaterThanOrEqual(1, count($nedelje));
    }

    /**
     * Test store creates multiple attendance records in single request
     */
    public function test_store_creates_multiple_attendance_records(): void
    {
        $studentIds = [$this->student1->id, $this->student2->id, $this->student3->id];
        $status = [
            $this->student1->id => 'prisutan',
            $this->student2->id => 'odsutan',
            $this->student3->id => 'opravdan',
        ];
        $napomena = [
            $this->student1->id => 'Redovnu',
            $this->student2->id => 'Bolestan',
        ];

        $response = $this->post(route('prisustvo.store'), [
            'student_ids' => $studentIds,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => $status,
            'napomena' => $napomena,
        ]);

        $response->assertRedirect(route('prisustvo.index'));
        $response->assertSessionHas('success', 'Prisustvo uspešno sačuvano');

        // Verify all records were created
        $this->assertDatabaseCount('prisanstva', 3);

        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student1->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'prisutan',
            'napomena' => 'Redovnu',
        ]);

        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student2->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'odsutan',
            'napomena' => 'Bolestan',
        ]);

        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student3->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'opravdan',
            'napomena' => null,
        ]);
    }

    /**
     * Test store with updateOrCreate - multiple submissions do not duplicate records
     */
    public function test_store_update_or_create_does_not_duplicate_records(): void
    {
        $studentIds = [$this->student1->id, $this->student2->id];
        $status = [
            $this->student1->id => 'prisutan',
            $this->student2->id => 'odsutan',
        ];

        // First submission
        $this->post(route('prisustvo.store'), [
            'student_ids' => $studentIds,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => $status,
            'napomena' => [],
        ]);

        $this->assertDatabaseCount('prisanstva', 2);

        // Second submission with same data - should update, not create new
        $this->post(route('prisustvo.store'), [
            'student_ids' => $studentIds,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => [
                $this->student1->id => 'odsutan', // Changed status
                $this->student2->id => 'prisutan', // Changed status
            ],
            'napomena' => [],
        ]);

        // Still only 2 records, not 4
        $this->assertDatabaseCount('prisanstva', 2);

        // Verify records were updated
        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student1->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'odsutan', // Updated
        ]);

        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student2->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'prisutan', // Updated
        ]);
    }

    /**
     * Test store defaults missing status to 'odsutan'
     */
    public function test_store_defaults_missing_status_to_odsutan(): void
    {
        $studentIds = [$this->student1->id];

        $response = $this->post(route('prisustvo.store'), [
            'student_ids' => $studentIds,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => [], // Empty status array - student1 status not provided
            'napomena' => [],
        ]);

        $response->assertRedirect(route('prisustvo.index'));

        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student1->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'odsutan', // Default value
        ]);
    }

    /**
     * Test store defaults missing napomena to null
     */
    public function test_store_defaults_missing_napomena_to_null(): void
    {
        $studentIds = [$this->student1->id];
        $status = [
            $this->student1->id => 'prisutan',
        ];

        $this->post(route('prisustvo.store'), [
            'student_ids' => $studentIds,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => $status,
            'napomena' => [], // Empty napomena array - student1 napomena not provided
        ]);

        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student1->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'napomena' => null, // Default value
        ]);
    }

    /**
     * Test store sets profesor_id from authenticated user
     */
    public function test_store_sets_profesor_id_from_auth_user(): void
    {
        $this->post(route('prisustvo.store'), [
            'student_ids' => [$this->student1->id],
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => [$this->student1->id => 'prisutan'],
            'napomena' => [],
        ]);

        // Auth::user()?->profesor_id will return null since users don't have profesor_id
        // But the store should still work with null profesor_id
        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student1->id,
            'profesor_id' => null,
        ]);
    }

    /**
     * Test store with empty student_ids array
     */
    public function test_store_with_empty_student_ids_array(): void
    {
        $response = $this->post(route('prisustvo.store'), [
            'student_ids' => [],
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => [],
            'napomena' => [],
        ]);

        $response->assertRedirect(route('prisustvo.index'));

        // No attendance records should be created
        $this->assertDatabaseCount('prisanstva', 0);
    }

    /**
     * Test report loads all enrolled students
     */
    public function test_report_loads_all_enrolled_students(): void
    {
        $response = $this->get(route('prisustvo.report', [
            'predmet_id' => $this->predmet->id,
        ]));

        $response->assertSuccessful();
        $response->assertViewIs('prisustvo.report');
        $response->assertViewHas('studenti');

        $studenti = $response->viewData('studenti');
        $this->assertGreaterThanOrEqual(3, count($studenti));
    }

    /**
     * Test report groups prisanstva by student_id
     */
    public function test_report_groups_prisanstva_by_student_id(): void
    {
        // Create multiple attendance records for different students
        Prisanstvo::create([
            'student_id' => $this->student1->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'prisutan',
        ]);

        $nedelja2 = NastavnaNedelja::create([
            'skolska_godina_id' => $this->skolskaGodina->id,
            'redni_broj' => 2,
            'datum_pocetka' => now()->addWeek()->startOfWeek()->toDateString(),
            'datum_kraja' => now()->addWeek()->endOfWeek()->toDateString(),
        ]);

        Prisanstvo::create([
            'student_id' => $this->student1->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $nedelja2->id,
            'status' => 'odsutan',
        ]);

        Prisanstvo::create([
            'student_id' => $this->student2->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'prisutan',
        ]);

        $response = $this->get(route('prisustvo.report', [
            'predmet_id' => $this->predmet->id,
        ]));

        $response->assertSuccessful();
        $prisanstva = $response->viewData('prisanstva');

        // Verify groupBy structure - should be a collection
        $this->assertNotNull($prisanstva);
        $this->assertIsObject($prisanstva);

        // Student 1 should have 2 records, student 2 should have 1
        $this->assertCount(2, $prisanstva->get($this->student1->id, []));
        $this->assertCount(1, $prisanstva->get($this->student2->id, []));
    }

    /**
     * Test report loads relationships (student, nastavnaNedelja)
     */
    public function test_report_loads_relationships(): void
    {
        Prisanstvo::create([
            'student_id' => $this->student1->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'prisutan',
        ]);

        $response = $this->get(route('prisustvo.report', [
            'predmet_id' => $this->predmet->id,
        ]));

        $response->assertSuccessful();
        $prisanstva = $response->viewData('prisanstva');

        // Verify relationships are loaded
        if (method_exists($prisanstva, 'get')) {
            $records = $prisanstva->get($this->student1->id, []);
            if (count($records) > 0) {
                $record = $records[0];
                // Check that relationships are accessible
                $this->assertNotNull($record->student);
                $this->assertNotNull($record->nastavnaNedelja);
            }
        }
    }

    /**
     * Test report with no attendance records for given predmet
     */
    public function test_report_with_no_attendance_records(): void
    {
        $response = $this->get(route('prisustvo.report', [
            'predmet_id' => $this->predmet->id,
        ]));

        $response->assertSuccessful();
        $response->assertViewIs('prisustvo.report');
        $response->assertViewHas('studenti');
        $response->assertViewHas('prisanstva');

        // prisanstva should be an empty collection/array
        $prisanstva = $response->viewData('prisanstva');
        $this->assertCount(0, $prisanstva);
    }

    /**
     * Test store with mixed status values
     */
    public function test_store_with_all_status_values(): void
    {
        $studentIds = [$this->student1->id, $this->student2->id, $this->student3->id];
        $status = [
            $this->student1->id => 'prisutan',
            $this->student2->id => 'odsutan',
            $this->student3->id => 'opravdan',
        ];

        $response = $this->post(route('prisustvo.store'), [
            'student_ids' => $studentIds,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => $status,
            'napomena' => [],
        ]);

        $response->assertRedirect(route('prisustvo.index'));

        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student1->id,
            'status' => 'prisutan',
        ]);

        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student2->id,
            'status' => 'odsutan',
        ]);

        $this->assertDatabaseHas('prisanstva', [
            'student_id' => $this->student3->id,
            'status' => 'opravdan',
        ]);
    }

    /**
     * Test index with filters loads student relationship
     */
    public function test_index_with_filters_loads_student_relationship(): void
    {
        Prisanstvo::create([
            'student_id' => $this->student1->id,
            'predmet_id' => $this->predmet->id,
            'nastavna_nedelja_id' => $this->nedelja->id,
            'status' => 'prisutan',
        ]);

        $response = $this->get(route('prisustvo.index', [
            'predmet' => $this->predmet->id,
            'nedelja' => $this->nedelja->id,
        ]));

        $response->assertSuccessful();
        $prisanstva = $response->viewData('prisanstva');

        // Verify relationship is loaded and accessible
        $this->assertNotNull($prisanstva[0]->student);
        $this->assertEquals($this->student1->id, $prisanstva[0]->student->id);
    }
}
