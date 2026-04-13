<?php

namespace App\Http\Controllers;

use App\Models\GodinaStudija;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class GodinaStudijaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $godinaStudija = GodinaStudija::all();

        return view('sifarnici.godinaStudija', compact('godinaStudija'));
    }

    public function unos(Request $request)
    {
        $godinaStudija = new GodinaStudija;

        $godinaStudija->naziv = $request->naziv;
        $godinaStudija->nazivRimski = $request->nazivRimski;
        $godinaStudija->nazivSlovimaUPadezu = $request->nazivSlovimaUPadezu;
        $godinaStudija->redosledPrikazivanja = $request->redosledPrikazivanja;
        $godinaStudija->indikatorAktivan = 1;

        $godinaStudija->save();

        return Redirect::to('/godinaStudija');
    }

    public function edit(GodinaStudija $godinaStudija)
    {
        return view('sifarnici.editGodinaStudija', compact('godinaStudija'));
    }

    public function add()
    {
        return view('sifarnici.addGodinaStudija');
    }

    public function update(Request $request, GodinaStudija $godinaStudija)
    {
        $godinaStudija->naziv = $request->naziv;
        $godinaStudija->nazivRimski = $request->nazivRimski;
        $godinaStudija->nazivSlovimaUPadezu = $request->nazivSlovimaUPadezu;
        $godinaStudija->redosledPrikazivanja = $request->redosledPrikazivanja;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $godinaStudija->indikatorAktivan = 1;
        } else {
            $godinaStudija->indikatorAktivan = 0;
        }

        $godinaStudija->update();

        return Redirect::to('/godinaStudija');
    }

    public function delete(GodinaStudija $godinaStudija)
    {

        $godinaStudija->delete();

        return back();
    }
}
