<?php

namespace App\Services;

use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PredmetProgram;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;

/**
 * Creates exam records and their initial student/program memberships.
 */
class IspitZapisnikCreationService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int>  $odabir
     */
    public function create(array $data, array $odabir): ZapisnikOPolaganjuIspita
    {
        $zapisnik = new ZapisnikOPolaganjuIspita($data);
        $zapisnik->save();

        $kandidatiMap = Kandidat::whereIn('id', $odabir)->get()->keyBy('id');
        $studijskiProgramIds = $kandidatiMap->pluck('studijskiProgram_id')->unique()->all();
        $predmetProgramMap = PredmetProgram::where('predmet_id', $data['predmet_id'])
            ->whereIn('studijskiProgram_id', $studijskiProgramIds)
            ->get()
            ->keyBy('studijskiProgram_id');
        $smerovi = [];

        foreach ($odabir as $id) {
            $kandidat = $kandidatiMap->get($id);
            $predmetProgram = $predmetProgramMap->get($kandidat->studijskiProgram_id);

            $zapisStudent = new ZapisnikOPolaganju_Student;
            $zapisStudent->zapisnik_id = $zapisnik->id;
            $zapisStudent->prijavaIspita_id = $zapisnik->prijavaIspita_id;
            $zapisStudent->kandidat_id = $id;
            $zapisStudent->save();

            $smerovi[] = $kandidat->studijskiProgram_id;

            $polozenIspit = new PolozeniIspiti;
            $polozenIspit->indikatorAktivan = false;
            $polozenIspit->kandidat_id = $id;
            $polozenIspit->predmet_id = $predmetProgram->id;
            $polozenIspit->zapisnik_id = $zapisnik->id;
            $polozenIspit->prijava_id = $zapisnik->prijavaIspita_id;
            $polozenIspit->save();
        }

        foreach (array_unique($smerovi) as $smerId) {
            $zapisSmer = new ZapisnikOPolaganju_StudijskiProgram;
            $zapisSmer->zapisnik_id = $zapisnik->id;
            $zapisSmer->StudijskiProgram_id = $smerId;
            $zapisSmer->save();
        }

        return $zapisnik;
    }
}
