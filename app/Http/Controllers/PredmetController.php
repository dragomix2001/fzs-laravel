<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\GodinaStudija;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PredmetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $predmet = Predmet::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $studijskiProgram = StudijskiProgram::all();
        $godinaStudija = GodinaStudija::all();

        return view('sifarnici.predmet', compact('predmet', 'tipStudija', 'studijskiProgram', 'godinaStudija', 'tipPredmeta'));
    }

    public function unos(Request $request)
    {
        $predmet = new Predmet;

        $predmet->naziv = $request->naziv;

        $predmet->save();

        return Redirect::to('/predmet');
    }

    public function edit(Predmet $predmet)
    {
        // $programi = PredmetProgram::where(['predmet_id' => $predmet->id])->get();
        // return $programi;

        return view('sifarnici.editPredmet', compact('predmet'));
    }

    public function editProgram(Predmet $predmet)
    {
        $programi = PredmetProgram::where(['predmet_id' => $predmet->id])->get();

        return view('sifarnici.editPredmetProgram', compact('programi', 'predmet'));
    }

    public function add()
    {
        $godinaStudija = GodinaStudija::all();

        return view('sifarnici.addPredmet');
    }

    public function update(Request $request, Predmet $predmet)
    {
        $predmet->naziv = $request->naziv;

        $predmet->update();

        return Redirect::to('/predmet');
    }

    public function delete(Predmet $predmet)
    {
        $predmet->delete();

        return back();
    }

    public function deleteProgram(PredmetProgram $program)
    {
        $program->delete();

        return back();
    }

    public function addProgram(Predmet $predmet)
    {
        $programi = StudijskiProgram::all();
        $godinaStudija = GodinaStudija::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $skolskaGodina = SkolskaGodUpisa::all();
        // $semestar = Semestar::all();
        // $oblik = OblikNastave::all();

        return view('sifarnici.addPredmetProgram', compact('programi', 'predmet', 'godinaStudija', 'tipPredmeta', 'tipStudija', 'skolskaGodina'));
    }

    public function addProgramUnos(Requests\ProgramRequest $request)
    {

        $program = new PredmetProgram;
        $program->studijskiProgram_id = $request->program_id;
        $program->predmet_id = $request->predmet_id;
        $program->godinaStudija_id = $request->godinaStudija_id;
        $program->semestar = $request->semestar;
        $program->tipPredmeta_id = $request->tipPredmeta_id;
        $program->tipStudija_id = $program->program->tipStudija->id;
        $program->espb = $request->espb;
        $program->predavanja = $request->predavanja;
        $program->vezbe = $request->vezbe;
        $program->skolskaGodina_id = $request->skolskaGodina_id;
        $program->statusPredmeta = 1;
        $program->indikatorAktivan = 1;

        $program->save();

        return Redirect::to('/predmet/'.$request->predmet_id.'/editProgram');
    }
}
