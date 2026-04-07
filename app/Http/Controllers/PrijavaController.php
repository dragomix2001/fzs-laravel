<?php

namespace App\Http\Controllers;

use App\Models\AktivniIspitniRokovi;
use App\Models\GodinaStudija;
use App\Http\Requests\StoreDiplomskiOdbranaRequest;
use App\Http\Requests\StoreDiplomskiPolaganjeRequest;
use App\Http\Requests\StoreDiplomskiTemaRequest;
use App\Http\Requests\StorePrijavaIspitaPredmetManyRequest;
use App\Http\Requests\StorePrijavaIspitaRequest;
use App\Http\Requests\UpdateDiplomskiOdbranaRequest;
use App\Http\Requests\UpdateDiplomskiPolaganjeRequest;
use App\Http\Requests\UpdateDiplomskiTemaRequest;
use App\Models\Kandidat;
use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaOdbrane;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\ProfesorPredmet;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipPrijave;
use App\Models\TipStudija;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class PrijavaController extends Controller
{
    // region PRIJAVA ISPITA - PREDMET
    //
    //  Deo za prijavu ispita sa strane predmeta
    //

    // Spisak svih predmeta
    public function spisakPredmeta(Request $request)
    {
        $tipStudija = TipStudija::all();

        $predmeti = Predmet::all();

        $studijskiProgrami = StudijskiProgram::all();

        return view('prijava.spisakPredmeta', compact('tipStudija', 'studijskiProgrami', 'predmeti'));
    }

    // Sve prijave ispita za odabrani predmet
    // Predmet se bira iznad
    public function indexPrijavaIspitaPredmet($id)
    {
        $predmetProgram = PredmetProgram::where(['predmet_id' => $id])->get();

        $prijave = new Collection;

        foreach ($predmetProgram as $predmet) {
            $prijave = $prijave->merge($predmet->prijaveIspita);
        }

        $predmet = Predmet::find($id);

        return view('prijava.indexPredmet', compact('predmet', 'prijave'));
    }

    //
    public function createPrijavaIspitaPredmet($id)
    {
        $predmet = PredmetProgram::find($id);
        $kandidat = null;
        $brojeviIndeksa = Kandidat::where([
            'tipStudija_id' => $predmet->tipStudija_id,
            'studijskiProgram_id' => $predmet->studijskiProgram_id,
            'statusUpisa_id' => 1])->
        select('id', 'BrojIndeksa as naziv')->get();
        $studijskiProgram = StudijskiProgram::where(['id' => $predmet->studijskiProgram_id])->get();
        $godinaStudija = GodinaStudija::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $ispitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();
        $profesorPredmet = ProfesorPredmet::where(['predmet_id' => $predmet->id])->select('profesor_id')->first();
        if ($profesorPredmet == null) {
            $profesor = Profesor::all();
        } else {
            $profesor = Profesor::where(['id' => $profesorPredmet->profesor_id])->get();
        }
        $tipPrijave = TipPrijave::all();

        return view('prijava.create', compact('kandidat', 'brojeviIndeksa', 'predmet', 'studijskiProgram', 'godinaStudija',
            'tipPredmeta', 'tipStudija', 'ispitniRok', 'profesor', 'tipPrijave'));
    }

    public function createPrijavaIspitaPredmetMany($id)
    {
        $predmet = Predmet::find($id);

        $studijskiProgrami = PredmetProgram::where(['predmet_id' => $id])->pluck('studijskiProgram_id')->all();

        // Ako predmet ima studijske programe, filtriraj studente
        if (! empty($studijskiProgrami)) {
            $kandidati = Kandidat::where([
                'statusUpisa_id' => 1,
            ])->whereIn('studijskiProgram_id', $studijskiProgrami)->orderBy('brojIndeksa')->get();
        } else {
            // Ako nema studijskih programa, prikaži sve aktivne studente
            $kandidati = Kandidat::where([
                'statusUpisa_id' => 1,
            ])->orderBy('brojIndeksa')->get();
        }

        // Za autocomplete - niz studentata za JavaScript
        $kandidatiJson = $kandidati->map(function ($k) {
            return [
                'id' => $k->id,
                'label' => $k->brojIndeksa.' - '.$k->imeKandidata.' '.$k->prezimeKandidata,
                'value' => $k->id,
            ];
        });

        $ispitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();

        $profesor = Profesor::all();

        $godinaStudija = GodinaStudija::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $tipPrijave = TipPrijave::all();
        $studijskiProgram = StudijskiProgram::whereIn('id', $studijskiProgrami)->get();

        return view('prijava.createManyPredmet', compact('kandidati', 'kandidatiJson', 'predmet', 'studijskiProgram', 'godinaStudija',
            'tipPredmeta', 'tipStudija', 'ispitniRok', 'profesor', 'tipPrijave'));
    }

    public function storePrijavaIspitaPredmetMany(StorePrijavaIspitaPredmetManyRequest $request)
    {
        $errorArray = [];
        $duplicateArray = [];

        if (isset($request->Submit2)) {
            $zapisnik = new ZapisnikOPolaganjuIspita;
            $zapisnik->predmet_id = $request->predmet_id;
            $zapisnik->datum = $request->datum;
            $zapisnik->datum2 = $request->datum2;
            $zapisnik->rok_id = $request->rok_id;
            $zapisnik->profesor_id = $request->profesor_id;
            $zapisnik->save();

            $smerovi = [];
        }

        // Pre-fetch all kandidati by id for O(1) lookups inside the loop
        $kandidatiMap = Kandidat::whereIn('id', $request->odabir)->get()->keyBy('id');

        // Pre-fetch PredmetProgram records for all unique studijskiProgram_id + tipStudija_id combos
        // keyed by "tipStudija_id_studijskiProgram_id_predmet_id" for the first lookup (lines 176-180)
        // and by "predmet_id_studijskiProgram_id" for the second lookup (lines 213-216)
        $predmetProgramMap1 = PredmetProgram::where('predmet_id', $request->predmet_id)
            ->whereIn('studijskiProgram_id', $kandidatiMap->pluck('studijskiProgram_id')->unique()->values())
            ->get()
            ->keyBy(function ($pp) {
                return $pp->tipStudija_id.'_'.$pp->studijskiProgram_id;
            });

        $predmetProgramMap2 = PredmetProgram::where('predmet_id', $request->predmet_id)
            ->whereIn('studijskiProgram_id', $kandidatiMap->pluck('studijskiProgram_id')->unique()->values())
            ->get()
            ->keyBy(function ($pp) {
                return $pp->studijskiProgram_id;
            });

        foreach ($request->odabir as $kandidatId) {

            $kandidat = $kandidatiMap->get($kandidatId);

            $predmetProgramZaPrijavu = $predmetProgramMap1->get($kandidat->tipStudija_id.'_'.$kandidat->studijskiProgram_id);

            if ($predmetProgramZaPrijavu == null) {
                continue;
            }

            $validator = PrijavaIspita::where([
                'kandidat_id' => $kandidatId,
                'rok_id' => $request->rok_id,
                'predmet_id' => $predmetProgramZaPrijavu->id,
            ])->get();

            if (! $validator->isEmpty()) {
                $duplicateArray[] = $kandidat;

                continue;
            }

            $prijava = new PrijavaIspita;
            $prijava->kandidat_id = $kandidatId;
            $prijava->predmet_id = $predmetProgramZaPrijavu->id;
            $prijava->rok_id = $request->rok_id;
            $prijava->profesor_id = $request->profesor_id;
            $prijava->brojPolaganja = 1;
            $prijava->datum = $request->datum;
            $prijava->tipPrijave_id = $request->tipPrijave_id;

            if (isset($request->Submit2)) {
                $zapisnik->predmet_id = $request->predmet_id;
                $zapisnik->save();
            }

            $saved = $prijava->save();

            if (isset($request->Submit2)) {
                $zapisStudent = new ZapisnikOPolaganju_Student;
                $zapisStudent->zapisnik_id = $zapisnik->id;
                $zapisStudent->prijavaIspita_id = $prijava->id;

                $zapisStudent->kandidat_id = $kandidatId;
                $zapisStudent->save();

                $smerovi[] = $kandidat->studijskiProgram_id;

                $programId = $predmetProgramMap2->get($kandidat->studijskiProgram_id)->id;

                $polozenIspit = new PolozeniIspiti;
                $polozenIspit->indikatorAktivan = 0;
                $polozenIspit->kandidat_id = $kandidatId;
                $polozenIspit->predmet_id = $programId;
                $polozenIspit->zapisnik_id = $zapisnik->id;
                $polozenIspit->prijava_id = $prijava->id;
                $polozenIspit->save();
            }

            if (! $saved) {
                $errorArray[] = $kandidatiMap->get($kandidatId);
            }
        }

        if (isset($request->Submit2)) {
            $smerovi = array_unique($smerovi);
            foreach ($smerovi as $id) {
                $zapisSmer = new ZapisnikOPolaganju_StudijskiProgram;
                $zapisSmer->zapisnik_id = $zapisnik->id;
                $zapisSmer->StudijskiProgram_id = $id;
                $zapisSmer->save();
            }
        }

        return view('prijava.rezultat', compact('errorArray', 'duplicateArray'))->with('predmetId', $request->predmet_id);
    }
    // endregion

    // region PRIJAVA ISPITA - STUDENT
    //
    //  Deo za prijavu ispita sa strane studenta
    //

    // Stranica Status studenta
    public function svePrijaveIspitaZaStudenta($id)
    {
        $kandidat = Kandidat::find($id);
        $prijave = $kandidat->prijaveIspita()->get();

        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $diplomskiRadOdbrana = DiplomskiPrijavaOdbrane::where([
            'kandidat_id' => $id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $diplomskiRadPolaganje = DiplomskiPolaganje::where([
            'kandidat_id' => $id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $ispiti = PolozeniIspiti::where([
            'kandidat_id' => $id,
            'indikatorAktivan' => 1,
        ])->get();

        return view('prijava.index', compact(
            'kandidat',
            'prijave',
            'diplomskiRadTema',
            'diplomskiRadOdbrana',
            'diplomskiRadPolaganje',
            'ispiti'
        ));
    }

    public function createPrijavaIspitaStudent($id)
    {
        $kandidat = Kandidat::find($id);
        $brojeviIndeksa = Kandidat::where([
            'statusUpisa_id' => 1,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'tipStudija_id' => $kandidat->tipStudija_id,
            'godinaStudija_id' => $kandidat->godinaStudija_id,
        ])->select('id', 'BrojIndeksa as naziv')->get();

        $predmeti = PredmetProgram::where([
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'tipStudija_id' => $kandidat->tipStudija_id,
            // Godina je uklonjena, da bi mogao da se prijavi ispit iz bilo koje godine
            // 'godinaStudija_id' =>  $kandidat->godinaStudija_id,
        ])->orderBy('semestar')->get();

        $studijskiProgram = StudijskiProgram::where(['id' => $kandidat->studijskiProgram_id])->get();
        $godinaStudija = GodinaStudija::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $ispitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();
        $profesor = Profesor::all();

        // TODO check
        if ($predmeti->isEmpty()) {
            $profesori = Profesor::all();
        } else {
            $profesorPredmet = ProfesorPredmet::where(['predmet_id' => $predmeti->first()->id])->select('profesor_id')->get();
            $ids = array_map(function (ProfesorPredmet $o) {
                return $o->profesor_id;
            }, $profesorPredmet->all());
            $profesori = Profesor::whereIn('id', $ids)->get();
        }

        $tipPrijave = TipPrijave::all();

        return view('prijava.create', compact('kandidat', 'brojeviIndeksa', 'predmeti', 'studijskiProgram', 'godinaStudija',
            'tipPredmeta', 'tipStudija', 'ispitniRok', 'profesor', 'tipPrijave', 'profesori'));

    }
    // endregion

    // region PRIJAVA ISPITA - SAVE/DELETE
    //
    //  Deo za čuvanje i brisanje prijave ispita (Univerzalno)
    //

    public function storePrijavaIspita(StorePrijavaIspitaRequest $request)
    {
        $this->authorize('create', PrijavaIspita::class);

        $prijava = new PrijavaIspita($request->only([
            'kandidat_id',
            'predmet_id',
            'rok_id',
            'profesor_id',
            'brojPolaganja',
            'datum',
            'tipPrijave_id',
        ]));
        $saved = $prijava->save();

        if ($saved) {
            if (! empty($request->prijava_za_predmet)) {
                return redirect("/prijava/zaPredmet/{$request->predmet_id}?tipStudijaId=".$request->tipStudija_id.'&studijskiProgramId='.$request->studijskiProgram_id);
            } else {
                return redirect("/prijava/zaStudenta/{$request->kandidat_id}?tipStudijaId=".$request->tipStudija_id.'&studijskiProgramId='.$request->studijskiProgram_id);
            }
        } else {
            Session::flash('flash-error', 'create');

            return Redirect::back();
        }
    }

    public function deletePrijavaIspita($id, Request $request)
    {
        $prijava = PrijavaIspita::find($id);
        $this->authorize('delete', $prijava);
        $kandidat = $prijava->kandidat_id;
        $predmet = PredmetProgram::find($prijava->predmet_id)->predmet_id;

        $zapisnikStudent = ZapisnikOPolaganju_Student::where(['prijavaIspita_id' => $id])->first();
        $polozeniIspit = PolozeniIspiti::where(['prijava_id' => $id])->first();

        $zapisnikId = 0;
        if ($zapisnikStudent != null) {
            $zapisnikId = $zapisnikStudent->zapisnik_id;
            $zapisnikStudent->delete();
        }

        if ($polozeniIspit != null) {
            $polozeniIspit->delete();
        }

        PrijavaIspita::destroy($id);

        $zapisnikProvera = ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnikId,
        ])->get();

        if ($zapisnikId != 0 && $zapisnikProvera->count() == 0) {
            ZapisnikOPolaganjuIspita::destroy($zapisnikId);
        }

        if ($request->prijava == 'predmet') {
            return redirect("/prijava/zaPredmet/{$predmet}");
        } else {
            return redirect("/prijava/zaStudenta/{$kandidat}");
        }
    }

    // AJAX call
    public function vratiKandidataPrijava(Request $request)
    {
        $kandidat = Kandidat::find($request->id);
        $predmetProgram = PredmetProgram::where(['tipStudija_id' => $kandidat->tipStudija_id, 'studijskiProgram_id' => $kandidat->studijskiProgram_id])->get();

        $stringPredmeti = '';
        foreach ($predmetProgram as $item) {
            $stringPredmeti .= "<option value='{$item->id}'>{$item->predmet->naziv}</option>";
        }

        return ['student' => $kandidat, 'predmeti' => $stringPredmeti];
    }

    // AJAX call
    public function vratiPredmetPrijava(Request $request)
    {
        //        $kandidat = Kandidat::find($request->kandidat);
        $predmetProgram = PredmetProgram::find($request->id);
        $profesorPredmet = ProfesorPredmet::where(['predmet_id' => $request->id])->select('profesor_id')->get();

        if ($profesorPredmet->isEmpty()) {
            $profesori = Profesor::all();
        } else {
            $ids = array_map(function (ProfesorPredmet $o) {
                return $o->profesor_id;
            }, $profesorPredmet->all());
            $profesori = Profesor::whereIn('id', $ids)->get();
        }
        $stringProfesori = '';
        foreach ($profesori as $item) {
            $stringProfesori .= "<option value='{$item->id}'>".$item->zvanje.' '.$item->ime.' '.$item->prezime.'</option>';
        }

        return ['tipPredmeta' => $predmetProgram->tipPredmeta_id,
            'godinaStudija' => $predmetProgram->godinaStudija_id,
            'tipStudija' => $predmetProgram->tipStudija_id,
            'profesori' => $stringProfesori];
    }

    // AJAX call
    public function vratiKandidataPoBroju(Request $request)
    {
        $kandidat = Kandidat::find($request->id);

        return '<tr>'.
        "<td><input type='checkbox' name='odabir[]' value='$kandidat->id' checked></td>".
        "<td>{$kandidat->brojIndeksa}</td>".
        '<td>'.$kandidat->imeKandidata.' '.$kandidat->prezimeKandidata.'</td>'.
        "<td>{$kandidat->godinaStudija->nazivRimski}</td></tr>";
    }
    // endregion

    // region DIPLOMSKI RAD - TEMA

    public function diplomskiTema($kandidat)
    {
        $kandidat = Kandidat::findOrFail($kandidat);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();

        return view('prijava.diplomskiTema', compact('kandidat', 'profesor', 'predmeti'));
    }

    public function storeDiplomskiTema(StoreDiplomskiTemaRequest $request)
    {
        $prijavaTeme = new DiplomskiPrijavaTeme($request->all());
        $prijavaTeme->save();

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function editdiplomskiTema($kandidat)
    {
        $kandidat = Kandidat::findOrFail($kandidat);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();
        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        return view('prijava.editDiplomskiTema', compact('kandidat', 'profesor', 'predmeti', 'diplomskiRadTema'));
    }

    public function updateDiplomskiTema(UpdateDiplomskiTemaRequest $request)
    {
        $prijavaTeme = DiplomskiPrijavaTeme::find($request->diplomskiTema_id);
        $prijavaTeme->fill($request->all());
        if (! isset($request->indikatorOdobreno)) {
            $prijavaTeme->indikatorOdobreno = 0;
        } else {
            $prijavaTeme->indikatorOdobreno = 1;
        }
        $prijavaTeme->save();

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function deleteDiplomskiTema($kandidat)
    {
        $kandidat = Kandidat::findOrFail($kandidat);
        $prijavaTeme = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $prijavaTeme->delete();

        return redirect('/prijava/zaStudenta/'.$kandidat->id);
    }
    // endregion

    // region DIPLOMSKI RAD - ODBRANA
    public function diplomskiOdbrana($kandidat)
    {
        $kandidat = Kandidat::findOrFail($kandidat);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();
        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();
        if ($diplomskiRadTema == null) {
            return 'Не постоји пријава теме дипломског рада!';
        }

        return view('prijava.odbrana.diplomskiOdbrana', compact('kandidat', 'profesor', 'predmeti', 'diplomskiRadTema'));
    }

    public function storeDiplomskiOdbrana(StoreDiplomskiOdbranaRequest $request)
    {
        $prijavaOdbrane = new DiplomskiPrijavaOdbrane($request->all());
        $prijavaOdbrane->save();

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function editDiplomskiOdbrana($kandidat)
    {
        $kandidat = Kandidat::findOrFail($kandidat);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();
        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();
        $diplomskiRadOdbrana = DiplomskiPrijavaOdbrane::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        return view('prijava.odbrana.editDiplomskiOdbrana',
            compact('kandidat', 'profesor', 'predmeti', 'diplomskiRadTema', 'diplomskiRadOdbrana'));
    }

    public function updateDiplomskiOdbrana(UpdateDiplomskiOdbranaRequest $request)
    {
        $prijavaOdbrane = DiplomskiPrijavaOdbrane::find($request->diplomskiRadOdbrana_id);
        $prijavaOdbrane->fill($request->all());
        if (! isset($request->indikatorOdobreno)) {
            $prijavaOdbrane->indikatorOdobreno = 0;
        } else {
            $prijavaOdbrane->indikatorOdobreno = 1;
        }
        $prijavaOdbrane->save();

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function deleteDiplomskiOdbrana($kandidat)
    {
        $kandidat = Kandidat::findOrFail($kandidat);
        $prijavaOdbrane = DiplomskiPrijavaOdbrane::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $prijavaOdbrane->delete();

        return redirect('/prijava/zaStudenta/'.$kandidat->id);
    }
    // endregion

    // region DIPLOMSKI RAD - POLAGANJE
    public function diplomskiPolaganje($kandidat)
    {
        $kandidat = Kandidat::findOrFail($kandidat);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();
        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();
        $ispitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();

        return view('prijava.polaganje.diplomskiPolaganje', compact('kandidat', 'profesor', 'predmeti',
            'diplomskiRadTema', 'ispitniRok'));
    }

    public function storeDiplomskiPolaganje(StoreDiplomskiPolaganjeRequest $request)
    {
        $prijavaPolaganje = new DiplomskiPolaganje($request->all());
        $prijavaPolaganje->save();

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function editDiplomskiPolaganje($kandidat)
    {
        $kandidat = Kandidat::findOrFail($kandidat);
        $profesor = Profesor::all();
        $predmeti = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar', 'asc')->get();
        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();
        $diplomskiRadPolaganje = DiplomskiPolaganje::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();
        $ispitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();

        return view('prijava.polaganje.editDiplomskiPolaganje',
            compact('kandidat', 'profesor', 'predmeti', 'diplomskiRadTema', 'diplomskiRadPolaganje', 'ispitniRok'));
    }

    public function updateDiplomskiPolaganje(UpdateDiplomskiPolaganjeRequest $request)
    {
        $prijavaPolaganje = DiplomskiPolaganje::find($request->polaganje_id);
        $prijavaPolaganje->fill($request->all());

        $prijavaPolaganje->save();

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function deleteDiplomskiPolaganje($kandidat)
    {
        $kandidat = Kandidat::findOrFail($kandidat);
        $prijavaOdbrane = DiplomskiPolaganje::where([
            'kandidat_id' => $kandidat->id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $prijavaOdbrane->delete();

        return redirect('/prijava/zaStudenta/'.$kandidat->id);
    }
    // endregion

    // region PRIVREMENI DEO
    //
    //  Privremeni deo za unos priznatih ispita retroaktivno
    //

    public function unosPrivremeni($kandidat)
    {
        $kandidat = Kandidat::findOrFail($kandidat);
        $ispiti = PredmetProgram::where([
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->get();
        $polozeniIspiti = PolozeniIspiti::where(['kandidat_id' => $kandidat->id])->get();

        return view('upis.unosPrivremeni', compact('kandidat', 'ispiti', 'polozeniIspiti'));
    }

    public function vratiIspitPoId(Request $request)
    {
        $predmet = PredmetProgram::find($request->id);

        return '<tr>'.
        "<td><input type='checkbox' name='odabir[$predmet->id]' value='$predmet->id' checked></td>".
        "<td>{$predmet->predmet->naziv}</td>".
        "<td><select class='konacnaOcena' data-index='$predmet->id' name='konacnaOcena[$predmet->id]'>".
        "<option value='0'></option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option></select></td></tr>";
    }

    public function dodajPolozeneIspite(Request $request)
    {
        // dd($request->all());
        $kandidat = $request->kandidat_id;
        $ispiti = $request->odabir;

        foreach ($ispiti as $index => $ispit) {
            $novIspit = new PolozeniIspiti;
            $novIspit->prijava_id = null;
            $novIspit->zapisnik_id = null;
            $novIspit->kandidat_id = $kandidat;
            $novIspit->predmet_id = $ispit;
            $novIspit->ocenaPismeni = 0;
            $novIspit->ocenaUsmeni = 0;
            $novIspit->konacnaOcena = $request->konacnaOcena[$index];
            $novIspit->brojBodova = 0;
            $novIspit->statusIspita = 1;
            $novIspit->odluka_id = 0;
            $novIspit->indikatorAktivan = 1;
            $novIspit->save();
        }

        return Redirect::back();

    }
    // endregion
}
