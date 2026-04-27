<?php

namespace App\Services;

use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use Carbon\Carbon;

/**
 * Ispit Membership Service — student add/remove within existing exam records.
 *
 * Responsibilities:
 * - Adding students to an existing Zapisnik (with PrijavaIspita and PolozeniIspiti creation)
 * - Removing students from an existing Zapisnik (with cleanup of related records)
 *
 * @see IspitService  For the main orchestrator that delegates here
 * @see IspitController
 */
class IspitMembershipService
{
    /**
     * Add more students to an existing exam record (Zapisnik).
     *
     * Skips students already enrolled. Skips students whose study program
     * has no matching PredmetProgram for the zapisnik subject.
     * Creates new PrijavaIspita, ZapisnikOPolaganju_Student, and PolozeniIspiti
     * records for each newly added student.
     *
     * @param  int  $zapisnikId  The record ID to update
     * @param  array  $odabir  List of student IDs to add
     */
    public function addStudentToZapisnik(int $zapisnikId, array $odabir): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::find($zapisnikId);

        $prijavljeniStudenti = ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnikId,
        ])->pluck('kandidat_id')->all();

        $prijavljeniSmerovi = ZapisnikOPolaganju_StudijskiProgram::where([
            'zapisnik_id' => $zapisnikId,
        ])->pluck('studijskiProgram_id')->all();

        $smerovi = [];

        $kandidatiMap = Kandidat::whereIn('id', $odabir)->get()->keyBy('id');

        $studijskiProgramIds = $kandidatiMap->pluck('studijskiProgram_id')->unique()->all();
        $predmetProgramMap = PredmetProgram::where('predmet_id', $zapisnik->predmet_id)
            ->whereIn('studijskiProgram_id', $studijskiProgramIds)
            ->get()
            ->groupBy('studijskiProgram_id');

        foreach ($odabir as $id) {
            if (in_array($id, $prijavljeniStudenti)) {
                // student is already enrolled in this zapisnik — skip
                continue;
            }

            $kandidat = $kandidatiMap->get($id);
            $predmetProgram = $predmetProgramMap->get($kandidat->studijskiProgram_id)?->first();

            if ($predmetProgram === null) {
                continue;
            }

            $novaPrijava = new PrijavaIspita;
            $novaPrijava->kandidat_id = $id;
            $novaPrijava->predmet_id = $predmetProgram->id;
            $novaPrijava->profesor_id = $zapisnik->profesor_id;
            $novaPrijava->rok_id = $zapisnik->rok_id;
            $novaPrijava->brojPolaganja = 1;
            $novaPrijava->datum = Carbon::now();
            $novaPrijava->datum2 = Carbon::now();
            $novaPrijava->vreme = $zapisnik->vreme;
            $novaPrijava->tipPrijave_id = 0;
            $novaPrijava->save();

            $zapisStudent = new ZapisnikOPolaganju_Student;
            $zapisStudent->zapisnik_id = $zapisnik->id;
            $zapisStudent->prijavaIspita_id = $novaPrijava->id;
            $zapisStudent->kandidat_id = $id;
            $zapisStudent->save();

            if (! in_array($kandidat->studijskiProgram_id, $prijavljeniSmerovi)) {
                $smerovi[] = $kandidat->studijskiProgram_id;
            }

            $polozenIspit = new PolozeniIspiti;
            $polozenIspit->indikatorAktivan = false;
            $polozenIspit->kandidat_id = $id;
            $polozenIspit->predmet_id = $predmetProgram->id;
            $polozenIspit->zapisnik_id = $zapisnik->id;
            $polozenIspit->prijava_id = $novaPrijava->id;
            $polozenIspit->save();
        }

        $smerovi = array_unique($smerovi);
        foreach ($smerovi as $id) {
            $zapisSmer = new ZapisnikOPolaganju_StudijskiProgram;
            $zapisSmer->zapisnik_id = $zapisnik->id;
            $zapisSmer->StudijskiProgram_id = $id;
            $zapisSmer->save();
        }
    }

    /**
     * Remove a student from an exam record and clean up associated records.
     *
     * Deletes the ZapisnikOPolaganju_Student and PolozeniIspiti records.
     * If the zapisnik becomes empty after removal, the zapisnik itself is deleted.
     *
     * @param  int  $zapisnikId  The record ID
     * @param  int  $kandidatId  The student ID to remove
     * @return bool True if the zapisnik was also deleted (became empty), false otherwise
     */
    public function removeStudentFromZapisnik(int $zapisnikId, int $kandidatId): bool
    {
        ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnikId,
            'kandidat_id' => $kandidatId,
        ])->delete();

        PolozeniIspiti::where([
            'zapisnik_id' => $zapisnikId,
            'kandidat_id' => $kandidatId,
        ])->delete();

        $remaining = ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnikId,
        ])->count();

        if ($remaining === 0) {
            ZapisnikOPolaganjuIspita::destroy($zapisnikId);

            return true;
        }

        return false;
    }
}
