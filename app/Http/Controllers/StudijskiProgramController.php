<?php

namespace App\Http\Controllers;

use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class StudijskiProgramController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $studijskiProgram = StudijskiProgram::all();
        $tipStudija = TipStudija::all();

        return view('sifarnici.studijskiProgram', compact('studijskiProgram', 'tipStudija'));
    }

    public function unos(Request $request)
    {
        $studijskiProgram = new StudijskiProgram;

        $studijskiProgram->naziv = $request->naziv;
        $studijskiProgram->tipStudija_id = $request->tipStudija_id;
        $studijskiProgram->skrNazivStudijskogPrograma = $request->skrNazivStudijskogPrograma;
        $studijskiProgram->zvanje = $request->zvanje;
        $studijskiProgram->indikatorAktivan = 1;

        $studijskiProgram->save();

        return Redirect::to('/studijskiProgram');
    }

    public function edit(StudijskiProgram $studijskiProgram)
    {
        $tipStudija = TipStudija::all();

        return view('sifarnici.editStudijskiProgram', compact('studijskiProgram', 'tipStudija'));
    }

    public function add()
    {
        $tipStudija = TipStudija::all();

        return view('sifarnici.addStudijskiProgram', compact('tipStudija'));
    }

    public function update(Request $request, StudijskiProgram $studijskiProgram)
    {
        $studijskiProgram->naziv = $request->naziv;
        $studijskiProgram->tipStudija_id = $request->tipStudija_id;
        $studijskiProgram->skrNazivStudijskogPrograma = $request->skrNazivStudijskogPrograma;
        $studijskiProgram->zvanje = $request->zvanje;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $studijskiProgram->indikatorAktivan = 1;
        } else {
            $studijskiProgram->indikatorAktivan = 0;
        }

        $studijskiProgram->update();

        return Redirect::to('/studijskiProgram');
    }

    public function delete(StudijskiProgram $studijskiProgram)
    {
        $studijskiProgram->delete();

        return back();
    }
}
