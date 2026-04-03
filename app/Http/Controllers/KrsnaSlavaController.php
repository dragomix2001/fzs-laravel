<?php

namespace App\Http\Controllers;

use App\KrsnaSlava;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class KrsnaSlavaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $krsnaSlava = KrsnaSlava::all();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.krsnaSlava', compact('krsnaSlava'));
    }

    public function unos(Request $request)
    {
        $krsnaSlava = new KrsnaSlava;
        $krsnaSlava->naziv = $request->naziv;
        $krsnaSlava->datumSlave = $request->datumSlave;
        $krsnaSlava->indikatorAktivan = 1;

        try {
            $krsnaSlava->save();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/krsnaSlava');
    }

    public function edit(KrsnaSlava $krsnaSlava)
    {
        return view('sifarnici.editKrsnaSlava', compact('krsnaSlava'));
    }

    public function add()
    {
        return view('sifarnici.addKrsnaSlava');
    }

    public function update(Request $request, KrsnaSlava $krsnaSlava)
    {
        $krsnaSlava->naziv = $request->naziv;
        $krsnaSlava->datumSlave = $request->datumSlave;
        if ($request->indikatorAktivan == 'on' || $request->indikatorAktivan == 1) {
            $krsnaSlava->indikatorAktivan = 1;
        } else {
            $krsnaSlava->indikatorAktivan = 0;
        }

        try {
            $krsnaSlava->update();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/krsnaSlava');
    }

    public function delete(KrsnaSlava $krsnaSlava)
    {
        try {
            $krsnaSlava->delete();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
