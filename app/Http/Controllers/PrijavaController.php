<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiplomskiOdbranaRequest;
use App\Http\Requests\StoreDiplomskiPolaganjeRequest;
use App\Http\Requests\StoreDiplomskiTemaRequest;
use App\Http\Requests\StorePrijavaIspitaPredmetManyRequest;
use App\Http\Requests\StorePrijavaIspitaRequest;
use App\Http\Requests\UpdateDiplomskiOdbranaRequest;
use App\Http\Requests\UpdateDiplomskiPolaganjeRequest;
use App\Http\Requests\UpdateDiplomskiTemaRequest;
use App\Models\PrijavaIspita;
use App\Services\DiplomskiPrijavaService;
use App\Services\PrijavaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class PrijavaController extends Controller
{
    public function __construct(
        protected PrijavaService $prijavaService,
        protected DiplomskiPrijavaService $diplomskiPrijavaService,
    ) {}

    // region PRIJAVA ISPITA - PREDMET

    public function spisakPredmeta(Request $request)
    {
        return view('prijava.spisakPredmeta', $this->prijavaService->getSpisakPredmetaData());
    }

    public function indexPrijavaIspitaPredmet($id)
    {
        return view('prijava.indexPredmet', $this->prijavaService->getPrijaveZaPredmet((int) $id));
    }

    public function createPrijavaIspitaPredmet($id)
    {
        return view('prijava.create', $this->prijavaService->getCreatePrijavaIspitaPredmetData((int) $id));
    }

    public function createPrijavaIspitaPredmetMany($id)
    {
        return view('prijava.createManyPredmet', $this->prijavaService->getCreatePrijavaIspitaPredmetManyData((int) $id));
    }

    public function storePrijavaIspitaPredmetMany(StorePrijavaIspitaPredmetManyRequest $request)
    {
        $result = $this->prijavaService->storePrijavaIspitaPredmetMany([
            'odabir' => $request->odabir,
            'predmet_id' => $request->predmet_id,
            'rok_id' => $request->rok_id,
            'profesor_id' => $request->profesor_id,
            'datum' => $request->datum,
            'datum2' => $request->datum2,
            'tipPrijave_id' => $request->tipPrijave_id,
            'withZapisnik' => isset($request->Submit2),
        ]);

        return view('prijava.rezultat', $result)->with('predmetId', $request->predmet_id);
    }

    // endregion

    // region PRIJAVA ISPITA - STUDENT

    public function svePrijaveIspitaZaStudenta($id)
    {
        return view('prijava.index', $this->prijavaService->getSvePrijaveZaStudenta((int) $id));
    }

    public function createPrijavaIspitaStudent($id)
    {
        return view('prijava.create', $this->prijavaService->getCreatePrijavaIspitaStudentData((int) $id));
    }

    // endregion

    // region PRIJAVA ISPITA - SAVE/DELETE

    public function storePrijavaIspita(StorePrijavaIspitaRequest $request)
    {
        $this->authorize('create', PrijavaIspita::class);

        $prijava = $this->prijavaService->storePrijavaIspita($request->only([
            'kandidat_id',
            'predmet_id',
            'rok_id',
            'profesor_id',
            'brojPolaganja',
            'datum',
            'tipPrijave_id',
        ]));

        if ($prijava->exists) {
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

        $result = $this->prijavaService->deletePrijavaIspita((int) $id);

        if ($request->prijava == 'predmet') {
            return redirect("/prijava/zaPredmet/{$result['predmet_id']}");
        } else {
            return redirect("/prijava/zaStudenta/{$result['kandidat_id']}");
        }
    }

    public function vratiKandidataPrijava(Request $request)
    {
        return $this->prijavaService->vratiKandidataPrijava((int) $request->id);
    }

    public function vratiPredmetPrijava(Request $request)
    {
        return $this->prijavaService->vratiPredmetPrijava((int) $request->id);
    }

    public function vratiKandidataPoBroju(Request $request)
    {
        return $this->prijavaService->vratiKandidataPoBroju((int) $request->id);
    }

    // endregion

    // region DIPLOMSKI RAD - TEMA

    public function diplomskiTema($kandidat)
    {
        return view('prijava.diplomskiTema', $this->diplomskiPrijavaService->getDiplomskiTemaData((int) $kandidat));
    }

    public function storeDiplomskiTema(StoreDiplomskiTemaRequest $request)
    {
        $prijavaTeme = $this->diplomskiPrijavaService->storeDiplomskiTema($request->all());

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function editdiplomskiTema($kandidat)
    {
        return view('prijava.editDiplomskiTema', $this->diplomskiPrijavaService->getEditDiplomskiTemaData((int) $kandidat));
    }

    public function updateDiplomskiTema(UpdateDiplomskiTemaRequest $request)
    {
        $this->diplomskiPrijavaService->updateDiplomskiTema(
            (int) $request->diplomskiTema_id,
            $request->all(),
            isset($request->indikatorOdobreno)
        );

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function deleteDiplomskiTema($kandidat)
    {
        $kandidat = $this->diplomskiPrijavaService->deleteDiplomskiTema((int) $kandidat);

        return redirect('/prijava/zaStudenta/'.$kandidat->id);
    }

    // endregion

    // region DIPLOMSKI RAD - ODBRANA

    public function diplomskiOdbrana($kandidat)
    {
        $data = $this->diplomskiPrijavaService->getDiplomskiOdbranaData((int) $kandidat);

        if ($data['diplomskiRadTema'] === null) {
            return 'Не постоји пријава теме дипломског рада!';
        }

        return view('prijava.odbrana.diplomskiOdbrana', $data);
    }

    public function storeDiplomskiOdbrana(StoreDiplomskiOdbranaRequest $request)
    {
        $this->diplomskiPrijavaService->storeDiplomskiOdbrana($request->all());

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function editDiplomskiOdbrana($kandidat)
    {
        return view('prijava.odbrana.editDiplomskiOdbrana', $this->diplomskiPrijavaService->getEditDiplomskiOdbranaData((int) $kandidat));
    }

    public function updateDiplomskiOdbrana(UpdateDiplomskiOdbranaRequest $request)
    {
        $this->diplomskiPrijavaService->updateDiplomskiOdbrana(
            (int) $request->diplomskiRadOdbrana_id,
            $request->all(),
            isset($request->indikatorOdobreno)
        );

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function deleteDiplomskiOdbrana($kandidat)
    {
        $kandidat = $this->diplomskiPrijavaService->deleteDiplomskiOdbrana((int) $kandidat);

        return redirect('/prijava/zaStudenta/'.$kandidat->id);
    }

    // endregion

    // region DIPLOMSKI RAD - POLAGANJE

    public function diplomskiPolaganje($kandidat)
    {
        return view('prijava.polaganje.diplomskiPolaganje', $this->diplomskiPrijavaService->getDiplomskiPolaganjeData((int) $kandidat));
    }

    public function storeDiplomskiPolaganje(StoreDiplomskiPolaganjeRequest $request)
    {
        $this->diplomskiPrijavaService->storeDiplomskiPolaganje($request->all());

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function editDiplomskiPolaganje($kandidat)
    {
        return view('prijava.polaganje.editDiplomskiPolaganje', $this->diplomskiPrijavaService->getEditDiplomskiPolaganjeData((int) $kandidat));
    }

    public function updateDiplomskiPolaganje(UpdateDiplomskiPolaganjeRequest $request)
    {
        $this->diplomskiPrijavaService->updateDiplomskiPolaganje((int) $request->polaganje_id, $request->all());

        return redirect('/prijava/zaStudenta/'.$request->kandidat_id);
    }

    public function deleteDiplomskiPolaganje($kandidat)
    {
        $kandidat = $this->diplomskiPrijavaService->deleteDiplomskiPolaganje((int) $kandidat);

        return redirect('/prijava/zaStudenta/'.$kandidat->id);
    }

    // endregion

    // region PRIVREMENI DEO

    public function unosPrivremeni($kandidat)
    {
        return view('upis.unosPrivremeni', $this->prijavaService->getUnosPrivremeniData((int) $kandidat));
    }

    public function vratiIspitPoId(Request $request)
    {
        return $this->prijavaService->vratiIspitPoId((int) $request->id);
    }

    public function dodajPolozeneIspite(Request $request)
    {
        $this->prijavaService->dodajPolozeneIspite(
            (int) $request->kandidat_id,
            $request->odabir,
            $request->konacnaOcena
        );

        return Redirect::back();
    }

    // endregion
}
