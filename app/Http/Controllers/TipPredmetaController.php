<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TipPredmeta;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class TipPredmetaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $tipPredmeta = TipPredmeta::all();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.tipPredmeta', compact('tipPredmeta'));
    }

    public function unos(Request $request)
    {
        $tipPredmeta = new TipPredmeta;

        $tipPredmeta->naziv = $request->naziv;
        $tipPredmeta->skrNaziv = $request->skrNaziv;
        $tipPredmeta->indikatorAktivan = 1;

        try {
            $tipPredmeta->save();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/tipPredmeta');
    }

    public function edit(TipPredmeta $tipPredmeta)
    {
        return view('sifarnici.editTipPredmeta', compact('tipPredmeta'));
    }

    public function add()
    {
        return view('sifarnici.addTipPredmeta');
    }

    public function update(Request $request, TipPredmeta $tipPredmeta)
    {
        $tipPredmeta->naziv = $request->naziv;
        $tipPredmeta->skrNaziv = $request->skrNaziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $tipPredmeta->indikatorAktivan = 1;
        } else {
            $tipPredmeta->indikatorAktivan = 0;
        }

        try {
            $tipPredmeta->update();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/tipPredmeta');
    }

    public function delete(TipPredmeta $tipPredmeta)
    {
        try {
            $tipPredmeta->delete();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
