<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\OblikNastave;
use App\Models\PredmetProgram;
use App\Models\Profesor;
use App\Models\ProfesorPredmet;
use App\Models\StatusProfesora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ProfesorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $profesor = Profesor::all();
        $status = StatusProfesora::all();

        return view('sifarnici.profesor', compact('profesor', 'status'));
    }

    public function unos(Request $request)
    {
        $profesor = new Profesor;

        $profesor->jmbg = $request->jmbg;
        $profesor->ime = $request->ime;
        $profesor->prezime = $request->prezime;
        $profesor->telefon = $request->telefon;
        $profesor->zvanje = $request->zvanje;
        $profesor->kabinet = $request->kabinet;
        $profesor->mail = $request->mail;
        $profesor->indikatorAktivan = 1;
        $profesor->status_id = $request->status_id;

        $profesor->save();

        return Redirect::to('/profesor');
    }

    public function edit(Profesor $profesor)
    {
        $status = StatusProfesora::all();
        $predmeti = ProfesorPredmet::where('profesor_id', $profesor->id)->get();

        return view('sifarnici.editProfesor', compact('profesor', 'status', 'predmeti'));
    }

    public function editPredmet(Profesor $profesor)
    {
        // $status = StatusProfesora::all();
        $predmeti = ProfesorPredmet::where('profesor_id', $profesor->id)->get();
        // return($predmeti->first());

        return view('sifarnici.editProfesorPredmet', compact('profesor', 'predmeti'));
    }

    public function add()
    {
        $status = StatusProfesora::all();

        return view('sifarnici.addProfesor', compact('status'));
    }

    public function update(Request $request, Profesor $profesor)
    {
        $profesor->jmbg = $request->jmbg;
        $profesor->ime = $request->ime;
        $profesor->mail = $request->mail;
        $profesor->prezime = $request->prezime;
        $profesor->telefon = $request->telefon;
        $profesor->zvanje = $request->zvanje;
        $profesor->kabinet = $request->kabinet;
        $profesor->status_id = $request->status_id;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $profesor->indikatorAktivan = 1;
        } else {
            $profesor->indikatorAktivan = 0;
        }

        $profesor->update();

        return Redirect::to('/profesor');
    }

    public function delete(Profesor $profesor)
    {
        $profesor->delete();

        return back();
    }

    public function deletePredmet(ProfesorPredmet $predmet)
    {
        $predmet->delete();

        return back();
    }

    public function addPredmet(Profesor $profesor)
    {
        $predmet = PredmetProgram::all();
        $oblik = OblikNastave::all();

        return view('sifarnici.addProfesorPredmet', compact('predmet', 'oblik', 'profesor'));
    }

    public function addPredmetUnos(Requests\ProfesorRequest $request)
    {
        $predmet = new ProfesorPredmet;

        $predmet->profesor_id = $request->profesor_id;
        $predmet->predmet_id = $request->predmet_id;
        $predmet->oblik_nastave_id = $request->oblikNastave_id;
        $predmet->indikatorAktivan = 1;

        $predmet->save();

        return Redirect::to('/profesor/'.$predmet->profesor_id.'/editPredmet');
    }
}
