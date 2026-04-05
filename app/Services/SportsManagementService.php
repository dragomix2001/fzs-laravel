<?php

declare(strict_types=1);

namespace App\Services;

use App\SportskoAngazovanje;
use Illuminate\Database\Eloquent\Collection;

/**
 * Sports Management Service - Centralized sports engagement management.
 *
 * This is a helper service extracted from KandidatService to centralize all sports
 * engagement (SportskoAngazovanje) operations for candidates.
 *
 * Responsibilities:
 * - Create sports engagement records for candidates
 * - Retrieve sports engagements for a candidate
 * - Delete all sports engagements when candidate is deleted
 *
 * @see KandidatService (original implementation)
 * @see SportskoAngazovanje (sports engagement model)
 */
class SportsManagementService
{
    /**
     * Create a sports engagement record for a kandidat.
     *
     * Stores information about a candidate's sports club membership and activity duration.
     * Used during candidate registration when sports engagement is declared.
     *
     * @param  int  $kandidatId  The kandidat ID
     * @param  array  $data  Sports data with structure:
     *                       [
     *                       'sport' => $sport_id,
     *                       'klub' => $nazivKluba,
     *                       'uzrast' => $odDoGodina,
     *                       'godine' => $ukupnoGodina
     *                       ]
     * @return SportskoAngazovanje Created sports engagement instance
     */
    public function createSportForKandidat(int $kandidatId, array $data): SportskoAngazovanje
    {
        $sport = new SportskoAngazovanje;
        $sport->sport_id = $data['sport'];
        $sport->kandidat_id = $kandidatId;
        $sport->nazivKluba = $data['klub'];
        $sport->odDoGodina = $data['uzrast'];
        $sport->ukupnoGodina = $data['godine'];
        $sport->save();

        return $sport;
    }

    /**
     * Get all sports engagements for a kandidat.
     *
     * @param  int  $kandidatId  The kandidat ID
     * @return Collection Sports engagement records
     */
    public function getSportsForKandidat(int $kandidatId)
    {
        return SportskoAngazovanje::where('kandidat_id', $kandidatId)->get();
    }

    /**
     * Delete all sports engagements for a kandidat.
     *
     * Used when deleting a kandidat to ensure all related sports records are removed.
     * Prevents orphaned SportskoAngazovanje records in database.
     *
     * @param  int  $kandidatId  The kandidat ID
     * @return int Number of deleted records
     */
    public function deleteSportsForKandidat(int $kandidatId): int
    {
        return SportskoAngazovanje::where('kandidat_id', $kandidatId)->delete();
    }
}
