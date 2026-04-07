<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class SportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $sport = Sport::all();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.sport', compact('sport'));
    }

    public function unos(Request $request)
    {
        $sport = new Sport;

        $sport->naziv = $request->naziv;
        $sport->indikatorAktivan = 1;

        try {
            $sport->save();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/sport');
    }

    public function edit(Sport $sport)
    {
        return view('sifarnici.editSport', compact('sport'));
    }

    public function add()
    {
        return view('sifarnici.addSport');
    }

    public function update(Request $request, Sport $sport)
    {
        $sport->naziv = $request->naziv;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $sport->indikatorAktivan = 1;
        } else {
            $sport->indikatorAktivan = 0;
        }

        try {
            $sport->update();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/sport');
    }

    public function delete(Sport $sport)
    {
        try {
            $sport->delete();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
