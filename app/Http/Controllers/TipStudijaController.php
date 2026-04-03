<?php

namespace App\Http\Controllers;

use App\TipStudija;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class TipStudijaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $tipStudija = TipStudija::all();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.tipStudija', compact('tipStudija'));
    }

    public function unos(Request $request)
    {
        $tipStudija = new TipStudija;

        $tipStudija->naziv = $request->naziv;
        $tipStudija->skrNaziv = $request->skrNaziv;
        $tipStudija->indikatorAktivan = 1;

        try {
            $tipStudija->save();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/tipStudija');
    }

    public function edit(TipStudija $tipStudija)
    {
        return view('sifarnici.editTipStudija', compact('tipStudija'));
    }

    public function add()
    {
        return view('sifarnici.addTipStudija');
    }

    public function update(Request $request, TipStudija $tipStudija)
    {
        $tipStudija->naziv = $request->naziv;
        $tipStudija->skrNaziv = $request->skrNaziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $tipStudija->indikatorAktivan = 1;
        } else {
            $tipStudija->indikatorAktivan = 0;
        }

        try {
            $tipStudija->update();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/tipStudija');
    }

    public function delete(TipStudija $tipStudija)
    {
        try {
            $tipStudija->delete();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
