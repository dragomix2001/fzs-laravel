<?php

namespace App\Services;

use App\Models\AktivniIspitniRokovi;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\Profesor;
use App\Models\ProfesorPredmet;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipPrijave;
use App\Models\TipStudija;
use Illuminate\Database\Eloquent\Collection;

/**
 * Read-side data and form preparation for exam registration by subject.
 */
class PrijavaPredmetService
{
    /**
     * @return array{tipStudija: Collection, studijskiProgrami: Collection, predmeti: Collection}
     */
    public function getSpisakPredmetaData(): array
    {
        return [
            'tipStudija' => TipStudija::all(),
            'studijskiProgrami' => StudijskiProgram::all(),
            'predmeti' => Predmet::all(),
        ];
    }

    /**
     * @return array{predmet: Predmet, prijave: Collection}
     */
    public function getPrijaveZaPredmet(int $predmetId): array
    {
        $predmetProgrami = PredmetProgram::with([
            'prijaveIspita.kandidat',
            'prijaveIspita.rok',
            'prijaveIspita.profesor',
        ])
            ->where('predmet_id', $predmetId)
            ->get();

        $prijave = new Collection;
        foreach ($predmetProgrami as $predmetProgram) {
            $prijave = $prijave->merge($predmetProgram->prijaveIspita);
        }

        return [
            'predmet' => Predmet::find($predmetId),
            'prijave' => $prijave,
        ];
    }

    public function getCreatePrijavaIspitaPredmetData(int $predmetProgramId): array
    {
        $predmet = PredmetProgram::find($predmetProgramId);
        $kandidat = null;

        $brojeviIndeksa = Kandidat::where([
            'tipStudija_id' => $predmet->tipStudija_id,
            'studijskiProgram_id' => $predmet->studijskiProgram_id,
            'statusUpisa_id' => 1,
        ])->select('id', 'BrojIndeksa as naziv')->get();

        $studijskiProgram = StudijskiProgram::where('id', $predmet->studijskiProgram_id)->get();
        $godinaStudija = GodinaStudija::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $ispitniRok = AktivniIspitniRokovi::where('indikatorAktivan', 1)->get();
        $tipPrijave = TipPrijave::all();

        $profesorPredmet = ProfesorPredmet::where('predmet_id', $predmet->id)
            ->select('profesor_id')
            ->first();
        $profesor = $profesorPredmet === null
            ? Profesor::all()
            : Profesor::where('id', $profesorPredmet->profesor_id)->get();

        return compact(
            'kandidat', 'brojeviIndeksa', 'predmet', 'studijskiProgram',
            'godinaStudija', 'tipPredmeta', 'tipStudija', 'ispitniRok',
            'profesor', 'tipPrijave'
        );
    }

    public function getCreatePrijavaIspitaPredmetManyData(int $predmetId): array
    {
        $predmet = Predmet::find($predmetId);
        $studijskiProgrami = PredmetProgram::where('predmet_id', $predmetId)
            ->pluck('studijskiProgram_id')
            ->all();

        $kandidatiQuery = Kandidat::where('statusUpisa_id', 1)->orderBy('brojIndeksa');
        if ($studijskiProgrami !== []) {
            $kandidatiQuery->whereIn('studijskiProgram_id', $studijskiProgrami);
        }
        $kandidati = $kandidatiQuery->get();

        $kandidatiJson = $kandidati->map(fn (Kandidat $kandidat) => [
            'id' => $kandidat->id,
            'label' => $kandidat->brojIndeksa.' - '.$kandidat->imeKandidata.' '.$kandidat->prezimeKandidata,
            'value' => $kandidat->id,
        ]);

        $ispitniRok = AktivniIspitniRokovi::where('indikatorAktivan', 1)->get();
        $profesor = Profesor::all();
        $godinaStudija = GodinaStudija::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $tipPrijave = TipPrijave::all();
        $studijskiProgram = StudijskiProgram::whereIn('id', $studijskiProgrami)->get();

        return compact(
            'kandidati', 'kandidatiJson', 'predmet', 'studijskiProgram',
            'godinaStudija', 'tipPredmeta', 'tipStudija', 'ispitniRok',
            'profesor', 'tipPrijave'
        );
    }
}
