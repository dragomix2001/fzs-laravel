<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\OblikNastave;
use App\PredmetProgram;
use App\Profesor;
use App\ProfesorPredmet;
use App\StatusProfesora;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class ProfesorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $profesor = Profesor::all();
            $status = StatusProfesora::all();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

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

        try {
            $profesor->save();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/profesor');
    }

    public function edit(Profesor $profesor)
    {
        try {
            $status = StatusProfesora::all();
            $predmeti = ProfesorPredmet::where('profesor_id', $profesor->id)->get();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.editProfesor', compact('profesor', 'status', 'predmeti'));
    }

    public function editPredmet(Profesor $profesor)
    {
        try {
            // $status = StatusProfesora::all();
            $predmeti = ProfesorPredmet::where('profesor_id', $profesor->id)->get();
            // return($predmeti->first());
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.editProfesorPredmet', compact('profesor', 'status', 'predmeti'));
    }

    public function add()
    {
        try {
            $status = StatusProfesora::all();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

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

        try {
            $profesor->update();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/profesor');
    }

    public function delete(Profesor $profesor)
    {
        try {
            $profesor->delete();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }

    public function deletePredmet(ProfesorPredmet $predmet)
    {
        try {
            $predmet->delete();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }

    public function addPredmet(Profesor $profesor)
    {
        try {
            $predmet = PredmetProgram::all();
            $oblik = OblikNastave::all();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.addProfesorPredmet', compact('predmet', 'oblik', 'profesor'));
    }

    public function addPredmetUnos(Requests\ProfesorRequest $request)
    {
        $predmet = new ProfesorPredmet;

        $predmet->profesor_id = $request->profesor_id;
        $predmet->predmet_id = $request->predmet_id;
        $predmet->oblik_nastave_id = $request->oblikNastave_id;
        $predmet->indikatorAktivan = 1;

        try {
            $predmet->save();
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
                        return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/profesor/'.$predmet->profesor_id.'/editPredmet');
    }
}
