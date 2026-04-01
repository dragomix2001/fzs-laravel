<?php

namespace App\Http\Controllers;

use App\DTOs\KandidatData;
use App\GodinaStudija;
use App\Http\Requests\StoreKandidatRequest;
use App\Http\Requests\StoreMasterKandidatRequest;
use App\Http\Requests\UpdateKandidatRequest;
use App\Http\Requests\UpdateMasterKandidatRequest;
use App\Kandidat;
use App\Opstina;
use App\OpstiUspeh;
use App\PrilozenaDokumenta;
use App\Services\KandidatService;
use App\SkolskaGodUpisa;
use App\Sport;
use App\SportskoAngazovanje;
use App\StatusStudiranja;
use App\StudijskiProgram;
use App\TipStudija;
use App\UspehSrednjaSkola;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KandidatController extends Controller
{
    public function __construct(protected KandidatService $kandidatService)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $studijskiProgramId = $this->kandidatService->getActiveStudijskiProgramOsnovne();
        if (! empty($request->studijskiProgramId)) {
            $studijskiProgramId = $request->studijskiProgramId;
        }

        $studijskiProgrami = $this->kandidatService->getStudijskiProgrami(1);

        $kandidati = Kandidat::where(['tipStudija_id' => 1, 'statusUpisa_id' => 3, 'studijskiProgram_id' => $studijskiProgramId])->get();

        return view('kandidat.indeks')
            ->with('kandidati', $kandidati)
            ->with('studijskiProgrami', $studijskiProgrami);
    }

    public function create()
    {
        $dropdownData = $this->kandidatService->getDropdownData();
        $dropdownData['studijskiProgram'] = StudijskiProgram::where('tipStudija_id', '1')->get();

        return view('kandidat.create_part_1', $dropdownData);
    }

    public function store(StoreKandidatRequest $request)
    {
        if ($request->page == 1) {

            $data = KandidatData::fromRequest($request);
            $kandidat = $this->kandidatService->storeKandidatPage1($data, $request);
            $insertedId = $kandidat->id;

            $dokumentiPrvaGodina = PrilozenaDokumenta::where('skolskaGodina_id', '1')->get();
            $dokumentiOstaleGodine = PrilozenaDokumenta::where('skolskaGodina_id', '2')->get();

            return view('kandidat.create_part_2')
                ->with('mestoZavrseneSkoleFakulteta', Opstina::all())
                ->with('opstiUspehSrednjaSkola', OpstiUspeh::all())
                ->with('uspehSrednjaSkola', UspehSrednjaSkola::all())
                ->with('prilozeniDokumentPrvaGodina', PrilozenaDokumenta::all())
                ->with('statusaUpisaKandidata', StatusStudiranja::all())
                ->with('studijskiProgram', StudijskiProgram::all())
                ->with('tipStudija', TipStudija::all())
                ->with('godinaStudija', GodinaStudija::all())
                ->with('skolskeGodineUpisa', SkolskaGodUpisa::all())
                ->with('insertedId', $insertedId)
                ->with('sport', Sport::all())
                ->with('dokumentiPrvaGodina', $dokumentiPrvaGodina)
                ->with('dokumentiOstaleGodine', $dokumentiOstaleGodine);

        } elseif ($request->page == 2) {

            $this->kandidatService->storeKandidatPage2($request);

            return redirect('/kandidat/');
        }
    }

    public function show($id)
    {
        $kandidat = Kandidat::find($id)->toArray();

        return view('kandidat.details')->with('kandidat', $kandidat);
    }

    public function edit($id)
    {
        $kandidat = Kandidat::find($id);
        $editData = $this->kandidatService->getEditDropdownData($id);

        return view('kandidat.update', array_merge(['kandidat' => $kandidat], $editData));
    }

    public function update(UpdateKandidatRequest $request, $id)
    {
        $kandidat = Kandidat::find($id);

        $data = KandidatData::fromRequest($request);
        $kandidat = $this->kandidatService->updateKandidat($id, $data, $request);

        $saved = ! empty($kandidat->id);

        if ($saved) {
            Session::flash('flash-success', 'update');
            if (! empty($request->submitstay)) {
                return redirect("/kandidat/{$kandidat->id}/edit");
            }
            if ($kandidat->statusUpisa_id == 1) {
                return redirect("/student/index/1?godina={$kandidat->godinaStudija_id}&studijskiProgramId={$kandidat->studijskiProgram_id}");
            }

            return redirect('/kandidat?studijskiProgramId='.$kandidat->studijskiProgram_id);
        } else {
            Session::flash('flash-error', 'update');
            if ($kandidat->statusUpisa_id == 1) {
                return redirect("/student/index/1?godina={$kandidat->godinaStudija_id}&studijskiProgramId={$kandidat->studijskiProgram_id}");
            }

            return redirect('/kandidat?studijskiProgramId=1'.$kandidat->studijskiProgram_id);
        }
    }

    public function destroy($id)
    {
        $deleted = $this->kandidatService->deleteKandidat($id);

        if ($deleted) {
            Session::flash('flash-success', 'delete');
        } else {
            Session::flash('flash-error', 'delete');
        }

        return \Redirect::back();
    }

    public function sport($id)
    {
        $kandidat = Kandidat::find($id);
        $sportskoAngazovanje = SportskoAngazovanje::where('kandidat_id', $id)->get();
        $sport = Sport::all();

        return view('kandidat.sportsko_angazovanje')
            ->with('sport', $sport)
            ->with('kandidat', $kandidat)
            ->with('sportskoAngazovanje', $sportskoAngazovanje)
            ->with('id', $id);
    }

    public function sportStore(Request $request, $id)
    {
        $kandidat = Kandidat::find($id);
        $sportskoAngazovanje = SportskoAngazovanje::where('kandidat_id', $id)->get();
        $sportovi = Sport::all();

        $this->kandidatService->storeSport($id, $request->all());

        return redirect("/kandidat/{$id}/sportskoangazovanje")
            ->with('sport', $sportovi)
            ->with('kandidat', $kandidat)
            ->with('sportskoAngazovanje', $sportskoAngazovanje)
            ->with('id', $id);
    }

    public function testPost(Request $request)
    {
        return $request->all();
    }

    public function createMaster(Request $request)
    {
        $dropdownData = $this->kandidatService->getDropdownDataMaster();

        return view('kandidat.create_master', $dropdownData);
    }

    public function storeMaster(StoreMasterKandidatRequest $request)
    {
        $kandidat = $this->kandidatService->storeMasterKandidat($request);

        return redirect('/master?studijskiProgramId='.$kandidat->studijskiProgram_id);
    }

    public function editMaster($id)
    {
        $kandidat = Kandidat::find($id);
        $editData = $this->kandidatService->getEditDropdownDataMaster($id);

        return view('kandidat.update_master', array_merge(['kandidat' => $kandidat], $editData));
    }

    public function updateMaster(UpdateMasterKandidatRequest $request, $id)
    {
        $kandidat = Kandidat::find($id);

        $kandidat = $this->kandidatService->updateMasterKandidat($id, $request);
        $saved = ! empty($kandidat->id);

        if ($saved) {
            Session::flash('flash-success', 'update');
            if (! empty($request->submitstay)) {
                return redirect("/master/{$kandidat->id}/edit");
            }
            if ($kandidat->statusUpisa_id == 1) {
                return redirect("/student/index/2?studijskiProgramId={$kandidat->studijskiProgram_id}");
            }

            return redirect("/master?studijskiProgramId={$kandidat->studijskiProgram_id}");
        } else {
            Session::flash('flash-error', 'update');
            if ($kandidat->statusUpisa_id == 1) {
                return redirect("/student/index/2?studijskiProgramId={$kandidat->studijskiProgram_id}");
            }

            return redirect("/master?studijskiProgramId={$kandidat->studijskiProgram_id}");
        }
    }

    public function indexMaster(Request $request)
    {
        $studijskiProgram = StudijskiProgram::where(['tipStudija_id' => 2, 'indikatorAktivan' => 1])->first();

        if (! $studijskiProgram) {
            return view('kandidat.index_master')
                ->with('kandidati', collect())
                ->with('studijskiProgrami', collect());
        }

        $studijskiProgramId = $studijskiProgram->id;
        if (! empty($request->studijskiProgramId)) {
            $studijskiProgramId = $request->studijskiProgramId;
        }

        $studijskiProgrami = StudijskiProgram::where(['tipStudija_id' => 2, 'indikatorAktivan' => 1])->get();

        $kandidati = Kandidat::where(['tipStudija_id' => 2, 'statusUpisa_id' => 3, 'studijskiProgram_id' => $studijskiProgramId])->get();

        return view('kandidat.index_master')
            ->with('kandidati', $kandidati)
            ->with('studijskiProgrami', $studijskiProgrami);
    }

    public function destroyMaster($id)
    {
        $deleted = $this->kandidatService->deleteMasterKandidat($id);

        if ($deleted) {
            Session::flash('flash-success', 'delete');

            return \Redirect::back();
        } else {
            Session::flash('flash-error', 'delete');

            return \Redirect::back();
        }
    }

    public function upisKandidata($id)
    {
        $result = $this->kandidatService->upisKandidata($id);

        if (! $result['success']) {
            Session::flash('flash-error', 'upis');
            if ($result['tipStudija_id'] == 1) {
                return redirect('/kandidat/');
            }

            return redirect('/master/');
        }

        Session::flash('flash-success', 'upis');

        if ($result['tipStudija_id'] == 1) {
            return redirect('/kandidat/');
        } elseif ($result['tipStudija_id'] == 2) {
            return redirect('/master/');
        }
    }

    public function masovnaUplata(Request $request)
    {
        $this->kandidatService->masovnaUplata($request->odabir);

        return redirect('/kandidat/');
    }

    public function masovniUpis(Request $request)
    {
        $success = $this->kandidatService->masovniUpis($request->odabir);

        if (! $success) {
            Session::flash('flash-error', 'upis');
        }

        return redirect('/kandidat/');
    }

    public function masovnaUplataMaster(Request $request)
    {
        $this->kandidatService->masovnaUplataMaster($request->odabir);

        return redirect('/master/');
    }

    public function masovniUpisMaster(Request $request)
    {
        $this->kandidatService->masovniUpisMaster($request->odabir);

        return redirect('/master/');
    }

    public function registracijaKandidata($id)
    {
        $this->kandidatService->registracijaKandidata($id);

        return redirect('/kandidat/');
    }
}
