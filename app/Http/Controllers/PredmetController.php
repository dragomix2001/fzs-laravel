<?php

namespace App\Http\Controllers;

use App\GodinaStudija;
use App\Http\Requests;
use App\Predmet;
use App\PredmetProgram;
use App\SkolskaGodUpisa;
use App\StudijskiProgram;
use App\TipPredmeta;
use App\TipStudija;
use Illuminate\Database\QueryException;
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
        try {
            $predmet = Predmet::all();
            $tipPredmeta = TipPredmeta::all();
            $tipStudija = TipStudija::all();
            $studijskiProgram = StudijskiProgram::all();
            $godinaStudija = GodinaStudija::all();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return view('sifarnici.predmet', compact('predmet', 'tipStudija', 'studijskiProgram', 'godinaStudija', 'tipPredmeta'));
    }

    public function unos(Request $request)
    {
        $predmet = new Predmet;

        $predmet->naziv = $request->naziv;

        try {
            $predmet->save();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return Redirect::to('/predmet');
    }

    public function edit(Predmet $predmet)
    {
        try {
            // $programi = PredmetProgram::where(['predmet_id' => $predmet->id])->get();
            // return $programi;
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return view('sifarnici.editPredmet', compact('predmet'));
    }

    public function editProgram(Predmet $predmet)
    {
        try {
            try {
                $programi = PredmetProgram::where(['predmet_id' => $predmet->id])->get();

            } catch (QueryException $e) {
                dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
            }
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return view('sifarnici.editPredmetProgram', compact('programi', 'predmet'));
    }

    public function add()
    {
        try {
            $godinaStudija = GodinaStudija::all();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return view('sifarnici.addPredmet', compact('tipStudija', 'tipPredmeta'));
    }

    public function update(Request $request, Predmet $predmet)
    {
        $predmet->naziv = $request->naziv;

        try {
            $predmet->update();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return Redirect::to('/predmet');
    }

    public function delete(Predmet $predmet)
    {
        try {
            $predmet->delete();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return back();
    }

    public function deleteProgram(PredmetProgram $program)
    {
        try {
            $program->delete();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return back();
    }

    public function addProgram(Predmet $predmet)
    {
        try {
            $programi = StudijskiProgram::all();
            $godinaStudija = GodinaStudija::all();
            $tipPredmeta = TipPredmeta::all();
            $tipStudija = TipStudija::all();
            $skolskaGodina = SkolskaGodUpisa::all();
            // $semestar = Semestar::all();
            // $oblik = OblikNastave::all();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

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

        try {
            $program->save();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return Redirect::to('/predmet/'.$request->predmet_id.'/editProgram');
    }
}
