<?php

namespace App\Http\Controllers;

use App\DTOs\KandidatPage1Data;
use App\DTOs\KandidatPage2Data;
use App\DTOs\KandidatUpdateData;
use App\DTOs\MasterKandidatData;
use App\Http\Requests\DodajStudentaRequest;
use App\Http\Requests\KandidatSportRequest;
use App\Http\Requests\StoreKandidatRequest;
use App\Http\Requests\StoreMasterKandidatRequest;
use App\Http\Requests\UpdateKandidatRequest;
use App\Http\Requests\UpdateMasterKandidatRequest;
use App\Services\KandidatEnrollmentService;
use App\Services\KandidatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KandidatController extends Controller
{
    public function __construct(
        protected KandidatService $kandidatService,
        protected KandidatEnrollmentService $enrollmentService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $studijskiProgramId = $request->integer('studijskiProgramId') ?: $this->kandidatService->getActiveStudijskiProgramOsnovne();

        $studijskiProgrami = $this->kandidatService->getStudijskiProgrami(1);
        $kandidati = $this->kandidatService->getAll([
            'tipStudija_id' => 1,
            'statusUpisa_id' => 3,
            'studijskiProgram_id' => $studijskiProgramId,
        ]);

        return view('kandidat.indeks')
            ->with('kandidati', $kandidati)
            ->with('studijskiProgrami', $studijskiProgrami);
    }

    public function create()
    {
        return view('kandidat.create_part_1', $this->kandidatService->getDropdownData());
    }

    public function store(StoreKandidatRequest $request)
    {
        if ($request->page == 1) {

            $data = KandidatPage1Data::fromRequest($request);
            $kandidat = $this->kandidatService->storeKandidatPage1($data);

            return view('kandidat.create_part_2', $this->kandidatService->getPageTwoFormData($kandidat->id));

        } elseif ($request->page == 2) {

            $data = KandidatPage2Data::fromRequest($request);
            $this->kandidatService->storeKandidatPage2($data);

            return redirect()->route('kandidat.index');
        }
    }

    public function show($id)
    {
        $kandidat = $this->kandidatService->findByIdOrFail($id)->toArray();

        return view('kandidat.details')->with('kandidat', $kandidat);
    }

    public function edit($id)
    {
        $kandidat = $this->kandidatService->findByIdOrFail($id);
        $editData = $this->kandidatService->getEditDropdownData($id);

        return view('kandidat.update', array_merge(['kandidat' => $kandidat], $editData));
    }

    public function update(UpdateKandidatRequest $request, $id)
    {
        $data = KandidatUpdateData::fromRequest($request);
        $kandidat = $this->kandidatService->updateKandidat($id, $data);

        $saved = ! empty($kandidat->id);

        if ($saved) {
            Session::flash('flash-success', 'update');
            if (! empty($request->submitstay)) {
                return redirect()->route('kandidat.edit', $kandidat->id);
            }
            if ($kandidat->statusUpisa_id == 1) {
                return redirect()->route('student.index', ['tipStudijaId' => 1, 'godina' => $kandidat->godinaStudija_id, 'studijskiProgramId' => $kandidat->studijskiProgram_id]);
            }

            return redirect()->route('kandidat.index', ['studijskiProgramId' => $kandidat->studijskiProgram_id]);
        } else {
            Session::flash('flash-error', 'update');
            if ($kandidat->statusUpisa_id == 1) {
                return redirect()->route('student.index', ['tipStudijaId' => 1, 'godina' => $kandidat->godinaStudija_id, 'studijskiProgramId' => $kandidat->studijskiProgram_id]);
            }

            return redirect()->route('kandidat.index', ['studijskiProgramId' => $kandidat->studijskiProgram_id]);
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
        return view('kandidat.sportsko_angazovanje', $this->kandidatService->getSportPageData($id));
    }

    public function sportStore(KandidatSportRequest $request, $id)
    {
        $this->kandidatService->storeSport($id, $request->validated());

        return redirect()->route('kandidat.sport', $id);
    }

    public function testPost(Request $request)
    {
        return $request->all();
    }

    public function createMaster()
    {
        $dropdownData = $this->kandidatService->getDropdownDataMaster();

        return view('kandidat.create_master', $dropdownData);
    }

    public function storeMaster(StoreMasterKandidatRequest $request)
    {
        $data = MasterKandidatData::fromRequest($request);
        $kandidat = $this->kandidatService->storeMasterKandidat($data);

        return redirect()->route('master.index', ['studijskiProgramId' => $kandidat->studijskiProgram_id]);
    }

    public function editMaster($id)
    {
        $kandidat = $this->kandidatService->findByIdOrFail($id);
        $editData = $this->kandidatService->getEditDropdownDataMaster($id);

        return view('kandidat.update_master', array_merge(['kandidat' => $kandidat], $editData));
    }

    public function updateMaster(UpdateMasterKandidatRequest $request, $id)
    {
        $data = MasterKandidatData::fromRequest($request);
        $kandidat = $this->kandidatService->updateMasterKandidat($id, $data);
        $saved = ! empty($kandidat->id);

        if ($saved) {
            Session::flash('flash-success', 'update');
            if (! empty($request->submitstay)) {
                return redirect()->route('master.edit', $kandidat->id);
            }
            if ($kandidat->statusUpisa_id == 1) {
                return redirect()->route('student.index', ['tipStudijaId' => 2, 'studijskiProgramId' => $kandidat->studijskiProgram_id]);
            }

            return redirect()->route('master.index', ['studijskiProgramId' => $kandidat->studijskiProgram_id]);
        } else {
            Session::flash('flash-error', 'update');
            if ($kandidat->statusUpisa_id == 1) {
                return redirect()->route('student.index', ['tipStudijaId' => 2, 'studijskiProgramId' => $kandidat->studijskiProgram_id]);
            }

            return redirect()->route('master.index', ['studijskiProgramId' => $kandidat->studijskiProgram_id]);
        }
    }

    public function indexMaster(Request $request)
    {
        $studijskiProgramId = $request->integer('studijskiProgramId') ?: $this->kandidatService->getActiveStudijskiProgramId(2);

        if ($studijskiProgramId === null) {
            return view('kandidat.index_master')
                ->with('kandidati', collect())
                ->with('studijskiProgrami', collect());
        }

        $studijskiProgrami = $this->kandidatService->getAktivniStudijskiProgrami(2);
        $kandidati = $this->kandidatService->getAll([
            'tipStudija_id' => 2,
            'statusUpisa_id' => 3,
            'studijskiProgram_id' => $studijskiProgramId,
        ]);

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
        $result = $this->enrollmentService->upisKandidata($id);

        if (! $result['success']) {
            Session::flash('flash-error', 'upis');
            if ($result['tipStudija_id'] == 1) {
                return redirect()->route('kandidat.index');
            }

            return redirect()->route('master.index');
        }

        Session::flash('flash-success', 'upis');

        if ($result['tipStudija_id'] == 1) {
            return redirect()->route('kandidat.index');
        } elseif ($result['tipStudija_id'] == 2) {
            return redirect()->route('master.index');
        }
    }

    public function masovnaUplata(DodajStudentaRequest $request)
    {
        $this->enrollmentService->masovnaUplata($request->input('odabir'));

        return redirect()->route('kandidat.index');
    }

    public function masovniUpis(DodajStudentaRequest $request)
    {
        $success = $this->enrollmentService->masovniUpis($request->input('odabir'));

        if (! $success) {
            Session::flash('flash-error', 'upis');
        }

        return redirect()->route('kandidat.index');
    }

    public function masovnaUplataMaster(DodajStudentaRequest $request)
    {
        $this->enrollmentService->masovnaUplataMaster($request->input('odabir'));

        return redirect()->route('master.index');
    }

    public function masovniUpisMaster(DodajStudentaRequest $request)
    {
        $this->enrollmentService->masovniUpisMaster($request->input('odabir'));

        return redirect()->route('master.index');
    }

    public function registracijaKandidata($id)
    {
        $this->enrollmentService->registracijaKandidata($id);

        return redirect()->route('kandidat.index');
    }
}
