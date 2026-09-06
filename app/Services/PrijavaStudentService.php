<?php

namespace App\Services;

use App\Models\AktivniIspitniRokovi;
use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaOdbrane;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PredmetProgram;
use App\Models\Profesor;
use App\Models\ProfesorPredmet;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipPrijave;
use App\Models\TipStudija;

class PrijavaStudentService
{
    public function getSvePrijaveZaStudenta(int $kandidatId): array
    {
        $kandidat = Kandidat::find($kandidatId);
        $prijave = $kandidat->prijaveIspita()->get();

        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidatId,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();
        $diplomskiRadOdbrana = DiplomskiPrijavaOdbrane::where([
            'kandidat_id' => $kandidatId,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();
        $diplomskiRadPolaganje = DiplomskiPolaganje::where([
            'kandidat_id' => $kandidatId,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();
        $ispiti = PolozeniIspiti::where([
            'kandidat_id' => $kandidatId,
            'indikatorAktivan' => 1,
        ])->get();

        return compact(
            'kandidat', 'prijave',
            'diplomskiRadTema', 'diplomskiRadOdbrana', 'diplomskiRadPolaganje',
            'ispiti'
        );
    }

    public function getCreatePrijavaIspitaStudentData(int $kandidatId): array
    {
        $kandidat = Kandidat::find($kandidatId);
        $brojeviIndeksa = Kandidat::where([
            'statusUpisa_id' => 1,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'tipStudija_id' => $kandidat->tipStudija_id,
            'godinaStudija_id' => $kandidat->godinaStudija_id,
        ])->select('id', 'BrojIndeksa as naziv')->get();
        $predmeti = PredmetProgram::where([
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->orderBy('semestar')->get();
        $studijskiProgram = StudijskiProgram::where('id', $kandidat->studijskiProgram_id)->get();
        $godinaStudija = GodinaStudija::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $ispitniRok = AktivniIspitniRokovi::where('indikatorAktivan', 1)->get();
        $profesor = Profesor::all();

        if ($predmeti->isEmpty()) {
            $profesori = Profesor::all();
        } else {
            $ids = ProfesorPredmet::where('predmet_id', $predmeti->first()->id)
                ->pluck('profesor_id');
            $profesori = Profesor::whereIn('id', $ids)->get();
        }

        $tipPrijave = TipPrijave::all();

        return compact(
            'kandidat', 'brojeviIndeksa', 'predmeti', 'studijskiProgram',
            'godinaStudija', 'tipPredmeta', 'tipStudija', 'ispitniRok',
            'profesor', 'tipPrijave', 'profesori'
        );
    }
}
