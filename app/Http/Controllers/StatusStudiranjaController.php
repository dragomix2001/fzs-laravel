<?php

namespace App\Http\Controllers;

use App\Models\StatusStudiranja;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class StatusStudiranjaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $statusStudiranja = StatusStudiranja::all();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.statusStudiranja', compact('statusStudiranja'));
    }

    public function unos(Request $request)
    {
        $statusStudiranja = new StatusStudiranja;

        $statusStudiranja->naziv = $request->naziv;
        $statusStudiranja->indikatorAktivan = 1;

        try {
            $statusStudiranja->save();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/statusStudiranja');
    }

    public function edit(StatusStudiranja $statusStudiranja)
    {
        return view('sifarnici.editStatusStudiranja', compact('statusStudiranja'));
    }

    public function add()
    {
        return view('sifarnici.addStatusStudiranja');
    }

    public function update(Request $request, StatusStudiranja $statusStudiranja)
    {
        $statusStudiranja->naziv = $request->naziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $statusStudiranja->indikatorAktivan = 1;
        } else {
            $statusStudiranja->indikatorAktivan = 0;
        }

        try {
            $statusStudiranja->update();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/statusStudiranja');
    }

    public function delete(StatusStudiranja $statusStudiranja)
    {
        try {
            $statusStudiranja->delete();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
