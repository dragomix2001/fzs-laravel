<?php

namespace App\Services;

use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;

class BulkPrijavaService
{
    /** @param array<string, mixed> $data */
    public function store(array $data): array
    {
        $errorArray = [];
        $duplicateArray = [];
        $zapisnik = null;

        if ($data['withZapisnik']) {
            $zapisnik = new ZapisnikOPolaganjuIspita;
            $zapisnik->predmet_id = $data['predmet_id'];
            $zapisnik->datum = $data['datum'];
            $zapisnik->datum2 = $data['datum2'] ?? null;
            $zapisnik->rok_id = $data['rok_id'];
            $zapisnik->profesor_id = $data['profesor_id'];
            $zapisnik->save();
        }

        $kandidatiMap = Kandidat::whereIn('id', $data['odabir'])->get()->keyBy('id');
        $programIds = $kandidatiMap->pluck('studijskiProgram_id')->unique()->values();
        $predmetProgrami = PredmetProgram::where('predmet_id', $data['predmet_id'])
            ->whereIn('studijskiProgram_id', $programIds)
            ->get();
        $predmetProgramMap = $predmetProgrami->keyBy(fn (PredmetProgram $program) => $program->tipStudija_id.'_'.$program->studijskiProgram_id);
        $predmetProgramByProgram = $predmetProgrami->keyBy('studijskiProgram_id');
        $smerovi = [];

        foreach ($data['odabir'] as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $predmetProgram = $predmetProgramMap->get($kandidat->tipStudija_id.'_'.$kandidat->studijskiProgram_id);
            if ($predmetProgram === null) {
                continue;
            }

            if (PrijavaIspita::where([
                'kandidat_id' => $kandidatId,
                'rok_id' => $data['rok_id'],
                'predmet_id' => $predmetProgram->id,
            ])->exists()) {
                $duplicateArray[] = $kandidat;

                continue;
            }

            $prijava = new PrijavaIspita;
            $prijava->kandidat_id = $kandidatId;
            $prijava->predmet_id = $predmetProgram->id;
            $prijava->rok_id = $data['rok_id'];
            $prijava->profesor_id = $data['profesor_id'];
            $prijava->brojPolaganja = 1;
            $prijava->datum = $data['datum'];
            $prijava->tipPrijave_id = $data['tipPrijave_id'];

            if ($zapisnik !== null) {
                $zapisnik->save();
            }
            $saved = $prijava->save();

            if ($zapisnik !== null) {
                $zapisStudent = new ZapisnikOPolaganju_Student;
                $zapisStudent->zapisnik_id = $zapisnik->id;
                $zapisStudent->prijavaIspita_id = $prijava->id;
                $zapisStudent->kandidat_id = $kandidatId;
                $zapisStudent->save();

                $smerovi[] = $kandidat->studijskiProgram_id;
                $polozenIspit = new PolozeniIspiti;
                $polozenIspit->indikatorAktivan = false;
                $polozenIspit->kandidat_id = $kandidatId;
                $polozenIspit->predmet_id = $predmetProgramByProgram->get($kandidat->studijskiProgram_id)->id;
                $polozenIspit->zapisnik_id = $zapisnik->id;
                $polozenIspit->prijava_id = $prijava->id;
                $polozenIspit->save();
            }

            if (! $saved) {
                $errorArray[] = $kandidat;
            }
        }

        if ($zapisnik !== null) {
            foreach (array_unique($smerovi) as $smerId) {
                $zapisSmer = new ZapisnikOPolaganju_StudijskiProgram;
                $zapisSmer->zapisnik_id = $zapisnik->id;
                $zapisSmer->StudijskiProgram_id = $smerId;
                $zapisSmer->save();
            }
        }

        return compact('errorArray', 'duplicateArray');
    }
}
