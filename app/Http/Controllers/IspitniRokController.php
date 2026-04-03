<?php

namespace App\Http\Controllers;

use App\IspitniRok;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class IspitniRokController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $ispitniRok = IspitniRok::all();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return view('sifarnici.ispitniRok', compact('ispitniRok'));
    }

    public function unos(Request $request)
    {
        $ispitniRok = new IspitniRok;

        $ispitniRok->naziv = $request->naziv;
        $ispitniRok->indikatorAktivan = 1;

        try {
            $ispitniRok->save();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/ispitniRok');
    }

    public function edit(IspitniRok $ispitniRok)
    {
        return view('sifarnici.editIspitniRok', compact('ispitniRok'));
    }

    public function add()
    {
        return view('sifarnici.addIspitniRok');
    }

    public function update(Request $request, IspitniRok $ispitniRok)
    {
        $ispitniRok->naziv = $request->naziv;
        if ($request->indikatorAktivan == 'on') {
            $ispitniRok->indikatorAktivan = 1;
        } else {
            $ispitniRok->indikatorAktivan = 0;
        }

        try {
            $ispitniRok->update();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return Redirect::to('/ispitniRok');
    }

    public function delete(IspitniRok $ispitniRok)
    {
        try {
            $ispitniRok->delete();
        } catch (QueryException $e) {
            Log::error('Database error: '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Дошло је до непредвиђене грешке. Молимо покушајте поново.');
        }

        return back();
    }
}
