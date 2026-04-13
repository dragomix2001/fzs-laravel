<?php

namespace App\Http\Controllers;

use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusGodine;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\UpisGodine;
use App\Services\UpisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class StudentController extends Controller
{
    private $status;

    public function __construct(protected UpisService $upisService)
    {
        $this->middleware('auth');

        $this->status = Config::get('constants.statusi');
    }

    public function index(Request $request, $tipStudijaId)
    {
        $godinaStudija = $request->godina;
        if ($godinaStudija == null || $godinaStudija > 4 || $godinaStudija < 1) {
            $godinaStudija = 1;
        }

        $studijskiProgramId = 1;
        if (! empty($request->studijskiProgramId)) {
            $studijskiProgramId = $request->studijskiProgramId;
        }

        if ($tipStudijaId == 1) {

            $studenti = Kandidat::where(['tipStudija_id' => 1, 'statusUpisa_id' => 1, 'godinaStudija_id' => $godinaStudija, 'studijskiProgram_id' => $studijskiProgramId])->get();
            $studijskiProgrami = StudijskiProgram::where(['tipStudija_id' => 1])->get();

            return view('student.indeks')
                ->with('studenti', $studenti)->with('tipStudija', 1)
                ->with('studijskiProgrami', $studijskiProgrami);

        } elseif ($tipStudijaId == 2) {

            $studenti = Kandidat::where(['tipStudija_id' => 2, 'statusUpisa_id' => 1, 'studijskiProgram_id' => $studijskiProgramId])->get();
            $studijskiProgrami = StudijskiProgram::where(['tipStudija_id' => 2])->get();

            return view('student.index_master')->with('studenti', $studenti)
                ->with('tipStudija', 2)
                ->with('studijskiProgrami', $studijskiProgrami);
        }

        return 'Дошло је до неочекиване грешке.';
    }

    // Status studenta
    public function upisStudenta($id)
    {
        $kandidat = Kandidat::find($id);
        $studijskiProgram = StudijskiProgram::where(['tipStudija_id' => 2])->get();
        $skolskaGodinaUpisa = SkolskaGodUpisa::all();
        $osnovneStudije = UpisGodine::where(['kandidat_id' => $id, 'tipStudija_id' => 1])
            ->orderBy('godina', 'ASC')
            ->orderBy('pokusaj', 'ASC')
            ->get();
        $masterStudije = UpisGodine::where(['kandidat_id' => $id, 'tipStudija_id' => 2])->get();

        $doktorskeStudije = UpisGodine::where(['kandidat_id' => $id, 'tipStudija_id' => 3])->get();

        return view('upis.index', compact('kandidat', 'osnovneStudije', 'masterStudije', 'doktorskeStudije', 'studijskiProgram', 'skolskaGodinaUpisa'));
    }

    public function upisiStudenta($id, Request $request)
    {
        if (empty($id) || empty($request->godina)) {
            Session::flash('flash-error', 'upis');

            return redirect("student/{$id}/upis");
        }

        $this->upisService->upisiStudentaGodinu($id, $request->godina, $request->pokusaj);

        return redirect("student/{$id}/upis");
    }

    public function obnoviGodinu($id, Request $request)
    {
        if (empty($id) || empty($request->godina)) {
            Session::flash('flash-error', 'upis');

            return redirect("student/{$id}/upis");
        }

        $this->upisService->obnoviGodinu($id, $request->godina, $request->tipStudijaId);

        return redirect("student/{$id}/upis");
    }

    public function obrisiObnovuGodine($id, Request $request)
    {
        if (empty($id) || empty($request->upisId)) {
            Session::flash('flash-error', 'upis');

            return redirect("student/{$id}/upis");
        }

        $this->upisService->obrisiObnovuGodine($request->upisId);

        return redirect("student/{$id}/upis");
    }

    public function ponistiUpis($id, Request $request)
    {
        if (empty($id) || empty($request->upisId)) {
            Session::flash('flash-error', 'upis');

            return redirect("student/{$id}/upis");
        }

        $this->upisService->ponistiUpis($request->upisId);

        return redirect("student/{$id}/upis");
    }

    public function promeniStatus($id, $statusId, $godinaId)
    {
        $this->upisService->promeniStatus($id, $statusId, $godinaId, $this->status);

        return Redirect::back();
    }

    public function masovniUpis(Request $request)
    {
        foreach ($request->odabir as $kandidatId) {

            $kandidat = Kandidat::find($kandidatId);
            $godina = $kandidat->godinaStudija_id + 1;

            $this->upisService->upisiGodinu($kandidatId, $godina, $kandidat->skolskaGodinaUpisa_id);
        }

        return redirect('/student/index/1');
    }

    public function upisMasterStudija(Request $request)
    {
        $result = $this->upisService->upisMasterPostojeciKandidat($request->kandidat_id, $request->StudijskiProgram, $request->SkolskaGodinaUpisa);
        if ($result) {
            Session::flash('flash-success', 'upis');

            return redirect("/student/{$result}/upis");
        } else {
            Session::flash('flash-error', 'upis');

            return Redirect::back();
        }

    }

    public function izmenaGodine($id)
    {
        $upisGodine = UpisGodine::find($id);
        $statusGodine = StatusGodine::all();
        $skolskaGodina = SkolskaGodUpisa::all();

        return view('upis.edit', compact('upisGodine', 'statusGodine', 'skolskaGodina'));
    }

    public function storeIzmenaGodine(Request $request)
    {
        try {
            $kandidatId = $this->upisService->sacuvajIzmenuGodine(
                $request->id,
                $request->statusGodine_id,
                $request->skolskaGodina_id,
                $request->datumUpisa,
                $request->datumUpisa_format,
                $request->datumPromene,
                $request->datumPromene_format,
            );
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());

            return Redirect::back();
        }

        return redirect("/student/{$kandidatId}/upis");
    }

    public function zamrznutiStudenti()
    {
        $statusZamrzao = Config::get('constants.statusi.zamrzao');
        $studenti = Kandidat::where(['statusUpisa_id' => $statusZamrzao])->get();

        return view('student.index_zamrznuti', compact('studenti'));
    }

    public function diplomiraniStudenti(Request $request)
    {
        $statusDiplomirao = Config::get('constants.statusi.diplomirao');
        $tipStudija = TipStudija::all();
        $studijskiProgrami = StudijskiProgram::where([
            'tipStudija_id' => $request->tipStudijaId,
            'indikatorAktivan' => 1])->get();

        $studenti = Kandidat::where([
            'tipStudija_id' => $request->tipStudijaId,
            'studijskiProgram_id' => $request->studijskiProgramId,
            'statusUpisa_id' => $statusDiplomirao,
        ])->get();

        return view('student.index_diplomirani', compact('studenti', 'tipStudija', 'studijskiProgrami'));
    }

    public function ispisaniStudenti(Request $request)
    {
        $statusIspisan = Config::get('constants.statusi.odustao');
        $tipStudija = TipStudija::all();
        $studijskiProgrami = StudijskiProgram::where([
            'tipStudija_id' => $request->tipStudijaId,
            'indikatorAktivan' => 1])->get();

        $studenti = Kandidat::where([
            'tipStudija_id' => $request->tipStudijaId,
            'studijskiProgram_id' => $request->studijskiProgramId,
            'statusUpisa_id' => $statusIspisan,
        ])->get();

        return view('student.index_ispisani', compact('studenti', 'tipStudija', 'studijskiProgrami'));
    }
}
