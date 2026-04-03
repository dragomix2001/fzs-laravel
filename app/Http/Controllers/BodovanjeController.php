<?php

namespace App\Http\Controllers;

use App\Bodovanje;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class BodovanjeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $bodovanje = Bodovanje::all();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.bodovanje', compact('bodovanje'));
    }

    public function unos(Request $request)
    {
        $bodovanje = new Bodovanje;

        $bodovanje->opisnaOcena = $request->opisnaOcena;
        $bodovanje->poeniMin = $request->poeniMin;
        $bodovanje->poeniMax = $request->poeniMax;
        $bodovanje->ocena = $request->ocena;
        $bodovanje->indikatorAktivan = 1;

        try {
            $bodovanje->save();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/bodovanje');
    }

    public function edit(Bodovanje $bodovanje)
    {
        return view('sifarnici.editBodovanje', compact('bodovanje'));
    }

    public function add()
    {
        return view('sifarnici.addBodovanje');
    }

    public function update(Request $request, Bodovanje $bodovanje)
    {
        $bodovanje->opisnaOcena = $request->opisnaOcena;
        $bodovanje->poeniMin = $request->poeniMin;
        $bodovanje->poeniMax = $request->poeniMax;
        $bodovanje->ocena = $request->ocena;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $bodovanje->indikatorAktivan = 1;
        } else {
            $bodovanje->indikatorAktivan = 0;
        }

        try {
            $bodovanje->update();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/bodovanje');
    }

    public function delete(Bodovanje $bodovanje)
    {
        try {
            $bodovanje->delete();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
