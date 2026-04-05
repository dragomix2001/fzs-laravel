<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TipPrijave;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class TipPrijaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $tip = TipPrijave::all();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.tipPrijave', compact('tip'));
    }

    public function unos(Request $request)
    {
        $tip = new TipPrijave;

        $tip->naziv = $request->naziv;
        $tip->indikatorAktivan = 1;

        try {
            $tip->save();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/tipPrijave');
    }

    public function edit(TipPrijave $tip)
    {
        return view('sifarnici.editTipPrijave', compact('tip'));
    }

    public function add()
    {
        return view('sifarnici.addTipPrijave');
    }

    public function update(Request $request, TipPrijave $tip)
    {
        $tip->naziv = $request->naziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $tip->indikatorAktivan = 1;
        } else {
            $tip->indikatorAktivan = 0;
        }

        try {
            $tip->update();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/tipPrijave');
    }

    public function delete(TipPrijave $tip)
    {
        try {
            $tip->delete();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
