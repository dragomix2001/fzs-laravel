<?php

namespace App\Http\Controllers;

use App\GodinaStudija;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class GodinaStudijaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $godinaStudija = GodinaStudija::all();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

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

        try {
            $godinaStudija->save();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

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

        try {
            $godinaStudija->update();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/godinaStudija');
    }

    public function delete(GodinaStudija $godinaStudija)
    {

        try {
            $godinaStudija->delete();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
