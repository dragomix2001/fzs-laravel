<?php

namespace App\Services;

use App\Models\UspehSrednjaSkola;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Grade Management Service - Centralized high school grade management.
 *
 * This is a helper service extracted from KandidatService to centralize all UspehSrednjaSkola
 * (high school grade) operations. Improves testability, maintainability, and enables grade
 * management reuse across multiple controllers and services.
 *
 * **CRITICAL BUG FIX**: Fixes RedniBrojRazreda default bug in getGradesForEdit where all
 * default instances were incorrectly set to RedniBrojRazreda=1 instead of 1,2,3,4.
 *
 * **MISSING FEATURE**: Adds deleteGradesForKandidat which was completely missing from
 * deleteKandidat method, leaving orphaned UspehSrednjaSkola records in database.
 *
 * Grade Structure (4-year high school):
 * - Each kandidat has exactly 4 UspehSrednjaSkola records (RedniBrojRazreda: 1, 2, 3, 4)
 * - Each record contains: general success indicator, average grade for that year
 * - Used for: enrollment recommendations, scholarship calculations
 *
 * @see KandidatService (original implementation with bugs)
 * @see UspehSrednjaSkola (high school grade model)
 */
class GradeManagementService
{
    /**
     * Create high school grades for a new kandidat.
     *
     * Creates 4 UspehSrednjaSkola records (one for each high school year) from array input.
     * Used during kandidat registration (storeKandidatPage2).
     *
     * @param  int  $kandidatId  The ID of the newly created kandidat
     * @param  array  $grades  Array of grade data with structure:
     *                         [
     *                         ['razred' => 1, 'uspeh' => $opstiUspeh_id, 'ocena' => $srednja_ocena],
     *                         ['razred' => 2, 'uspeh' => $opstiUspeh_id, 'ocena' => $srednja_ocena],
     *                         ['razred' => 3, 'uspeh' => $opstiUspeh_id, 'ocena' => $srednja_ocena],
     *                         ['razred' => 4, 'uspeh' => $opstiUspeh_id, 'ocena' => $srednja_ocena],
     *                         ]
     */
    public function createGradesForKandidat(int $kandidatId, array $grades): void
    {
        foreach ($grades as $grade) {
            UspehSrednjaSkola::create([
                'kandidat_id' => $kandidatId,
                'opstiUspeh_id' => $grade['uspeh'],
                'srednja_ocena' => $grade['ocena'],
                'RedniBrojRazreda' => $grade['razred'],
            ]);
        }
    }

    /**
     * Update (or create if missing) high school grades for existing kandidat.
     *
     * Uses updateOrCreate pattern to handle partial grade updates. If a grade for
     * a specific razred doesn't exist, it will be created; otherwise, it will be updated.
     *
     * @param  int  $kandidatId  The ID of the kandidat
     * @param  array  $grades  Array of grade data (same format as createGradesForKandidat)
     */
    public function updateGradesForKandidat(int $kandidatId, array $grades): void
    {
        foreach ($grades as $grade) {
            UspehSrednjaSkola::updateOrCreate(
                ['kandidat_id' => $kandidatId, 'RedniBrojRazreda' => $grade['razred']],
                ['opstiUspeh_id' => $grade['uspeh'], 'srednja_ocena' => $grade['ocena']]
            );
        }
    }

    /**
     * Get all high school grades for a kandidat, with defaults for missing grades.
     *
     * Retrieves all 4 grades for edit forms. If a grade doesn't exist, creates a default
     * UspehSrednjaSkola instance with:
     * - kandidat_id = 0
     * - opstiUspeh_id = 1 (default general success)
     * - srednja_ocena = 0
     * - RedniBrojRazreda = respective grade number (1, 2, 3, or 4)
     *
     * **FIX**: Correctly sets RedniBrojRazreda to 1, 2, 3, 4 (was buggy in getEditDropdownData,
     * all defaults had RedniBrojRazreda=1).
     *
     * @param  int  $kandidatId  The ID of the kandidat
     * @return array Associative array with keys: 'prviRazred', 'drugiRazred', 'treciRazred', 'cetvrtiRazred'
     *               Each value is an UspehSrednjaSkola model instance (existing or default)
     */
    public function getGradesForEdit(int $kandidatId): array
    {
        $grades = [];

        // Fetch or create default for grade 1
        try {
            $grades['prviRazred'] = UspehSrednjaSkola::where([
                'kandidat_id' => $kandidatId,
                'RedniBrojRazreda' => 1,
            ])->firstOrFail();
        } catch (ModelNotFoundException) {
            $grades['prviRazred'] = new UspehSrednjaSkola;
            $grades['prviRazred']->kandidat_id = 0;
            $grades['prviRazred']->opstiUspeh_id = 1;
            $grades['prviRazred']->srednja_ocena = 0;
            $grades['prviRazred']->RedniBrojRazreda = 1;
        }

        // Fetch or create default for grade 2
        try {
            $grades['drugiRazred'] = UspehSrednjaSkola::where([
                'kandidat_id' => $kandidatId,
                'RedniBrojRazreda' => 2,
            ])->firstOrFail();
        } catch (ModelNotFoundException) {
            $grades['drugiRazred'] = new UspehSrednjaSkola;
            $grades['drugiRazred']->kandidat_id = 0;
            $grades['drugiRazred']->opstiUspeh_id = 1;
            $grades['drugiRazred']->srednja_ocena = 0;
            $grades['drugiRazred']->RedniBrojRazreda = 2;
        }

        // Fetch or create default for grade 3
        try {
            $grades['treciRazred'] = UspehSrednjaSkola::where([
                'kandidat_id' => $kandidatId,
                'RedniBrojRazreda' => 3,
            ])->firstOrFail();
        } catch (ModelNotFoundException) {
            $grades['treciRazred'] = new UspehSrednjaSkola;
            $grades['treciRazred']->kandidat_id = 0;
            $grades['treciRazred']->opstiUspeh_id = 1;
            $grades['treciRazred']->srednja_ocena = 0;
            $grades['treciRazred']->RedniBrojRazreda = 3;
        }

        // Fetch or create default for grade 4
        try {
            $grades['cetvrtiRazred'] = UspehSrednjaSkola::where([
                'kandidat_id' => $kandidatId,
                'RedniBrojRazreda' => 4,
            ])->firstOrFail();
        } catch (ModelNotFoundException) {
            $grades['cetvrtiRazred'] = new UspehSrednjaSkola;
            $grades['cetvrtiRazred']->kandidat_id = 0;
            $grades['cetvrtiRazred']->opstiUspeh_id = 1;
            $grades['cetvrtiRazred']->srednja_ocena = 0;
            $grades['cetvrtiRazred']->RedniBrojRazreda = 4;
        }

        return $grades;
    }

    /**
     * Delete all high school grades for a kandidat.
     *
     * Removes all 4 UspehSrednjaSkola records associated with a kandidat.
     * Called during kandidat deletion to prevent orphaned records.
     *
     * **MISSING FEATURE**: This method was completely missing from deleteKandidat,
     * leaving orphaned grade records in the database.
     *
     * @param  int  $kandidatId  The ID of the kandidat whose grades should be deleted
     */
    public function deleteGradesForKandidat(int $kandidatId): void
    {
        UspehSrednjaSkola::where('kandidat_id', $kandidatId)->delete();
    }
}
