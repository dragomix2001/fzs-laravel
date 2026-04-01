<?php

namespace App\Http\Controllers;

use App\DTOs\ZapisnikData;
use App\Http\Requests\DodajStudentaRequest;
use App\Http\Requests\StoreZapisnikRequest;
use App\Services\IspitService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class IspitController extends Controller
{
    public function __construct(protected IspitService $ispitService)
    {
        $this->middleware('auth');
    }

    public function indexZapisnik(Request $request)
    {
        $data = $this->ispitService->getZapisniciForIndex($request->all());

        return view('ispit.indexZapisnik', $data);
    }

    public function createZapisnik()
    {
        $data = $this->ispitService->getCreateZapisnikData();
        $rok_id = null;
        $predmet_id = null;

        return view('ispit.createZapisnik', array_merge($data, compact('rok_id', 'predmet_id')));
    }

    public function vratiZapisnikPredmet(Request $request)
    {
        return $this->ispitService->getZapisnikPredmetData((int) $request->rokId);
    }

    public function vratiZapisnikStudenti(Request $request)
    {
        return $this->ispitService->getZapisnikStudenti(
            (int) $request->predmet_id,
            (int) $request->rok_id,
            (int) $request->profesor_id
        );
    }

    public function storeZapisnik(StoreZapisnikRequest $request)
    {
        try {
            $data = ZapisnikData::fromRequest($request);
            $this->ispitService->storeZapisnik($data);
        } catch (QueryException $ex) {
            Session::flash('flash-error', 'create');
        }

        return redirect('/zapisnik/');
    }

    public static function deleteZapisnikAndChildren($id)
    {
        app(IspitService::class)->deleteZapisnikWithChildren((int) $id);
    }

    public function deleteZapisnik($id)
    {
        $this->ispitService->deleteZapisnik((int) $id);

        return \Redirect::back();
    }

    public function pregledZapisnik($zapisnikId)
    {
        $data = $this->ispitService->getZapisnikPregled((int) $zapisnikId);

        return view('ispit.pregledZapisnik', $data);
    }

    public function polozeniIspit(Request $request)
    {
        $zapisnikId = $this->ispitService->savePolozeniIspiti(
            $request->ispit_id,
            $request->ocenaPismeni ?? [],
            $request->ocenaUsmeni ?? [],
            $request->konacnaOcena ?? [],
            $request->brojBodova ?? [],
            $request->statusIspita ?? []
        );

        return redirect('/zapisnik/pregled/'.$zapisnikId);
    }

    public function priznavanjeIspita($id)
    {
        $data = $this->ispitService->getPriznavanjeData((int) $id);

        return view('ispit.priznatiIspiti', $data);
    }

    public function storePriznatiIspiti(Request $request)
    {
        $this->ispitService->storePriznatiIspiti(
            (int) $request->kandidat_id,
            $request->predmetId,
            $request->konacnaOcena ?? []
        );

        return redirect("/prijava/zaStudenta/{$request->kandidat_id}");
    }

    public function deletePriznatIspit($id)
    {
        $kandidatId = $this->ispitService->deletePriznatIspit((int) $id);

        return redirect("/prijava/zaStudenta/{$kandidatId}");
    }

    public function deletePrivremeniIspit($id)
    {
        $this->ispitService->deletePrivremeniIspit((int) $id);

        return Redirect::back();
    }

    public function deletePolozeniIspit($id, Request $request)
    {
        $this->ispitService->deletePolozeniIspit((int) $id, (int) ($request->brisiZapisnik ?? 0));

        return Redirect::back();
    }

    public function pregledZapisnikDelete($zapisnikId, $kandidatId)
    {
        $zapisnikDeleted = $this->ispitService->removeStudentFromZapisnik((int) $zapisnikId, (int) $kandidatId);

        if ($zapisnikDeleted) {
            return redirect('/zapisnik');
        }

        return \Redirect::back();
    }

    public function dodajStudenta(DodajStudentaRequest $request)
    {
        $zapisnikId = $request->zapisnikId;

        try {
            $this->ispitService->addStudentToZapisnik((int) $zapisnikId, $request->odabir);
        } catch (QueryException $ex) {
            Session::flash('flash-error', 'Дошло је до грешке!');
        }

        return redirect('/zapisnik/pregled/'.$zapisnikId);
    }

    public function izmeniPodatke(Request $request)
    {
        $this->ispitService->updateZapisnikDetails((int) $request->zapisnikId, $request->only(['vreme', 'ucionica', 'datum', 'datum2']));

        return Redirect::back();
    }

    public function arhivaZapisnik()
    {
        $data = $this->ispitService->getArhiviraniZapisnici();

        return view('ispit.arhivaZapisnik', $data);
    }

    public function arhivirajZapisnik($id)
    {
        $this->ispitService->arhivirajZapisnik((int) $id);

        return Redirect::back();
    }

    public function arhivirajZapisnikeZaIspitniRok(Request $requset)
    {
        $this->ispitService->arhivirajZapisnikeZaRok((int) $requset->rok_id);

        return Redirect::back();
    }
}
