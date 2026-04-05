<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Kandidat;
use App\Models\UspehSrednjaSkola;
use App\Services\GradeManagementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * GradeManagementServiceTest
 *
 * Comprehensive unit tests for GradeManagementService covering all public methods:
 * - createGradesForKandidat()
 * - updateGradesForKandidat()
 * - getGradesForEdit()
 * - deleteGradesForKandidat()
 *
 * Critical tests:
 * 1. Verifies RedniBrojRazreda bug fix (lines 111, 125, 139, 153)
 *    - Grade 1 must have RedniBrojRazreda = 1
 *    - Grade 2 must have RedniBrojRazreda = 2
 *    - Grade 3 must have RedniBrojRazreda = 3
 *    - Grade 4 must have RedniBrojRazreda = 4
 *
 * 2. Verifies missing grade deletion feature
 *    - deleteGradesForKandidat() removes all grade records
 */
class GradeManagementServiceTest extends TestCase
{
    use DatabaseTransactions;

    private GradeManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GradeManagementService::class);
    }

    /**
     * Test createGradesForKandidat creates all 4 grade records
     */
    #[Test]
    public function test_create_grades_for_kandidat_creates_four_grades(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];

        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $this->assertDatabaseCount('uspeh_srednja_skola', 4);
        $this->assertEquals(4, UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->count());
    }

    /**
     * Test createGradesForKandidat stores correct kandidat_id
     */
    #[Test]
    public function test_create_grades_for_kandidat_stores_correct_kandidat_id(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];

        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $storedGrades = UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->get();
        $this->assertCount(4, $storedGrades);
        foreach ($storedGrades as $grade) {
            $this->assertEquals($kandidat->id, $grade->kandidat_id);
        }
    }

    /**
     * Test createGradesForKandidat stores correct RedniBrojRazreda values
     * CRITICAL BUG FIX: Verifies RedniBrojRazreda is 1, 2, 3, 4 (not all 1)
     */
    #[Test]
    public function test_create_grades_for_kandidat_stores_correct_redni_broj_razreda(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];

        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $grade1 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 1])->first();
        $this->assertNotNull($grade1);
        $this->assertEquals(1, $grade1->RedniBrojRazreda);

        $grade2 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 2])->first();
        $this->assertNotNull($grade2);
        $this->assertEquals(2, $grade2->RedniBrojRazreda);

        $grade3 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 3])->first();
        $this->assertNotNull($grade3);
        $this->assertEquals(3, $grade3->RedniBrojRazreda);

        $grade4 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 4])->first();
        $this->assertNotNull($grade4);
        $this->assertEquals(4, $grade4->RedniBrojRazreda);
    }

    /**
     * Test createGradesForKandidat stores correct opstiUspeh_id
     */
    #[Test]
    public function test_create_grades_for_kandidat_stores_correct_opsti_uspeh_id(): void
    {
        $kandidat = Kandidat::factory()->create();
        $uspehId = 5;

        $grades = [
            ['razred' => 1, 'uspeh' => $uspehId, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => $uspehId, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => $uspehId, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => $uspehId, 'ocena' => 4.0],
        ];

        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $storedGrades = UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->get();
        foreach ($storedGrades as $grade) {
            $this->assertEquals($uspehId, $grade->opstiUspeh_id);
        }
    }

    /**
     * Test createGradesForKandidat stores correct srednja_ocena
     */
    #[Test]
    public function test_create_grades_for_kandidat_stores_correct_srednja_ocena(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];

        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $grade1 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 1])->first();
        $this->assertEquals(3.5, $grade1->srednja_ocena);

        $grade2 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 2])->first();
        $this->assertEquals(3.7, $grade2->srednja_ocena);

        $grade3 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 3])->first();
        $this->assertEquals(3.9, $grade3->srednja_ocena);

        $grade4 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 4])->first();
        $this->assertEquals(4.0, $grade4->srednja_ocena);
    }

    /**
     * Test createGradesForKandidat with single grade
     */
    #[Test]
    public function test_create_grades_for_kandidat_with_single_grade(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
        ];

        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $this->assertDatabaseCount('uspeh_srednja_skola', 1);
        $grade = UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->first();
        $this->assertNotNull($grade);
        $this->assertEquals(1, $grade->RedniBrojRazreda);
    }

    /**
     * Test updateGradesForKandidat updates existing grades
     */
    #[Test]
    public function test_update_grades_for_kandidat_updates_existing_grades(): void
    {
        $kandidat = Kandidat::factory()->create();

        $initialGrades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.0],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.0],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.0],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 3.0],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $initialGrades);

        $updatedGrades = [
            ['razred' => 1, 'uspeh' => 2, 'ocena' => 4.0],
            ['razred' => 2, 'uspeh' => 2, 'ocena' => 3.8],
            ['razred' => 3, 'uspeh' => 2, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 2, 'ocena' => 4.0],
        ];
        $this->service->updateGradesForKandidat($kandidat->id, $updatedGrades);

        $this->assertDatabaseCount('uspeh_srednja_skola', 4);
        $grade1 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 1])->first();
        $this->assertEquals(2, $grade1->opstiUspeh_id);
        $this->assertEquals(4.0, $grade1->srednja_ocena);
    }

    /**
     * Test updateGradesForKandidat creates missing grades
     */
    #[Test]
    public function test_update_grades_for_kandidat_creates_missing_grades(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $this->assertDatabaseCount('uspeh_srednja_skola', 1);

        $allGrades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];
        $this->service->updateGradesForKandidat($kandidat->id, $allGrades);

        $this->assertDatabaseCount('uspeh_srednja_skola', 4);
        $this->assertEquals(4, UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->count());
    }

    /**
     * Test updateGradesForKandidat maintains RedniBrojRazreda
     */
    #[Test]
    public function test_update_grades_for_kandidat_maintains_redni_broj_razreda(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.0],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.0],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.0],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 3.0],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $updatedGrades = [
            ['razred' => 1, 'uspeh' => 2, 'ocena' => 4.0],
            ['razred' => 2, 'uspeh' => 2, 'ocena' => 4.0],
            ['razred' => 3, 'uspeh' => 2, 'ocena' => 4.0],
            ['razred' => 4, 'uspeh' => 2, 'ocena' => 4.0],
        ];
        $this->service->updateGradesForKandidat($kandidat->id, $updatedGrades);

        $grade1 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 1])->first();
        $this->assertEquals(1, $grade1->RedniBrojRazreda);

        $grade2 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 2])->first();
        $this->assertEquals(2, $grade2->RedniBrojRazreda);

        $grade3 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 3])->first();
        $this->assertEquals(3, $grade3->RedniBrojRazreda);

        $grade4 = UspehSrednjaSkola::where(['kandidat_id' => $kandidat->id, 'RedniBrojRazreda' => 4])->first();
        $this->assertEquals(4, $grade4->RedniBrojRazreda);
    }

    /**
     * Test getGradesForEdit returns all 4 grades
     */
    #[Test]
    public function test_get_grades_for_edit_returns_all_4_grades(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $result = $this->service->getGradesForEdit($kandidat->id);

        $this->assertArrayHasKey('prviRazred', $result);
        $this->assertArrayHasKey('drugiRazred', $result);
        $this->assertArrayHasKey('treciRazred', $result);
        $this->assertArrayHasKey('cetvrtiRazred', $result);
        $this->assertCount(4, $result);
    }

    /**
     * Test getGradesForEdit returns correct RedniBrojRazreda for grade 1
     * CRITICAL BUG FIX: Verifies RedniBrojRazreda=1 for first grade
     */
    #[Test]
    public function test_get_grades_for_edit_prvrazred_has_correct_redni_broj_razreda(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $result = $this->service->getGradesForEdit($kandidat->id);

        $this->assertEquals(1, $result['prviRazred']->RedniBrojRazreda);
    }

    /**
     * Test getGradesForEdit returns correct RedniBrojRazreda for grade 2
     * CRITICAL BUG FIX: Verifies RedniBrojRazreda=2 (not 1)
     */
    #[Test]
    public function test_get_grades_for_edit_drugi_razred_has_correct_redni_broj_razreda(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $result = $this->service->getGradesForEdit($kandidat->id);

        $this->assertEquals(2, $result['drugiRazred']->RedniBrojRazreda);
    }

    /**
     * Test getGradesForEdit returns correct RedniBrojRazreda for grade 3
     * CRITICAL BUG FIX: Verifies RedniBrojRazreda=3 (not 1)
     */
    #[Test]
    public function test_get_grades_for_edit_treci_razred_has_correct_redni_broj_razreda(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $result = $this->service->getGradesForEdit($kandidat->id);

        $this->assertEquals(3, $result['treciRazred']->RedniBrojRazreda);
    }

    /**
     * Test getGradesForEdit returns correct RedniBrojRazreda for grade 4
     * CRITICAL BUG FIX: Verifies RedniBrojRazreda=4 (not 1)
     */
    #[Test]
    public function test_get_grades_for_edit_cetvrti_razred_has_correct_redni_broj_razreda(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $result = $this->service->getGradesForEdit($kandidat->id);

        $this->assertEquals(4, $result['cetvrtiRazred']->RedniBrojRazreda);
    }

    /**
     * Test getGradesForEdit returns default grades for missing grades
     */
    #[Test]
    public function test_get_grades_for_edit_returns_default_grades_for_missing_grades(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $result = $this->service->getGradesForEdit($kandidat->id);

        $this->assertEquals(1, $result['prviRazred']->RedniBrojRazreda);
        $this->assertEquals(3.5, $result['prviRazred']->srednja_ocena);

        $this->assertEquals(0, $result['drugiRazred']->kandidat_id);
        $this->assertEquals(0, $result['drugiRazred']->srednja_ocena);
        $this->assertEquals(1, $result['drugiRazred']->opstiUspeh_id);

        $this->assertEquals(0, $result['treciRazred']->kandidat_id);
        $this->assertEquals(0, $result['treciRazred']->srednja_ocena);
        $this->assertEquals(1, $result['treciRazred']->opstiUspeh_id);

        $this->assertEquals(0, $result['cetvrtiRazred']->kandidat_id);
        $this->assertEquals(0, $result['cetvrtiRazred']->srednja_ocena);
        $this->assertEquals(1, $result['cetvrtiRazred']->opstiUspeh_id);
    }

    /**
     * Test getGradesForEdit returns existing grade values
     */
    #[Test]
    public function test_get_grades_for_edit_returns_existing_grade_values(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 5, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 6, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 7, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 8, 'ocena' => 4.0],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $result = $this->service->getGradesForEdit($kandidat->id);

        $this->assertEquals(5, $result['prviRazred']->opstiUspeh_id);
        $this->assertEquals(3.5, $result['prviRazred']->srednja_ocena);

        $this->assertEquals(6, $result['drugiRazred']->opstiUspeh_id);
        $this->assertEquals(3.7, $result['drugiRazred']->srednja_ocena);

        $this->assertEquals(7, $result['treciRazred']->opstiUspeh_id);
        $this->assertEquals(3.9, $result['treciRazred']->srednja_ocena);

        $this->assertEquals(8, $result['cetvrtiRazred']->opstiUspeh_id);
        $this->assertEquals(4.0, $result['cetvrtiRazred']->srednja_ocena);
    }

    /**
     * Test getGradesForEdit with empty database (all defaults)
     */
    #[Test]
    public function test_get_grades_for_edit_with_no_existing_grades(): void
    {
        $kandidat = Kandidat::factory()->create();

        $result = $this->service->getGradesForEdit($kandidat->id);

        $this->assertEquals(0, $result['prviRazred']->kandidat_id);
        $this->assertEquals(1, $result['prviRazred']->opstiUspeh_id);
        $this->assertEquals(0, $result['prviRazred']->srednja_ocena);
        $this->assertEquals(1, $result['prviRazred']->RedniBrojRazreda);

        $this->assertEquals(0, $result['drugiRazred']->kandidat_id);
        $this->assertEquals(1, $result['drugiRazred']->opstiUspeh_id);
        $this->assertEquals(0, $result['drugiRazred']->srednja_ocena);
        $this->assertEquals(2, $result['drugiRazred']->RedniBrojRazreda);

        $this->assertEquals(0, $result['treciRazred']->kandidat_id);
        $this->assertEquals(1, $result['treciRazred']->opstiUspeh_id);
        $this->assertEquals(0, $result['treciRazred']->srednja_ocena);
        $this->assertEquals(3, $result['treciRazred']->RedniBrojRazreda);

        $this->assertEquals(0, $result['cetvrtiRazred']->kandidat_id);
        $this->assertEquals(1, $result['cetvrtiRazred']->opstiUspeh_id);
        $this->assertEquals(0, $result['cetvrtiRazred']->srednja_ocena);
        $this->assertEquals(4, $result['cetvrtiRazred']->RedniBrojRazreda);
    }

    /**
     * Test deleteGradesForKandidat removes all grades
     * CRITICAL: Verifies missing grade deletion feature
     */
    #[Test]
    public function test_delete_grades_for_kandidat_removes_all_grades(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $this->assertEquals(4, UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->count());

        $this->service->deleteGradesForKandidat($kandidat->id);

        $this->assertEquals(0, UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->count());
    }

    /**
     * Test deleteGradesForKandidat does not affect other kandidats
     */
    #[Test]
    public function test_delete_grades_for_kandidat_does_not_affect_other_kandidats(): void
    {
        $kandidat1 = Kandidat::factory()->create();
        $kandidat2 = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.9],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
        ];
        $this->service->createGradesForKandidat($kandidat1->id, $grades);
        $this->service->createGradesForKandidat($kandidat2->id, $grades);

        $this->assertEquals(4, UspehSrednjaSkola::where('kandidat_id', $kandidat1->id)->count());
        $this->assertEquals(4, UspehSrednjaSkola::where('kandidat_id', $kandidat2->id)->count());

        $this->service->deleteGradesForKandidat($kandidat1->id);

        $this->assertEquals(0, UspehSrednjaSkola::where('kandidat_id', $kandidat1->id)->count());
        $this->assertEquals(4, UspehSrednjaSkola::where('kandidat_id', $kandidat2->id)->count());
    }

    /**
     * Test deleteGradesForKandidat with non-existent kandidat
     */
    #[Test]
    public function test_delete_grades_for_kandidat_with_non_existent_kandidat(): void
    {
        $this->service->deleteGradesForKandidat(999);

        $this->assertEquals(0, UspehSrednjaSkola::where('kandidat_id', 999)->count());
    }

    /**
     * Test deleteGradesForKandidat with partial grades
     */
    #[Test]
    public function test_delete_grades_for_kandidat_with_partial_grades(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.5],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.7],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);

        $this->assertEquals(2, UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->count());

        $this->service->deleteGradesForKandidat($kandidat->id);

        $this->assertEquals(0, UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->count());
    }

    /**
     * Test integration: create -> update -> delete
     */
    #[Test]
    public function test_integration_create_update_delete_workflow(): void
    {
        $kandidat = Kandidat::factory()->create();

        $grades = [
            ['razred' => 1, 'uspeh' => 1, 'ocena' => 3.0],
            ['razred' => 2, 'uspeh' => 1, 'ocena' => 3.0],
            ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.0],
            ['razred' => 4, 'uspeh' => 1, 'ocena' => 3.0],
        ];
        $this->service->createGradesForKandidat($kandidat->id, $grades);
        $this->assertEquals(4, UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->count());

        $result = $this->service->getGradesForEdit($kandidat->id);
        $this->assertCount(4, $result);
        $this->assertEquals(3.0, $result['prviRazred']->srednja_ocena);

        $updatedGrades = [
            ['razred' => 1, 'uspeh' => 2, 'ocena' => 4.0],
            ['razred' => 2, 'uspeh' => 2, 'ocena' => 4.0],
            ['razred' => 3, 'uspeh' => 2, 'ocena' => 4.0],
            ['razred' => 4, 'uspeh' => 2, 'ocena' => 4.0],
        ];
        $this->service->updateGradesForKandidat($kandidat->id, $updatedGrades);
        $this->assertEquals(4, UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->count());

        $result = $this->service->getGradesForEdit($kandidat->id);
        $this->assertEquals(4.0, $result['prviRazred']->srednja_ocena);

        $this->service->deleteGradesForKandidat($kandidat->id);
        $this->assertEquals(0, UspehSrednjaSkola::where('kandidat_id', $kandidat->id)->count());
    }
}
